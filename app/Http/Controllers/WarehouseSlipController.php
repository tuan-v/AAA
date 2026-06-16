<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\WarehouseSlip;
use App\Models\WarehouseSlipItem;
use App\Models\WarehouseProductStock;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseSlipController extends Controller
{
    // =========================
    // LIST
    // =========================
    public function index(Request $request)
    {
        $query = WarehouseSlip::with(['warehouse', 'items', 'createdBy', 'approvedBy', 'purchaseOrder']);


        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->type === 'import') {
            $query->where('type', 'import');
        }

        if ($request->type === 'export') {
            $query->where('type', 'export');
        }

        if ($request->filled('warehouse_id') && $request->warehouse_id !== 'all') {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }
        // if ($request->filled('status')) {
        //     $query->where('status', $request->status);
        // }
        // if ($request->filled('context')) {

        //     if ($request->context === 'approved_only') {
        //         $query->where('status', 'approved');
        //     }

        //     if ($request->context === 'manage') {
        //         // trang tạo phiếu → xem tất cả
        //         // không filter status
        //     }
        // } else {
        //     // default (an toàn)
        //     $query->where('status', 'approved');
        // }
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
                'created_by' => [
                    'id' => $item->created_by,
                    'name' => $item->createdBy?->name,
                ],
                'approved_by' => [
                    'id' => $item->approved_by,
                    'name' => $item->approvedBy?->name,
                ],
                'created_at' => $item->created_at->format('d/m/Y'),
                'approved_at' => $item->approved_at?->format('d/m/Y H:i') ?? null,
                'note' => $item->note,
                'total_items' => $item->items->count(),
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
            // dd($order->items->toArray());
            // CREATE SLIP
            $slip = WarehouseSlip::create([
                'code' => $this->generateCode('import'),
                'type' => 'import',

                'purchase_order_id' => $order->id,

                'warehouse_id' => $request->warehouse_id,
                'note' => $request->note,
                'status' => 'pending',
                'created_by' => auth()->id(),

            ]);

            foreach ($request->items as $item) {

                $qty = (int) ($item['import_quantity'] ?? $item['quantity']);

                if ($qty <= 0) continue;

                $poItem = $order->items
                    ->firstWhere('product_id', $item['product_id']);

                WarehouseSlipItem::create([
                    'slip_id'       => $slip->id,
                    'product_id'    => $item['product_id'],
                    'quantity'      => $qty,

                    'price'         => $poItem?->price ?? 0,
                    'company_price' => $poItem->company_price ?? 0,
                ]);
            }

            $this->updateOrderStatus($order->id);
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
    private function updateOrderStatus($orderId)
    {
        $order = PurchaseOrder::with('items')->findOrFail($orderId);

        $importedMap = WarehouseSlipItem::query()
            ->selectRaw('product_id, SUM(quantity) as total')
            ->whereIn('slip_id', function ($q) use ($orderId) {
                $q->select('id')
                    ->from('warehouse_slips')
                    ->where('purchase_order_id', $orderId)
                    ->where('type', 'import')
                    ->where('status', 'approved');
            })
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $totalItems = 0;
        $importedItems = 0;
        $completed = true;
        $hasAny = false;

        foreach ($order->items as $item) {

            $totalItems += $item->quantity;

            $importedQty = $importedMap[$item->product_id] ?? 0;

            if ($importedQty > 0) {
                $hasAny = true;
            }

            $importedItems += $importedQty;

            if ($importedQty < $item->quantity) {
                $completed = false;
            }
        }

        if (!$hasAny) {
            $order->status = 'approved'; // chưa nhập
        } elseif ($completed) {
            $order->status = 'completed'; // nhập đủ
        } else {
            $order->status = 'partial'; // nhập 1 phần
        }

        $order->save();
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
    private function updateStockFromPO($warehouseId, $productId, $qty)
    {
        $stock = \App\Models\WarehouseProductStock::firstOrCreate([
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,

        ]);
        $stock->quantity += $qty;
        $stock->save();

        $product = Product::find($productId);

        if ($product) {
            $product->quantity += $qty;
            $product->save();
        }
    }
    private function updateProductPriceFromPO(
        $productId,
        $companyPrice
    ) {
        $product = Product::findOrFail($productId);

        $product->update([
            'purchase_price' => $companyPrice,
        ]);
    }
    public function approve($id)
    {
        $slip = WarehouseSlip::with([
            'items',
            'purchaseOrder'
        ])->findOrFail($id);

        if ($slip->status !== 'pending') {
            return response()->json([
                'message' => 'Phiếu đã được xử lý'
            ], 422);
        }

        DB::transaction(function () use ($slip) {

            foreach ($slip->items as $item) {

                $this->updateStockFromPO(
                    $slip->warehouse_id,
                    $item->product_id,
                    $item->quantity
                );

                $this->updateProductPriceFromPO(
                    $item->product_id,
                    $item->company_price
                );
            }

            $slip->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        $this->updateOrderStatus($slip->purchase_order_id);

        return response()->json([
            'message' => 'Duyệt phiếu thành công'
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
