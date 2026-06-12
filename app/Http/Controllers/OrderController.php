<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\WarehouseSlip;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // =====================
    // INDEX (purchase / sale)
    // =====================
    public function index(Request $request)
    {
        $query = Order::with([
            'purchaseOrder.supplier',
            'purchaseOrder.currency',
            'items.product',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }
        if ($request->filled('order_id')) {
            $query->where('order_id', $request->order_id);
        }
        return $query
            ->latest()
            ->paginate(5)
            ->through(function ($o) {

                return [
                    'id' => $o->id, // <-- ID của ORDER
                    'code' => $o->code,

                    'status' => $o->status,

                    'supplier' => $o->purchaseOrder?->supplier,

                    'currency' => $o->purchaseOrder?->currency,

                    'expected_received_date'
                    => $o->purchaseOrder?->expected_received_date,

                    'total_amount'
                    => $o->purchaseOrder?->total_amount ?? 0,

                    'purchase_order_id'
                    => $o->purchase_order_id,

                    'items' => $o->items->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'product_name' => $item->product?->name,
                            'quantity' => $item->quantity,
                        ];
                    }),
                ];
            });
    }
    public function stockInData($id)
    {
        $order = Order::with([
            'purchaseOrder.supplier',
            'purchaseOrder.currency',
            'items.product.unit',
            'warehouseSlips.items'
        ])->findOrFail($id);
        if ($order->status === 'completed') {
            return response()->json([
                'message' => 'Đơn hàng đã nhập đầy đủ'
            ], 422);
        }
        foreach ($order->items as $item) {

            $received = 0;

            foreach ($order->warehouseSlips as $slip) {

                foreach ($slip->items as $slipItem) {

                    if ($slipItem->product_id == $item->product_id) {
                        $received += $slipItem->quantity;
                    }
                }
            }

            $item->received_quantity = $received;
        }

        return $order;
    }
}
