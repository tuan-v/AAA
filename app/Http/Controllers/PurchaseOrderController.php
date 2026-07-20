<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\WarehouseProductStock;
use App\Models\WarehouseSlipItem;
use App\Services\ActivityLogService;
use App\Services\SupplierDebtService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\OrderQuantityValidationService;
use Illuminate\Support\Facades\DB;
use App\Services\CodeGeneratorService;
use App\Services\CurrencyService;

class PurchaseOrderController extends Controller
{
    public function __construct(
        protected CurrencyService $currencyService,
        protected CodeGeneratorService $codeGenerator
    ) {}
    private function getCompanyCurrency()
    {
        $company = auth()->user()->company ?? auth()->user()->companies()->first();
        return $company ? $company->default_currency : null;
    }

    private function companyId(): int
    {
        $companyId = auth()->user()->company_id
            ?? auth()->user()->companies()->value('companies.id');

        abort_unless($companyId, 403, 'Tài khoản chưa thuộc công ty nào.');

        return (int) $companyId;
    }

    // =========================
    // INDEX
    // =========================
    public function index(Request $request)
    {
        $query = PurchaseOrder::with([
            'supplier',
            'currency',
            'items',
            'items.product'
        ])->whereIn('status', [
            'pending',
            'approved',
            'partial',
            'completed',
            'cancelled'
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        $perPage = min((int) $request->input('per_page', 10), 100);
        $orders = $query->latest()->paginate($perPage);
        $companyCurrency = $this->getCompanyCurrency();

        $orders->getCollection()->transform(function ($item) use ($companyCurrency) {
            foreach ($item->items as $i) {
                $displayPrice =
                    $i->price
                    * $item->exchange_rate
                    / $companyCurrency->exchange_rate;

                $i->price = round($displayPrice, 2);
                $i->amount = round($displayPrice * $i->quantity, 2);
            }
            $subtotal = $item->items->sum('amount');
            $vatAmount = $item->items->sum(fn ($line) =>
                (float) $line->amount * ((float) ($line->vat_percent ?? 0) / 100)
            );
            $total = $subtotal + $vatAmount;

            return [
                'id' => $item->id,
                'code' => $item->code,
                'status' => $item->status,
                'supplier' => $item->supplier,
                'currency' => $companyCurrency ?: $item->currency,
                'order_currency' => $item->currency,
                'currency_id' => $item->currency_id,
                'items' => $item->items,
                'subtotal' => round($subtotal, 2),
                'vat_amount' => round($vatAmount, 2),
                'total_amount' => round($total, 2),
                'expected_received_date' => $item->expected_received_date
                    ? $item->expected_received_date->format('d/m/Y')
                    : null,
                'exchange_rate' => $item->exchange_rate,
            ];
        });

        return response()->json($orders);
    }
    public function warehouseIndex(Request $request)
    {
        $query = PurchaseOrder::with([
            'supplier',
            'currency',
            'items',
            'items.product'
        ]);

        $query->whereIn('status', [
            'approved',
            'partial',
            'completed'
        ]);

        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $orders = $query->latest()->paginate(10);
        $companyCurrency = $this->getCompanyCurrency();

        $orders->getCollection()->transform(function ($item) use ($companyCurrency) {
            foreach ($item->items as $i) {
                $displayPrice =
                    $i->price
                    * $item->exchange_rate
                    / $companyCurrency->exchange_rate;

                $i->price = round($displayPrice, 2);
                $i->amount = round($displayPrice * $i->quantity, 2);
            }
            $subtotal = $item->items->sum('amount');
            $vatAmount = $item->items->sum(fn ($line) =>
                (float) $line->amount * ((float) ($line->vat_percent ?? 0) / 100)
            );
            $total = $subtotal + $vatAmount;

            return [
                'id' => $item->id,
                'code' => $item->code,
                'status' => $item->status,
                'supplier' => $item->supplier,
                'currency' => $companyCurrency ?: $item->currency,
                'items' => $item->items,
                'subtotal' => round($subtotal, 2),
                'vat_amount' => round($vatAmount, 2),
                'total_amount' => round($total, 2),
                'expected_received_date' => $item->expected_received_date
                    ? $item->expected_received_date->format('d/m/Y')
                    : null,
            ];
        });

        return response()->json($orders);
    }
    public function show($id)
    {
        $order = PurchaseOrder::with([
            'supplier',
            'currency',
            'items.product',
            'createdBy',
            'approvedBy',
            'warehouseSlips',
            'warehouseSlips.items',
        ])->findOrFail($id);

        $companyCurrency = $this->getCompanyCurrency();

        foreach ($order->items as $item) {

            $received = 0;

            foreach ($order->warehouseSlips as $slip) {

                if ($slip->status !== 'approved') {
                    continue;
                }

                foreach ($slip->items as $slipItem) {

                    if ($slipItem->product_id == $item->product_id) {
                        $received += $slipItem->quantity;
                    }
                }
            }

            $item->received_quantity = $received;

            // Giữ nguyên giá NCC
            $item->amount = $item->price * $item->quantity;
        }

        $order->subtotal = $order->items->sum('amount');
        $order->vat_amount = $order->items->sum(fn ($line) =>
            (float) $line->amount * ((float) ($line->vat_percent ?? 0) / 100)
        );
        $order->total_amount = $order->subtotal + $order->vat_amount;

        // Giữ nguyên currency của đơn hàng
        // KHÔNG setRelation thành companyCurrency
        return response()->json($order);
    }
    // =========================
    // STORE (FIXED)
    // =========================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required',
            'currency_id' => 'required',
            'expected_received_date' => 'required',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.vat_percent' => 'nullable|numeric|min:0|max:10',
        ], [
            'supplier_id.required' => 'Nhà cung cấp không được để trống',
            'currency_id.required' => 'Đơn vị tiền tệ không được để trống',
            'expected_received_date' => 'Ngày nhận hàng không được để trống',
            'items.required' => 'Sản phẩm không được để trống',
            'items.array' => 'Sản phẩm không hợp lệ',
            'items.min' => 'Sản phẩm không được để trống',
            'items.*.product_id.required' => 'Sản phẩm không được để trống',
            'items.*.quantity.required' => 'Số lượng không được để trống',
            'items.*.quantity.numeric' => 'Số lượng phải là số',
            'items.*.quantity.min' => 'Số lượng phải lớn hơn 0',
            'items.*.price.required' => 'Giá không được để trống',
            'items.*.price.numeric' => 'Giá phải là số',
            'items.*.price.min' => 'Giá phải lớn hơn hoặc bằng 0',
            'items.*.vat_percent.numeric' => 'VAT phải là số',
            'items.*.vat_percent.min' => 'VAT không được nhỏ hơn 0%',
            'items.*.vat_percent.max' => 'VAT không được vượt quá 10%',
        ]);

        app(OrderQuantityValidationService::class)->validate($validated['items']);

        try {

            DB::beginTransaction();

            $total = 0;


            $orderCurrency = Currency::findOrFail(
                $request->currency_id
            );
            $company = auth()
                ->user()
                ->companies()
                ->first();

            if (!$company) {
                throw new \Exception('Người dùng chưa thuộc công ty nào');
            }

            $companyCurrency = $company
                ->currencies()
                ->wherePivot('is_default', true)
                ->first();

            if (!$companyCurrency) {
                throw new \Exception('Công ty chưa cấu hình tiền tệ mặc định');
            }

            $validated['exchange_rate']
                = $orderCurrency->exchange_rate;
            $order = PurchaseOrder::create([
                'supplier_id' => $request->supplier_id,
                'currency_id' => $request->currency_id,
                'expected_received_date' => $request->expected_received_date,
                'note' => $request->note,
                'status' => 'pending',
                'total_amount' => 0,
                'exchange_rate' => $orderCurrency->exchange_rate,
                'created_by' => auth()->id(),
            ]);
            $subtotal = 0;
            $vatAmount = 0;
            foreach ($request->items as $item) {

                $amount = $item['quantity'] * $item['price'];
                $itemVat = $amount * ((float) ($item['vat_percent'] ?? 0) / 100);

                $companyPrice =
                    $item['price']
                    * $orderCurrency->exchange_rate
                    / $companyCurrency->exchange_rate;
                $order->items()->create([
                    'product_id'   => $item['product_id'],
                    'quantity'     => $item['quantity'],
                    'price'        => $item['price'], // giá NCC
                    'company_price' => round($companyPrice, 2),  // giá quy đổi
                    'amount'       => $amount,
                    'vat_percent'  => $item['vat_percent'] ?? 0,
                ]);

                $subtotal += $amount;
                $vatAmount += $itemVat;
                $total += $amount + $itemVat;
            }

            $order->update([
                'subtotal' => round($subtotal, 2),
                'vat_amount' => round($vatAmount, 2),
                'total_amount' => round($total, 2),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Tạo đơn thành công',
                'id' => $order->id
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    // =========================
    // UPDATE
    // =========================
    public function update(Request $request, $id)
    {
        $order = PurchaseOrder::where('company_id', $this->companyId())->findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Chỉ được chỉnh sửa đơn mua đang chờ duyệt.',
            ], 422);
        }

        $validated = $request->validate([
            'supplier_id' => 'required',
            'currency_id' => 'required',
            'expected_received_date' => 'required',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.vat_percent' => 'nullable|numeric|min:0|max:10',
        ], [
            'supplier_id.required' => 'Nhà cung cấp không được để trống',
            'currency_id.required' => 'Đơn vị tiền tệ không được để trống',
            'expected_received_date' => 'Ngày nhận hàng không được để trống',
            'items.required' => 'Sản phẩm không được để trống',
            'items.array' => 'Sản phẩm không hợp lệ',
            'items.min' => 'Sản phẩm không được để trống',
            'items.*.product_id.required' => 'Sản phẩm không được để trống',
            'items.*.quantity.required' => 'Số lượng không được để trống',
            'items.*.quantity.numeric' => 'Số lượng phải là số',
            'items.*.quantity.min' => 'Số lượng phải lớn hơn 0',
            'items.*.price.required' => 'Giá không được để trống',
            'items.*.price.numeric' => 'Giá phải là số',
            'items.*.price.min' => 'Giá phải lớn hơn hoặc bằng 0',
            'items.*.vat_percent.numeric' => 'VAT phải là số',
            'items.*.vat_percent.min' => 'VAT không được nhỏ hơn 0%',
            'items.*.vat_percent.max' => 'VAT không được vượt quá 10%',
        ]);

        app(OrderQuantityValidationService::class)->validate($validated['items']);

        DB::transaction(function () use ($request, $order) {

            $order->load('items');

            $total = 0;
            $orderCurrency = Currency::findOrFail(
                $request->currency_id
            );

            $company = auth()
                ->user()
                ->companies()
                ->first();

            if (!$company) {
                throw new \Exception('Người dùng chưa thuộc công ty nào');
            }

            $companyCurrency = $company
                ->currencies()
                ->wherePivot('is_default', true)
                ->first();

            if (!$companyCurrency) {
                throw new \Exception('Công ty chưa cấu hình tiền tệ');
            }
            $order->update([
                'supplier_id' => $request->supplier_id,
                'currency_id' => $request->currency_id,
                'expected_received_date' => $request->expected_received_date,
                'note' => $request->note,
                'exchange_rate' => $orderCurrency->exchange_rate,
            ]);

            $order->items()->delete();

            $subtotal = 0;
            $vatAmount = 0;
            foreach ($request->items as $item) {

                $amount = $item['quantity'] * $item['price'];
                $itemVat = $amount * ((float) ($item['vat_percent'] ?? 0) / 100);

                $companyPrice =
                    $item['price']
                    * $orderCurrency->exchange_rate
                    / $companyCurrency->exchange_rate;

                $order->items()->create([
                    'product_id'    => $item['product_id'],
                    'quantity'      => $item['quantity'],
                    'price'         => $item['price'],
                    'company_price' =>  round($companyPrice, 2),
                    'amount'        => $amount,
                    'vat_percent'   => $item['vat_percent'] ?? 0,
                ]);

                $subtotal += $amount;
                $vatAmount += $itemVat;
                $total += $amount + $itemVat;
            }

            $order->update([
                'subtotal' => round($subtotal, 2),
                'vat_amount' => round($vatAmount, 2),
                'total_amount' => round($total, 2),
                'status' => 'pending'
            ]);
        });

        return response()->json([
            'message' => 'Cập nhật đơn thành công'
        ]);
    }

    // =========================
    // APPROVE (WAREHOUSE UPDATE)
    // =========================
    public function approve($id, SupplierDebtService $supplierDebtService)
    {
        $order = PurchaseOrder::with('items')
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Đơn đã xử lý'
            ], 422);
        }

        DB::transaction(function () use ($order, $supplierDebtService) {
            $order->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Tự động phát sinh công nợ phải trả NCC khi đơn mua được duyệt
        });

        return response()->json([
            'message' => 'Duyệt đơn thành công'
        ]);
    }

    public function cancel($id)
    {
        $order = PurchaseOrder::withCount('warehouseSlips')
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Chỉ được hủy đơn mua đang chờ duyệt.',
            ], 422);
        }

        if ($order->warehouse_slips_count > 0) {
            return response()->json([
                'message' => 'Không thể hủy đơn mua đã phát sinh phiếu nhập warehouse.',
            ], 422);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Hủy đơn mua thành công.']);
    }

    public function destroy($id)
    {
        $order = PurchaseOrder::withCount('warehouseSlips')
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        if ($order->status !== 'pending' || $order->warehouse_slips_count > 0) {
            return response()->json([
                'message' => 'Chỉ được xóa đơn mua đang chờ duyệt và chưa phát sinh phiếu warehouse.',
            ], 422);
        }

        DB::transaction(function () use ($order) {
            $order->items()->delete();
            $order->delete();
        });

        return response()->json(['message' => 'Xóa đơn mua thành công.']);
    }

    // =========================
    // STOCK IN DATA
    // =========================
    public function stockInData($id)
    {
        $order = PurchaseOrder::with([
            'supplier',
            'currency',
            'items.product.unit',
            'warehouseSlips.items'
        ])->findOrFail($id);

        $companyCurrency = $this->getCompanyCurrency();

        foreach ($order->items as $item) {
            $received = $order->warehouseSlips
                ->where('status', 'approved')
                ->where('type', 'import')
                ->flatMap->items
                ->where('product_id', $item->product_id)
                ->sum('quantity');

            $item->received_quantity = $received;
            $displayPrice =
                $item->price
                * $order->exchange_rate
                / $companyCurrency->exchange_rate;

            $item->price = round($displayPrice, 2);
            $item->amount = round($displayPrice * $item->quantity, 2);
        }

        $order->subtotal = round($order->items->sum('amount'), 2);
        $order->vat_amount = round($order->items->sum(fn ($line) =>
            (float) $line->amount * ((float) ($line->vat_percent ?? 0) / 100)
        ), 2);
        $order->total_amount = round($order->subtotal + $order->vat_amount, 2);
        $order->setRelation('currency', $companyCurrency ?: $order->currency);
        $receivedMap = WarehouseSlipItem::query()
            ->selectRaw('product_id, SUM(quantity) as total')
            ->whereIn('slip_id', function ($q) use ($order) {
                $q->select('id')
                    ->from('warehouse_slips')
                    ->where('purchase_order_id', $order->id)
                    ->where('type', 'import')
                    ->whereIn('status', ['pending', 'approved']);
            })
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        foreach ($order->items as $item) {
            $item->received_quantity = $receivedMap[$item->product_id] ?? 0;
        }

        return response()->json($order);
    }
}
