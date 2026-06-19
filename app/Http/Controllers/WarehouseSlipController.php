<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
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
        $query = WarehouseSlip::with(['warehouse', 'items', 'createdBy', 'approvedBy', 'purchaseOrder', 'saleOrder']);


        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        // if ($request->type === 'import') {
        //     $query->where('type', 'import');
        // }

        // if ($request->type === 'export') {
        //     $query->where('type', 'export');
        // }
        if ($request->filled('sales_order_id')) {
            $query->where(
                'sales_order_id',
                $request->sales_order_id
            );
        }
        if ($request->filled('warehouse_id') && $request->warehouse_id !== 'all') {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
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
                'sales_order_code' => $item->saleOrder?->code,
                'warehouse' => [
                    'id' => $item->warehouse_id,
                    'name' => $item->warehouse?->name,
                    'code' => $item->warehouse?->code,
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
        $validated = $request->validate([
            'type' => 'required|in:import,export',
            'warehouse_id' => 'required|exists:warehouses,id',
            'purchase_order_id' => 'required_if:type,import|exists:purchase_orders,id',
            'sales_order_id' => 'required_if:type,export|exists:sales_orders,id',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $slip = null;
            $order = null;

            if ($validated['type'] === 'import') {
                $order = PurchaseOrder::with('items')->findOrFail($validated['purchase_order_id']);


                $slip = WarehouseSlip::create([
                    'code' => $this->generateCode('import'),
                    'type' => 'import',
                    'purchase_order_id' => $order->id,
                    'warehouse_id' => $validated['warehouse_id'],
                    'note' => $validated['note'],
                    'status' => 'pending',
                    'created_by' => auth()->id(),
                ]);
            } else { // EXPORT
                $order = SalesOrder::with('items')->findOrFail($validated['sales_order_id']);

                $companyPrice = 0;
                $slip = WarehouseSlip::create([
                    'code' => $this->generateCode('export'),
                    'type' => 'export',
                    'sales_order_id' => $order->id,
                    'warehouse_id' => $validated['warehouse_id'],
                    'note' => $validated['note'] ?? null,
                    'status' => 'pending',
                    'created_by' => auth()->id(),
                ]);
            }

            foreach ($validated['items'] as $itemData) {
                $orderItem = $order->items
                    ->firstWhere('product_id', $itemData['product_id']);
                $qty = (int)$itemData['quantity'];
                if ($qty <= 0) continue;
                if (!$orderItem) {
                    throw new \Exception(
                        'Sản phẩm không tồn tại trong đơn hàng'
                    );
                }
                $unitPrice = $validated['type'] === 'import'
                    ? $orderItem->price
                    : $orderItem->unit_price;
                $companyPrice = $validated['type'] === 'import'
                    ? ($unitPrice * ($order->exchange_rate ?? 1))
                    : 0;
                WarehouseSlipItem::create([
                    'slip_id' => $slip->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $qty,
                    'price'         => $unitPrice,
                    'company_price' => $companyPrice,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tạo phiếu kho thành công',
                'slip' => $slip
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
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

        $lastSlip = WarehouseSlip::where('type', $type)
            ->orderByDesc('id')
            ->first();

        $number = 1;

        if ($lastSlip) {
            $number = (int) substr($lastSlip->code, 2) + 1;
        }

        return $prefix . str_pad($number, 6, '0', STR_PAD_LEFT);
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
    private function updateSalesOrderStatus($orderId)
    {
        $order = SalesOrder::with('items')->findOrFail($orderId);

        $exportedMap = WarehouseSlipItem::query()
            ->selectRaw('product_id, SUM(quantity) as total')
            ->whereIn('slip_id', function ($q) use ($orderId) {
                $q->select('id')
                    ->from('warehouse_slips')
                    ->where('sales_order_id', $orderId)
                    ->where('type', 'export')
                    ->where('status', 'approved');
            })
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $completed = true;
        $hasAny = false;

        foreach ($order->items as $item) {

            $exportedQty = $exportedMap[$item->product_id] ?? 0;

            if ($exportedQty > 0) {
                $hasAny = true;
            }

            if ($exportedQty < $item->quantity) {
                $completed = false;
            }
        }

        if (!$hasAny) {
            $order->status = 'approved';
        } elseif ($completed) {
            $order->status = 'completed';
        } else {
            $order->status = 'partial';
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
        return SalesOrder::query()
            ->where('type', 'sell')
            ->whereIn('status', ['approved', 'partial'])
            ->select('id', 'code')
            ->get();
    }
    // private function updateStockFromPO($warehouseId, $productId, $qty)
    // {
    //     $stock = \App\Models\WarehouseProductStock::firstOrCreate([
    //         'warehouse_id' => $warehouseId,
    //         'product_id' => $productId,

    //     ]);
    //     $stock->quantity += $qty;
    //     $stock->save();

    //     $product = Product::find($productId);

    //     if ($product) {
    //         $product->quantity += $qty;
    //         $product->save();
    //     }
    // }
    private function updateProductPriceFromPO(
        $productId,
        $companyPrice
    ) {
        $product = Product::findOrFail($productId);

        $product->update([
            'purchase_price' => $companyPrice,
        ]);
    }
    private function increaseStock($warehouseId, $productId, $qty)
    {
        $stock = WarehouseProductStock::firstOrCreate([
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
        ]);

        $stock->quantity += $qty;
        $stock->save();
    }

    private function decreaseStock($warehouseId, $productId, $qty)
    {
        $stock = WarehouseProductStock::firstOrCreate([
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
        ]);

        $stock->quantity = max(0, $stock->quantity - $qty);
        $stock->save();
    }
    public function approve($id)
    {
        $slip = WarehouseSlip::with(['items'])->findOrFail($id);

        if ($slip->status !== 'pending') {
            return response()->json(['message' => 'Phiếu đã xử lý'], 422);
        }

        DB::transaction(function () use ($slip) {

            foreach ($slip->items as $item) {

                if ($slip->type === 'import') {
                    $stock = WarehouseProductStock::firstOrCreate(
                        [
                            'warehouse_id' => $slip->warehouse_id,
                            'product_id' => $item->product_id,
                        ],
                        [
                            'quantity' => 0,
                            'stock_value' => 0,
                        ]
                    );
                    $stock->refresh();

                    $companyPrice = $item->company_price ?? 0;

                    $stock->quantity += $item->quantity;
                    $stock->stock_value += $item->quantity * $companyPrice;

                    $this->updateProductPriceFromPO(
                        $item->product_id,
                        $companyPrice
                    );
                    $stock->save();
                }

                if ($slip->type === 'export') {
                    $stock = WarehouseProductStock::firstOrCreate([
                        'warehouse_id' => $slip->warehouse_id,
                        'product_id' => $item->product_id,
                    ]);
                    $product = Product::find($item->product_id);
                    if ($product) {

                        $saleOrder = SalesOrder::with('items')
                            ->find($slip->sales_order_id);

                        $saleItem = $saleOrder->items
                            ->firstWhere('product_id', $item->product_id);

                        if ($saleItem) {
                            $vatPercent = $saleItem->vat_percent ?? 0;

                            $sellPrice =
                                $saleItem->company_unit_price +
                                ($saleItem->company_unit_price * $vatPercent / 100);

                            $product->update([
                                'sell_price' => $sellPrice
                            ]);
                            $product->save();
                        }
                    }
                    if ($stock->quantity < $item->quantity) {
                        throw new \Exception('Không đủ tồn kho');
                    }

                    $avgCost = $stock->quantity > 0
                        ? $stock->stock_value / $stock->quantity
                        : 0;

                    $item->company_price = $avgCost;
                    $item->save();

                    $stock->quantity = max(
                        0,
                        $stock->quantity - $item->quantity
                    );

                    $stock->stock_value = max(
                        0,
                        $stock->stock_value -
                            ($item->quantity * $avgCost)
                    );

                    $stock->save();
                }
            }

            $slip->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            if ($slip->type === 'import') {
                $this->updateOrderStatus($slip->purchase_order_id);
            } else {
                $this->updateSalesOrderStatus($slip->sales_order_id);
            }

            // $this->updateStockFromPO(
            //     $slip->warehouse_id,
            //     $item->product_id,
            //     $item->quantity
            // );
        });

        return response()->json(['message' => 'Duyệt thành công']);
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
