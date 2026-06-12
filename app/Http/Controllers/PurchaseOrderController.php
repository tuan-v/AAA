<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with([
            'supplier',
            'currency',
            'items',
            'order',
            'items.product'
        ])
            ->where('status', 'pending');

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );
        }

        if ($request->filled('search')) {

            $query->where(
                'code',
                'like',
                "%{$request->search}%"
            );
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
                'total_amount' => $item->total_amount,
                'expected_received_date' => $item->expected_received_date,

                // ⭐ QUAN TRỌNG NHẤT
                'warehouse_order_id' => $item->order?->id,
            ];
        });

        return response()->json($orders);
    }
    public function warehouseIndex(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'currency', 'items', 'order', 'items.product'])
            ->where('status', 'approved'); // đã duyệt → sang kho

        $orders = $query->latest()->paginate(5);

        return response()->json($orders);
    }
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            $total = 0;

            $last = PurchaseOrder::latest('id')->first();

            $order = PurchaseOrder::create([
                'code' =>
                'PO' . str_pad(($last?->id ?? 0) + 1, 5, '0', STR_PAD_LEFT),

                'supplier_id' => $request->supplier_id,
                'currency_id' => $request->currency_id,
                'expected_received_date' => $request->expected_received_date,
                'note' => $request->note,

                'status' => 'pending',
                'amount' => $request->amount, // tạm thời
            ]);

            foreach ($request->items as $item) {

                $amount = $item['quantity'] * $item['price'];

                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'amount' => $amount,
                ]);

                $total += $amount;
            }

            $order->update([
                'total_amount' => $total
            ]);
        });

        return response()->json([
            'message' => 'Tạo đơn thành công'
        ]);
    }
    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {

            $order = PurchaseOrder::with('items')->findOrFail($id);

            $total = 0;

            // 1. update header
            $order->update([
                'supplier_id' => $request->supplier_id,
                'currency_id' => $request->currency_id,
                'expected_received_date' => $request->expected_received_date,
                'note' => $request->note,
            ]);

            // 2. xoá items cũ
            $order->items()->delete();

            // 3. tạo lại items mới
            foreach ($request->items as $item) {

                $amount = $item['quantity'] * $item['price'];

                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'amount' => $amount,
                ]);

                $total += $amount;
            }

            // 4. update total
            $order->update([
                'total_amount' => $total,
                'status' => 'pending', // optional reset trạng thái nếu muốn
            ]);
        });

        return response()->json([
            'message' => 'Cập nhật đơn thành công'
        ]);
    }
    public function approve($id)
    {
        DB::transaction(function () use ($id) {

            $purchaseOrder = PurchaseOrder::with('items')
                ->findOrFail($id);

            if ($purchaseOrder->status !== 'pending') {
                throw new \Exception('Đơn không thể duyệt');
            }

            $purchaseOrder->update([
                'status' => 'approved'
            ]);

            $order = Order::create([
                'purchase_order_id' => $purchaseOrder->id,
                'code' => 'ORD-' . now()->format('YmdHis'),
                'type' => 'purchase',
                'status' => 'approved',
            ]);

            foreach ($purchaseOrder->items as $item) {

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                ]);
            }
        });

        return response()->json([
            'message' => 'Duyệt thành công'
        ]);
    }

    // public function stockInData($id)
    // {
    //     return PurchaseOrder::with([
    //         'supplier',
    //         'currency',
    //         'items.product.unit'

    //     ])->findOrFail($id);
    // }
}
