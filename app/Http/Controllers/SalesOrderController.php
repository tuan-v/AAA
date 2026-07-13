<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Warehouse;
use App\Services\ActivityLogService;
use App\Services\CustomerDebtService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesOrderController extends Controller
{
    private function getCompanyCurrency()
    {
        $company = auth()->user()->company ?? auth()->user()->companies()->first();
        return $company ? $company->default_currency : null;
    }

    public function index(Request $request)
    {
        $query = SalesOrder::with([
            'customer',
            'currency',
            'items',
            'items.product.stocks',
            'warehouseSlips'
        ])->whereIn('status', [
            'pending',
            'approved',
            'partial',
            'completed'
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $orders = $query->latest()->paginate(min((int) $request->input('per_page', 10), 100));
        $companyCurrency = $this->getCompanyCurrency();

        $orders->getCollection()->transform(function ($item) use ($companyCurrency) {
            foreach ($item->items as $i) {

                $displayPrice = round(
                    $i->unit_price * $item->exchange_rate /
                        ($companyCurrency->exchange_rate ?: 1),
                    2
                );

                $i->unit_price = $displayPrice;
                $i->amount = round($displayPrice * $i->quantity, 2);
            }

            $subtotal = $item->items->sum('amount');

            $vatAmount = round(
                ($item->vat_amount ?? 0)
                    * $item->exchange_rate
                    / ($companyCurrency->exchange_rate ?: 1),
                2
            );

            $total = $subtotal + $vatAmount;
            $totalQuantity = $item->items->sum('quantity');

            return [
                'id' => $item->id,
                'code' => $item->code,
                'status' => $item->status,
                'customer' => $item->customer,
                'currency' => $companyCurrency ?: $item->currency,
                'items' => $item->items,
                'vat_amount' => round($vatAmount, 2),
                'total_amount' => round($total, 2),
                'total_quantity' => $totalQuantity,
                'expected_delivery_date' => $item->expected_delivery_date->format('d/m/Y'),
            ];
        });

        return response()->json($orders);
    }
    public function warehouseIndex(Request $request)
    {
        $orders = SalesOrder::with([
            'customer',
            'currency',
            'items.product'
        ])
            ->whereIn('status', ['approved', 'partial', 'completed'])
            ->latest()
            ->paginate(10);

        $companyCurrency = $this->getCompanyCurrency();

        $orders->getCollection()->transform(function ($order) use ($companyCurrency) {
            foreach ($order->items as $i) {

                $displayPrice = round(
                    $i->unit_price * $order->exchange_rate /
                        ($companyCurrency->exchange_rate ?: 1),
                    2
                );

                $i->unit_price = $displayPrice;
                $i->amount = round($displayPrice * $i->quantity, 2);
            }

            $subtotal = $order->items->sum('amount');

            $vatAmount = round(
                ($order->vat_amount ?? 0)
                    * $order->exchange_rate
                    / ($companyCurrency->exchange_rate ?: 1),
                2
            );

            $total = $subtotal + $vatAmount;
            $totalQuantity = $order->items->sum('quantity');

            return [
                'id' => $order->id,
                'code' => $order->code,
                'status' => $order->status,
                'customer' => $order->customer,
                'currency' => $companyCurrency ?: $order->currency,
                'items' => $order->items,
                'vat_amount' => round($vatAmount, 2),
                'total_amount' => round($total, 2),
                'total_quantity' => $totalQuantity,
                'expected_delivery_date' => $order->expected_delivery_date->format('d/m/Y'),
            ];
        });

        return response()->json($orders);
    }
    public function availableForExport(Request $request)
    {
        $orderId = $request->order_id;

        $order = SalesOrder::with('items.product')->findOrFail($orderId);

        $productIds = $order->items->pluck('product_id');

        $warehouses = Warehouse::whereHas('stocks', function ($q) use ($productIds) {
            $q->whereIn('product_id', $productIds)
                ->where('quantity', '>', 0);
        })
            ->with(['stocks' => function ($q) use ($productIds) {
                $q->whereIn('product_id', $productIds);
            }])
            ->get();

        return response()->json($warehouses);
    }
    public function show($id)
    {
        $companyCurrency = $this->getCompanyCurrency();
        $order = SalesOrder::with([
            'customer',
            'currency',
            'items.product',           // chi tiết sản phẩm
            'items.product.unit',      // nếu có đơn vị
            'warehouseSlips',          // phiếu xuất kho
            'warehouseSlips.items',
            'createdBy',               // người tạo
            'approvedBy',
            'province',
            'ward',
        ])->findOrFail($id);

        // Tính toán thêm exported quantity
        foreach ($order->items as $item) {
            $exported = 0;
            foreach ($order->warehouseSlips as $slip) {
                if ($slip->status !== 'approved') continue;
                foreach ($slip->items as $slipItem) {
                    if ($slipItem->product_id === $item->product_id) {
                        $exported += $slipItem->quantity;
                    }
                }
            }
            $item->exported_quantity = $exported;
            $item->amount = $item->unit_price * $item->quantity;
        }

        // Quy đổi giá trị phiếu xuất kho đi kèm
        foreach ($order->warehouseSlips as $slip) {
            foreach ($slip->items as $slipItem) {
                $slipItem->amount = $slipItem->price * $slipItem->quantity;
            }
        }
        $order->subtotal = $order->items->sum('amount');
        $order->total_amount = $order->subtotal + ($order->vat_amount ?? 0);
        $order->setRelation('currency', $order->currency);

        return response()->json([
            ...$order->toArray(),
            'vat_amount' => $order->vat_amount,
            'remaining_debt' => $order->remaining_debt ?? 0, // nếu có quan hệ công nợ
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'currency_id' => 'required|exists:currencies,id',

            'province_id' => 'nullable',
            'ward_id' => 'nullable',
            'address_detail' => 'nullable|string|max:500',
            'note' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.vat_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.amount' => 'required|numeric|min:0',

            'expected_delivery_date' => 'nullable|date',
            'vat_amount' => 'required|numeric|min:0',
            'subtotal' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ], [
            'customer_id.required' => 'Khách hàng không được để trống',
            'customer_id.exists' => 'Khách hàng không tồn tại',
            'currency_id.required' => 'Đơn vị tiền tệ không được để trống',
            'currency_id.exists' => 'Đơn vị tiền tệ không tồn tại',

            'address_detail.string' => 'Địa chỉ phải là chuỗi',
            'address_detail.max' => 'Địa chỉ không được vượt quá 500 ký tự',

            'note.string' => 'Ghi chú phải là chuỗi',

            'items.required' => 'Sản phẩm không được để trống',
            'items.array' => 'Sản phẩm không hợp lệ',
            'items.min' => 'Sản phẩm không được để trống',

            'items.*.product_id.required' => 'Sản phẩm không được để trống',
            'items.*.product_id.exists' => 'Sản phẩm không tồn tại',

            'items.*.quantity.required' => 'Số lượng không được để trống',
            'items.*.quantity.numeric' => 'Số lượng phải là số',
            'items.*.quantity.min' => 'Số lượng phải lớn hơn 0',

            'items.*.unit_price.required' => 'Đơn giá không được để trống',
            'items.*.unit_price.numeric' => 'Đơn giá phải là số',
            'items.*.unit_price.min' => 'Đơn giá phải lớn hơn hoặc bằng 0',

            'items.*.vat_percent.required' => 'VAT phải là số',
            'items.*.vat_percent.numeric' => 'VAT phải là số',
            'items.*.vat_percent.min' => 'VAT phải lớn hơn hoặc bằng 0',
            'items.*.vat_percent.max' => 'VAT không được vượt quá 100',

            'items.*.amount.required' => 'Thành tiền không được để trống',
            'items.*.amount.numeric' => 'Thành tiền phải là số',
            'items.*.amount.min' => 'Thành tiền phải lớn hơn hoặc bằng 0',

            'expected_delivery_date.date' => 'Ngày giao hàng phải là ngày',
            'vat_amount.required' => 'VAT không được để trống',
            'vat_amount.numeric' => 'VAT phải là số',
            'vat_amount.min' => 'VAT phải lớn hơn hoặc bằng 0',
            'subtotal.required' => 'Tổng phụ không được để trống',
            'subtotal.numeric' => 'Tổng phụ phải là số',
            'subtotal.min' => 'Tổng phụ phải lớn hơn hoặc bằng 0',
            'total_amount.required' => 'Tổng tiền không được để trống',
            'total_amount.numeric' => 'Tổng tiền phải là số',
            'total_amount.min' => 'Tổng tiền phải lớn hơn hoặc bằng 0',
        ]);
        DB::beginTransaction();

        try {
            $orderCurrency = Currency::findOrFail($validated['currency_id']);

            // Lấy tiền tệ mặc định của công ty
            $company = auth()->user()->company ?? auth()->user()->companies()->first();
            if (!$company) throw new \Exception('Người dùng chưa thuộc công ty nào');

            $companyCurrency = $company->currencies()->wherePivot('is_default', true)->first();
            if (!$companyCurrency) throw new \Exception('Công ty chưa cấu hình tiền tệ mặc định');

            $exchangeRate = $orderCurrency->exchange_rate ?? 1;
            $last = SalesOrder::latest('id')->first();

            $order = SalesOrder::create([
                'company_id' => auth()->user()->company_id,

                'customer_id' => $validated['customer_id'],
                'currency_id' => $validated['currency_id'],
                'exchange_rate' => $exchangeRate,

                'province_id' => $validated['province_id'] ?? null,
                'ward_id' => $validated['ward_id'] ?? null,
                'address_detail' => $validated['address_detail'] ?? null,
                'note' => $validated['note'] ?? null,

                'status' => 'pending',
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,

                'vat_amount' => $validated['vat_amount'],
                'subtotal' => $validated['subtotal'] ?? 0,
                'total_amount' => $validated['total_amount'],
                'created_by' => auth()->id(),
            ]);

            $total = 0;
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $amount = $item['quantity'] * $item['unit_price'];
                $vatPercent = $item['vat_percent'] ?? 0;
                $vatAmount = ($amount * $vatPercent) / 100;

                // Quy đổi đúng: giá khách hàng × tỉ_giá_đơn ÷ tỉ_giá_công_ty
                $companyUnitPrice = round(
                    $item['unit_price'] * $orderCurrency->exchange_rate / ($companyCurrency->exchange_rate ?: 1),
                    2
                );
                $companyAmount = round(
                    $amount * $orderCurrency->exchange_rate / ($companyCurrency->exchange_rate ?: 1),
                    2
                );

                $order->items()->create([
                    'product_id'          => $item['product_id'],
                    'quantity'            => $item['quantity'],
                    'unit_price'          => $item['unit_price'],
                    'company_unit_price'  => $companyUnitPrice,
                    'vat_percent'         => $vatPercent,
                    'amount'              => $item['amount'] ?? $amount,
                    'company_amount'      => $companyAmount,
                ]);
                $subtotal += $amount;
                $total += $amount + $vatAmount;
            }

            $order->update([
                'subtotal'     => $subtotal,
                'vat_amount'   => $validated['vat_amount'],
                'total_amount' => $total
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Tạo đơn bán thành công',
                'id'      => $order->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
    public function update(Request $request, $id)
    {
        $order = SalesOrder::findOrFail($id);
        $old = $order->toArray();
        if ($order->status !== 'pending') {

            return response()->json([
                'message' => 'Chỉ được chỉnh sửa đơn hàng đang chờ xử lý.'
            ], 422);
        }

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'currency_id' => ['nullable', 'exists:currencies,id'],
            'province_id' => ['nullable'],
            'ward_id' => ['nullable'],
            'address_detail' => ['nullable', 'string', 'max:500'],
            'expected_delivery_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'vat_amount' => ['required', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.vat_percent' => ['nullable', 'numeric', 'min:0'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
        ], [
            'customer_id.required' => 'Vui lòng chọn khách hàng.',
            'customer_id.exists' => 'Khách hàng không tồn tại.',
            'currency_id.exists' => 'Tiền tệ không tồn tại.',
            'address_detail.max' => 'Địa chỉ tối đa 500 ký tự.',
            'expected_delivery_date.date' => 'Ngày giao dự kiến không hợp lệ.',
            'subtotal.required' => 'Thiếu tiền tạm tính.',
            'subtotal.numeric' => 'Tiền tạm tính phải là số.',
            'vat_amount.required' => 'Thiếu tiền VAT.',
            'vat_amount.numeric' => 'Tiền VAT phải là số.',
            'total_amount.required' => 'Thiếu tổng thanh toán.',
            'total_amount.numeric' => 'Tổng thanh toán phải là số.',
            'items.required' => 'Đơn hàng phải có ít nhất 1 sản phẩm.',
            'items.array' => 'Danh sách sản phẩm không hợp lệ.',
            'items.min' => 'Vui lòng chọn ít nhất 1 sản phẩm.',
            'items.*.product_id.required' => 'Vui lòng chọn sản phẩm.',
            'items.*.product_id.exists' => 'Sản phẩm không tồn tại.',
            'items.*.quantity.required' => 'Vui lòng nhập số lượng.',
            'items.*.quantity.numeric' => 'Số lượng phải là số.',
            'items.*.quantity.min' => 'Số lượng phải lớn hơn 0.',
            'items.*.unit_price.required' => 'Vui lòng nhập đơn giá.',
            'items.*.unit_price.numeric' => 'Đơn giá phải là số.',
            'items.*.unit_price.min' => 'Đơn giá phải lớn hơn 0.',
            'items.*.vat_percent.numeric' => 'Phần trăm VAT phải là số.',
            'items.*.vat_percent.min' => 'Phần trăm VAT không âm.',
            'items.*.amount.required' => 'Vui lòng nhập thành tiền.',
            'items.*.amount.numeric' => 'Thành tiền phải là số.',
        ]);
        DB::beginTransaction();

        try {
            $orderCurrency = Currency::findOrFail($validated['currency_id']);

            // Lấy tiền tệ mặc định của công ty
            $company = auth()->user()->company ?? auth()->user()->companies()->first();
            if (!$company) throw new \Exception('Người dùng chưa thuộc công ty nào');

            $companyCurrency = $company->currencies()->wherePivot('is_default', true)->first();
            if (!$companyCurrency) throw new \Exception('Công ty chưa cấu hình tiền tệ mặc định');

            $exchangeRate = $orderCurrency->exchange_rate ?? 1;

            $order->update([
                'customer_id'           => $validated['customer_id'],
                'currency_id'           => $validated['currency_id'] ?? null,
                'exchange_rate'         => $exchangeRate,
                'province_id'           => $validated['province_id'] ?? null,
                'ward_id'               => $validated['ward_id'] ?? null,
                'address_detail'        => $validated['address_detail'] ?? null,
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'note'                  => $validated['note'] ?? null,
                'subtotal'              => $validated['subtotal'],
                'vat_amount'            => $validated['vat_amount'],
                'total_amount'          => $validated['total_amount'],
            ]);

            SalesOrderItem::where('sales_order_id', $order->id)->delete();

            foreach ($validated['items'] as $item) {
                // Quy đổi đúng: giá khách hàng × tỉ_giá_đơn ÷ tỉ_giá_công_ty
                $companyUnitPrice = round(
                    $item['unit_price'] * $orderCurrency->exchange_rate / ($companyCurrency->exchange_rate ?: 1),
                    2
                );
                $companyAmount = round(
                    $item['amount'] * $orderCurrency->exchange_rate / ($companyCurrency->exchange_rate ?: 1),
                    2
                );

                SalesOrderItem::create([
                    'sales_order_id'     => $order->id,
                    'product_id'         => $item['product_id'],
                    'quantity'           => $item['quantity'],
                    'unit_price'         => $item['unit_price'],
                    'company_unit_price' => $companyUnitPrice,
                    'vat_percent'        => $item['vat_percent'] ?? 0,
                    'amount'             => $item['amount'],
                    'company_amount'     => $companyAmount,
                ]);
            }
            DB::commit();

            return response()->json(['message' => 'Cập nhật đơn hàng thành công.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function approve($id, CustomerDebtService $customerDebtService)
    {
        $order = SalesOrder::findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Đơn đã xử lý'
            ], 422);
        }

        DB::transaction(function () use ($order, $customerDebtService) {
            $order->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Tự động phát sinh công nợ phải thu KH khi đơn bán được duyệt
            $customerDebtService->createFromSalesOrder($order);
        });

        return response()->json([
            'message' => 'Duyệt đơn bán thành công'
        ]);
    }
    public function stockOutData($id)
    {
        $order = SalesOrder::with([
            'customer',
            'currency',
            'items.product.unit',
            'warehouseSlips.items'
        ])->findOrFail($id);

        $companyCurrency = $this->getCompanyCurrency();

        foreach ($order->items as $item) {
            $exported = 0;
            foreach ($order->warehouseSlips as $slip) {
                // chỉ tính phiếu đã duyệt
                if (!in_array($slip->status, ['approved', 'pending'])) {
                    continue;
                }
                foreach ($slip->items as $slipItem) {
                    if ($slipItem->product_id == $item->product_id) {
                        $exported += $slipItem->quantity;
                    }
                }
            }
            $item->exported_quantity = $exported;
            $displayPrice = round(
                $item->unit_price * $order->exchange_rate /
                    ($companyCurrency->exchange_rate ?: 1),
                2
            );

            $item->unit_price = $displayPrice;
            $item->amount = round($displayPrice * $item->quantity, 2);
        }

        $order->subtotal = round($order->items->sum('amount'), 2);
        $order->vat_amount = round(($order->vat_amount ?? 0) * ($order->exchange_rate ?? 1), 2);
        $order->total_amount = round($order->subtotal + $order->vat_amount, 2);
        $order->setRelation('currency', $companyCurrency ?: $order->currency);

        return response()->json($order);
    }
}
