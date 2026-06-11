<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // =====================
    // INDEX (purchase / sale)
    // =====================
    public function index(Request $request)
    {
        $query = PurchaseOrder::with([
            'supplier',
            'currency',
            'items.product' // 👈 nên load product luôn
        ]);

        // ✅ TYPE FILTER (purchase / sale / all)
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // STATUS FILTER
        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );
        }
        // SEARCH
        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }

        return $query
            ->latest()
            ->paginate(5)
            ->through(function ($o) {

                return [
                    'id' => $o->id,
                    'code' => $o->code,

                    'type' => $o->type, // purchase / sale
                    'status' => $o->status,

                    'supplier_name' => $o->supplier?->name ?? '—',
                    'currency_code' => $o->currency?->code ?? '—',

                    'expected_received_date' => $o->expected_received_date ?? '—',

                    // ⚠️ fix null amount
                    'total_amount' => $o->total_amount ?? 0,

                    'note' => $o->note,

                    'created_at' => optional($o->created_at)->format('Y-m-d'),

                    'items' => $o->items->map(fn($i) => [
                        'product_name' => $i->product?->name ?? '—',
                        'quantity' => $i->quantity,
                        'price' => $i->price,
                        'amount' => $i->amount,
                    ]),
                ];
            });
    }

    // =====================
    // STORE
    // =====================
    // public function store(Request $request)
    // {
    //     DB::transaction(function () use ($request) {

    //         $total = 0;

    //         $last = PurchaseOrder::latest('id')->first();

    //         $order = PurchaseOrder::create([
    //             'code' =>
    //             'PO' . str_pad(($last?->id ?? 0) + 1, 5, '0', STR_PAD_LEFT),

    //             'type' => $request->type, // 👈 QUAN TRỌNG (purchase / sale)

    //             'supplier_id' => $request->supplier_id,
    //             'currency_id' => $request->currency_id,
    //             'expected_received_date' => $request->expected_received_date,
    //             'note' => $request->note,

    //             'status' => 'pending',

    //             'total_amount' => 0,
    //         ]);

    //         foreach ($request->items as $item) {

    //             $amount = $item['quantity'] * $item['price'];

    //             $order->items()->create([
    //                 'product_id' => $item['product_id'],
    //                 'quantity' => $item['quantity'],
    //                 'price' => $item['price'],
    //                 'amount' => $amount,
    //             ]);

    //             $total += $amount;
    //         }

    //         $order->update([
    //             'total_amount' => $total,
    //         ]);
    //     });

    //     return response()->json([
    //         'message' => 'Tạo đơn thành công'
    //     ]);
    // }

    // =====================
    // UPDATE
    // =====================
    // public function update(Request $request, $id)
    // {
    //     DB::transaction(function () use ($request, $id) {

    //         $order = PurchaseOrder::with('items')->findOrFail($id);

    //         $total = 0;

    //         $order->update([
    //             'type' => $request->type, // 👈 giữ đồng bộ
    //             'supplier_id' => $request->supplier_id,
    //             'currency_id' => $request->currency_id,
    //             'expected_received_date' => $request->expected_received_date,
    //             'note' => $request->note,
    //         ]);

    //         // xoá items cũ
    //         $order->items()->delete();

    //         foreach ($request->items as $item) {

    //             $amount = $item['quantity'] * $item['price'];

    //             $order->items()->create([
    //                 'product_id' => $item['product_id'],
    //                 'quantity' => $item['quantity'],
    //                 'price' => $item['price'],
    //                 'amount' => $amount,
    //             ]);

    //             $total += $amount;
    //         }

    //         $order->update([
    //             'total_amount' => $total,
    //         ]);
    //     });

    //     return response()->json([
    //         'message' => 'Cập nhật đơn thành công'
    //     ]);
    // }
}
