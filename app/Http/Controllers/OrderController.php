<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // =====================
    // INDEX
    // =====================
    public function index(Request $request)
    {
        $query = Order::with(['items.product', 'warehouse']);

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }
        // WAREHOUSE FILTER 👇 THÊM MỚI
        if ($request->filled('warehouse_id') && $request->warehouse_id !== 'all') {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }

        $orders = $query
            ->orderByDesc('id')
            ->paginate(5)
            ->through(function ($o) {
                return [
                    'id' => $o->id,
                    'code' => $o->code,
                    'type' => $o->type,
                    'status' => $o->status,

                    'warehouse_id' => $o->warehouse_id,
                    'warehouse_name' => $o->warehouse?->name,

                    'note' => $o->note,
                    'created_at' => $o->created_at?->format('Y-m-d'),

                    'items' => $o->items->map(function ($i) {
                        return [
                            'product_id' => $i->product_id,
                            'product_name' => $i->product?->name,
                            'quantity' => $i->quantity,
                        ];
                    }),
                ];
            });

        return response()->json($orders);
    }

    // =====================
    // STORE
    // =====================
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'warehouse_id' => 'required',
            'items' => 'required|array',
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|min:1',
        ]);

        return DB::transaction(function () use ($request) {

            $warehouse = Warehouse::findOrFail($request->warehouse_id);

            // 🔥 generate code an toàn
            $code = $this->generateOrderCode($warehouse);

            $order = Order::create([
                'code' => $code,
                'type' => $request->type,
                'warehouse_id' => $request->warehouse_id,
                'status' => 'draft',
                'note' => $request->note,
            ]);

            foreach ($request->items as $item) {
                $order->items()->create($item);
            }

            return response()->json([
                'message' => 'Thêm đơn thành công',
                'data' => $order
            ]);
        });
    }

    // =====================
    // SHOW
    // =====================
    public function show($id)
    {
        $order = Order::with(['items.product', 'warehouse'])->findOrFail($id);

        return response()->json([
            'id' => $order->id,
            'code' => $order->code,
            'type' => $order->type,
            'status' => $order->status,

            'warehouse_id' => $order->warehouse_id,
            'warehouse_name' => $order->warehouse?->name,

            'note' => $order->note,
            'created_at' => $order->created_at?->format('Y-m-d'),

            'items' => $order->items->map(fn($i) => [
                'product_id' => $i->product_id,
                'product_name' => $i->product?->name,
                'quantity' => $i->quantity,
            ]),
        ]);
    }

    // =====================
    // UPDATE (BẠN ĐANG THIẾU)
    // =====================
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'type' => 'required|in:purchase,sale',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order->update([
            'type' => $request->type,
            'warehouse_id' => $request->warehouse_id,
            'note' => $request->note,
        ]);

        // ❗ reset items cũ
        $order->items()->delete();

        // insert lại items mới
        foreach ($request->items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            ]);
        }

        return response()->json([
            'message' => 'Cập nhật đơn thành công',
            'data' => $order->load(['items.product', 'warehouse'])
        ]);
    }

    // =====================
    // DELETE (OPTIONAL)
    // =====================
    private function generateOrderCode($warehouse)
    {
        $year = date('Y');
        $prefix = $warehouse->code . $year . '-';

        return DB::transaction(function () use ($prefix) {

            // LOCK dòng để chống 2 user đọc cùng lúc
            $lastOrder = Order::where('code', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();

            if (!$lastOrder) {
                return $prefix . '001';
            }

            $lastNumber = (int) str_replace($prefix, '', $lastOrder->code);
            $next = $lastNumber + 1;

            return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
        });
    }
}
