<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\WarehouseSlip;
use App\Models\WarehouseSlipItem;
use App\Models\WarehouseProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseSlipController extends Controller
{
    // =========================
    // LIST
    // =========================
    public function index(Request $request)
    {
        $query = WarehouseSlip::with(['warehouse', 'items']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('warehouse_id') && $request->warehouse_id !== 'all') {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('purchase_order_id')) {
            $query->where(
                'purchase_order_id',
                $request->purchase_order_id
            );
        }
        $slips = $query->latest()->paginate(10);

        $slips->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'code' => $item->code,
                'type' => $item->type,
                'status' => $item->status,
                'purchase_order_code' => $item->purchaseOrder?->code,
                'warehouse' => [
                    'id' => $item->warehouse_id,
                    'name' => $item->warehouse?->name,
                ],
                'note' => $item->note,
                'total_items' => $item->items->count(),
                'created_at' => $item->created_at->format('d/m/Y'),
            ];
        });

        return response()->json($slips);
    }

    // =========================
    // SHOW
    // =========================
    public function show($id)
    {
        $slip = WarehouseSlip::with(['warehouse', 'items.product'])
            ->findOrFail($id);

        return response()->json($slip);
    }

    // =========================
    // CREATE IMPORT SLIP
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|integer',
            'items' => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {

            /** @var PurchaseOrder $order */
            $order = PurchaseOrder::with('items')
                ->findOrFail($request->purchase_order_id);

            // CREATE SLIP
            $slip = WarehouseSlip::create([
                'code' => $this->generateCode('import'),
                'type' => 'import',

                'purchase_order_id' => $order->id,

                'warehouse_id' => $request->warehouse_id,
                'note' => $request->note,
                'status' => 'approved',
            ]);

            foreach ($request->items as $item) {

                $qty = (int) ($item['import_quantity'] ?? $item['quantity']);

                if ($qty <= 0) continue;

                WarehouseSlipItem::create([
                    'slip_id' => $slip->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $qty,
                    'price' => $item['price'] ?? 0,
                ]);

                // $this->updateStock(
                //     $request->warehouse_id,
                //     $item['product_id'],
                //     $qty,
                //     'import'
                // );
            }

            $this->updateOrderStatus($order);
        });

        return response()->json([
            'success' => true,
            'message' => 'Nhập kho thành công'
        ]);
    }

    // =========================
    // UPDATE NOTE
    // =========================
    public function update(Request $request, $id)
    {
        $slip = WarehouseSlip::findOrFail($id);

        $slip->update([
            'note' => $request->note
        ]);

        return response()->json([
            'message' => 'Cập nhật thành công'
        ]);
    }

    // =========================
    // CODE GENERATOR (SAFE)
    // =========================
    private function generateCode($type)
    {
        $prefix = $type === 'import' ? 'PN' : 'PX';

        $lastId = WarehouseSlip::max('id') ?? 0;
        $nextId = $lastId + 1;

        return $prefix . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    // =========================
    // STOCK UPDATE
    // =========================
    private function updateStock($warehouseId, $productId, $qty, $type)
    {
        $stock = WarehouseProductStock::firstOrCreate([
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
        ]);

        if ($type === 'import') {
            $stock->quantity += $qty;
        } else {
            $stock->quantity = max(0, $stock->quantity - $qty);
        }

        $stock->save();
    }

    // =========================
    // ORDER STATUS UPDATE (OPTIMIZED)
    // =========================
    // private function updateOrderStatus(PurchaseOrder $order)
    // {
    //     $order->load([
    //         'items',

    //     ]);

    //     $importedMap = WarehouseSlipItem::query()
    //         ->selectRaw('product_id, SUM(quantity) as total')
    //         ->whereHas('slip', function ($q) use ($order) {
    //             $q->where('purchase_order_id', $order->id)
    //                 ->where('type', 'import');
    //         })
    //         ->groupBy('product_id')
    //         ->pluck('total', 'product_id');

    //     $hasImported = false;
    //     $completed = true;

    //     foreach ($order->items as $item) {

    //         $importedQty = $importedMap[$item->product_id] ?? 0;

    //         if ($importedQty > 0) {
    //             $hasImported = true;
    //         }

    //         if ($importedQty < $item->quantity) {
    //             $completed = false;
    //         }
    //     }

    //     $order->status = $completed
    //         ? 'completed'
    //         : ($hasImported ? 'partial' : 'approved');

    //     $order->save();
    // }
    private function updateOrderStatus(PurchaseOrder $order)
    {
        $order->load([
            'items',
            'warehouseSlips.items'
        ]);

        $completed = true;
        $hasImported = false;
        foreach ($order->warehouseSlips as $slip) {

            if ($slip->status !== 'approved') {
                continue;
            }
            foreach ($order->items as $item) {

                $importedQty = 0;

                foreach ($order->warehouseSlips as $slip) {

                    foreach ($slip->items as $slipItem) {

                        if ($slipItem->product_id == $item->product_id) {
                            $importedQty += $slipItem->quantity;
                        }
                    }
                }

                if ($importedQty > 0) {
                    $hasImported = true;
                }

                if ($importedQty < $item->quantity) {
                    $completed = false;
                }
            }

            if ($completed) {
                $order->status = 'completed';
            } elseif ($hasImported) {
                $order->status = 'partial';
            } else {
                $order->status = 'approved';
            }

            $order->save();
        }
    }

    // =========================
    // DROPDOWNS
    // =========================
    public function ordersForImport()
    {
        return PurchaseOrder::query()
            ->where('type', 'purchase')
            ->whereIn('status', ['approved', 'partial'])
            ->select('id', 'code')
            ->get();
    }

    public function ordersForExport()
    {
        return PurchaseOrder::query()
            ->where('type', 'sell')
            ->whereIn('status', ['approved', 'partial'])
            ->select('id', 'code')
            ->get();
    }
    public function approve($id)
    {
        $slip = WarehouseSlip::with('items')
            ->findOrFail($id);

        if ($slip->status !== 'pending') {
            return response()->json([
                'message' => 'Phiếu đã xử lý'
            ], 422);
        }

        foreach ($slip->items as $item) {

            $this->updateStock(
                $slip->warehouse_id,
                $item->product_id,
                $item->quantity,
                'import'
            );
        }

        $slip->update([
            'status' => 'ap'
        ]);

        $this->updateOrderStatus(
            PurchaseOrder::find($slip->purchase_order_id)
        );

        return response()->json([
            'message' => 'Duyệt thành công'
        ]);
    }

    public function reject($id)
    {
        $slip = WarehouseSlip::findOrFail($id);
        if ($slip->status !== 'pending') {
            return response()->json([
                'message' => 'Phiếu đã được xử lý'
            ], 422);
        }
        $slip->status = 'rejected';
        $slip->save();

        return response()->json([
            'message' => 'Từ chối phiếu thành công'
        ]);
    }
}
