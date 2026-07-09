<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\WarehouseProductStock;
use App\Services\ActivityLogService;
use App\Services\SupplierDebtService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    private function getCompanyCurrency()
    {
        $company = auth()->user()->company ?? auth()->user()->companies()->first();
        return $company ? $company->default_currency : null;
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
            'completed'
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

        $orders = $query->latest()->paginate(5);
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
            $total = $item->items->sum('amount');

            return [
                'id' => $item->id,
                'code' => $item->code,
                'status' => $item->status,
                'supplier' => $item->supplier,
                'currency' => $companyCurrency ?: $item->currency,
                'items' => $item->items,
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

        $orders = $query->latest()->paginate(5);
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
            $total = $item->items->sum('amount');

            return [
                'id' => $item->id,
                'code' => $item->code,
                'status' => $item->status,
                'supplier' => $item->supplier,
                'currency' => $companyCurrency ?: $item->currency,
                'items' => $item->items,
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

        $order->total_amount = $order->items->sum('amount');

        // Giữ nguyên currency của đơn hàng
        // KHÔNG setRelation thành companyCurrency
        return response()->json($order);
    }
    // =========================
    // STORE (FIXED)
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'currency_id' => 'required',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ], [
            'supplier_id.required' => 'Nhà cung cấp không được để trống',
            'currency_id.required' => 'Đơn vị tiền tệ không được để trống',
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
        ]);

        try {

            DB::beginTransaction();

            $total = 0;

            $last = PurchaseOrder::latest('id')->first();
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
                'code' => 'PO' . str_pad(($last?->id ?? 0) + 1, 5, '0', STR_PAD_LEFT),
                'supplier_id' => $request->supplier_id,
                'currency_id' => $request->currency_id,
                'expected_received_date' => $request->expected_received_date,
                'note' => $request->note,
                'status' => 'pending',
                'total_amount' => 0,
                'exchange_rate' => $orderCurrency->exchange_rate,
                'created_by' => auth()->id(),
            ]);
            foreach ($request->items as $item) {

                $amount = $item['quantity'] * $item['price'];

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
                ]);

                $total += $amount;
            }

            $order->update([
                'total_amount' => $total
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
        $request->validate([
            'supplier_id' => 'required',
            'currency_id' => 'required',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ], [
            'supplier_id.required' => 'Nhà cung cấp không được để trống',
            'currency_id.required' => 'Đơn vị tiền tệ không được để trống',
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
        ]);

        DB::transaction(function () use ($request, $id) {

            $order = PurchaseOrder::with('items')->findOrFail($id);

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

            foreach ($request->items as $item) {

                $amount = $item['quantity'] * $item['price'];

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
                ]);

                $total += $amount;
            }

            $order->update([
                'total_amount' => $total,
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
        $order = PurchaseOrder::with('items')->findOrFail($id);

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
            $supplierDebtService->createFromPurchaseOrder($order);
        });

        return response()->json([
            'message' => 'Duyệt đơn thành công'
        ]);
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

        $order->total_amount = round($order->items->sum('amount'), 2);
        $order->setRelation('currency', $companyCurrency ?: $order->currency);

        return response()->json($order);
    }
}
