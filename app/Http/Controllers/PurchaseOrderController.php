<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\WarehouseProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
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

        $orders = $query->latest()->paginate(5);

        $orders->getCollection()->transform(function ($item) {

            $total = $item->items->sum(function ($i) {
                return $i->quantity * $i->price;
            });

            return [
                'id' => $item->id,
                'code' => $item->code,
                'status' => $item->status,
                'supplier' => $item->supplier,
                'currency' => $item->currency,
                'items' => $item->items,
                'total_amount' => $total,
                'expected_received_date' => $item->expected_received_date,
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

        $orders = $query->latest()->paginate(5);

        $orders->getCollection()->transform(function ($item) {

            return [
                'id' => $item->id,
                'code' => $item->code,
                'status' => $item->status,
                'supplier' => $item->supplier,
                'currency' => $item->currency,
                'items' => $item->items,
                'total_amount' => $item->items->sum(fn($i) => $i->quantity * $i->price),
                'expected_received_date' => $item->expected_received_date,

            ];
        });

        return response()->json($orders);
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
            'items' => 'required|array|min:1',
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
    public function approve($id)
    {
        $order = PurchaseOrder::findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Đơn đã xử lý'
            ], 422);
        }

        $order->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

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

        foreach ($order->items as $item) {

            $received = $order->warehouseSlips
                ->where('status', 'approved')
                ->where('type', 'import')
                ->flatMap->items
                ->where('product_id', $item->product_id)
                ->sum('quantity');

            $item->received_quantity = $received;
        }

        return response()->json($order);
    }
}
