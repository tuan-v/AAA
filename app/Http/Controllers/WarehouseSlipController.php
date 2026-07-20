<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\WarehouseSlip;
use App\Models\WarehouseSlipItem;
use App\Models\WarehouseProductStock;
use App\Services\ActivityLogService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\SupplierDebtService;
use App\Services\CustomerDebtService;

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
        $slips = $query->latest()->paginate(min((int) $request->input('per_page', 10), 100));

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
        $slip = WarehouseSlip::with([
            'warehouse',
            'items.product.unit',
            'saleOrder.customer',
            'saleOrder.currency',
            'saleOrder.items',
            'purchaseOrder.supplier',
            'purchaseOrder.currency',
            'purchaseOrder.items',
            'createdBy',
            'approvedBy',
            'logs.user'
        ])->findOrFail($id);

        $company = auth()->user()->company ?? auth()->user()->companies()->first();
        $companyCurrency = $company ? $company->default_currency : null;

        // Quy đổi giá trị item của phiếu kho
        foreach ($slip->items as $item) {
            $item->price = $item->company_price;
        }

        // Quy đổi đơn mua hàng đi kèm
        if ($slip->purchaseOrder) {
            $order = $slip->purchaseOrder;
            foreach ($order->items as $item) {
                $item->price = $item->company_price;
                $item->amount = $item->quantity * $item->company_price;
            }
            $order->total_amount = round($order->items->sum('amount'), 2);
            $order->setRelation('currency', $companyCurrency ?: $order->currency);
        }

        // Quy đổi đơn bán hàng đi kèm
        if ($slip->saleOrder) {
            $order = $slip->saleOrder;
            foreach ($order->items as $item) {
                $item->unit_price = $item->company_unit_price;
                $item->amount = $item->company_amount;
            }
            $order->subtotal = round($order->items->sum('amount'), 2);
            $order->vat_amount = round(($order->vat_amount ?? 0) * ($order->exchange_rate ?? 1), 2);
            $order->total_amount = round($order->subtotal + $order->vat_amount, 2);
            $order->setRelation('currency', $companyCurrency ?: $order->currency);
        }

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
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        app(\App\Services\OrderQuantityValidationService::class)->validate($validated['items']);

        DB::beginTransaction();

        try {
            $slip = null;
            $order = null;

            if ($validated['type'] === 'import') {
                $order = PurchaseOrder::with('items')->findOrFail($validated['purchase_order_id']);

                if (! in_array($order->status, ['approved', 'partial'], true)) {
                    throw new \RuntimeException('Chỉ được tạo phiếu nhập từ đơn mua đã duyệt và chưa nhập đủ.');
                }


                $slip = WarehouseSlip::create([
                    'type' => 'import',
                    'purchase_order_id' => $order->id,
                    'warehouse_id' => $validated['warehouse_id'],
                    'note' => $validated['note'],
                    'status' => 'pending',
                    'created_by' => auth()->id(),
                ]);
            } else { // EXPORT
                $order = SalesOrder::with('items')->findOrFail($validated['sales_order_id']);

                if (! in_array($order->status, ['approved', 'partial'], true)) {
                    throw new \RuntimeException('Chỉ được tạo phiếu xuất từ đơn bán đã duyệt và chưa xuất đủ.');
                }

                $companyPrice = 0;
                $slip = WarehouseSlip::create([
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
                $qty = (float) $itemData['quantity'];
                if ($qty <= 0) continue;
                if (!$orderItem) {
                    throw new \Exception(
                        'Sản phẩm không tồn tại trong đơn hàng'
                    );
                }


                $reservedQuantity = WarehouseSlipItem::query()
                    ->where('product_id', $itemData['product_id'])
                    ->whereHas('slip', function ($query) use ($validated, $order) {
                        $query->whereIn('status', ['pending', 'approved']);
                        if ($validated['type'] === 'import') {
                            $query->where('purchase_order_id', $order->id)->where('type', 'import');
                        } else {
                            $query->where('sales_order_id', $order->id)->where('type', 'export');
                        }
                    })
                    ->sum('quantity');

                if ($reservedQuantity + $qty > (float) $orderItem->quantity) {
                    throw new \RuntimeException('Số lượng trên phiếu vượt quá số lượng còn lại của đơn hàng.');
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
                    'vat_percent' => (float) ($orderItem->vat_percent ?? 0),
                ]);
            }

            DB::commit();
            ActivityLogService::log(
                $slip,
                'create',
                'Tạo phiếu kho',
                null,
                [
                    'type' => $slip->type,
                    'warehouse_id' => $slip->warehouse_id,
                    'items' => $slip->items->map(function ($i) {
                        return [
                            'product_id' => $i->product_id,
                            'quantity' => $i->quantity,
                            'price' => $i->price,
                        ];
                    }),
                ]
            );
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

        if ($slip->status !== 'pending') {
            return response()->json(['message' => 'Chỉ được sửa phiếu đang chờ duyệt.'], 422);
        }

        $validated = $request->validate(['note' => 'nullable|string|max:2000']);

        $slip->update([
            'note' => $validated['note'] ?? null
        ]);

        return response()->json([
            'message' => 'Cập nhật thành công'
        ]);
    }

    // =========================
    // CODE GENERATOR (SAFE)
    // =========================
    private function updateOrderStatus($orderId)
    {
        $order = PurchaseOrder::with('items')->findOrFail($orderId);

        // $importedMap = WarehouseSlipItem::query()
        //     ->selectRaw('product_id, SUM(quantity) as total')
        //     ->whereIn('slip_id', function ($q) use ($orderId) {
        //         $q->select('id')
        //             ->from('warehouse_slips')
        //             ->where('purchase_order_id', $orderId)
        //             ->where('type', 'import')
        //             ->where('status', ['approved']);
        //     })
        //     ->groupBy('product_id')
        //     ->pluck('total', 'product_id');
        $importedMap = $this->getReceivedMap($orderId);
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
    private function updateProductPriceFromPO(
        $productId,
        $companyPrice
    ) {
        $product = Product::findOrFail($productId);

        $product->update([
            'purchase_price' => $companyPrice,
        ]);
    }
    public function approve(
        $id,
        SupplierDebtService $supplierDebtService,
        CustomerDebtService $customerDebtService
    ) {

        DB::transaction(function () use (
            $id,
            $supplierDebtService,
            $customerDebtService
        ) {
            $slip = WarehouseSlip::with(['items', 'warehouse'])
                ->lockForUpdate()
                ->findOrFail($id);

            if ($slip->status !== 'pending') {
                throw new \RuntimeException('Phiếu đã được xử lý.');
            }

            foreach ($slip->items as $item) {

                if ($slip->type === 'import') {
                    $stock = WarehouseProductStock::firstOrCreate(
                        [
                            'company_id' => $slip->company_id,
                            'warehouse_id' => $slip->warehouse_id,
                            'product_id' => $item->product_id,
                        ],
                        [
                            'quantity' => 0,
                            'stock_value' => 0,
                        ]
                    );
                    $stock = WarehouseProductStock::whereKey($stock->id)->lockForUpdate()->firstOrFail();

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
                        'company_id' => $slip->company_id,
                        'warehouse_id' => $slip->warehouse_id,
                        'product_id' => $item->product_id,
                    ]);
                    $stock = WarehouseProductStock::whereKey($stock->id)->lockForUpdate()->firstOrFail();
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
            if ($slip->type === 'import') {
                $supplierDebtService->createFromWarehouseSlip($slip);
            } else {
                $customerDebtService->createFromWarehouseSlip($slip);
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
        $old = ['status' => $slip->status];
        $slip->status = 'rejected';
        $slip->approved_by = auth()->id();
        $slip->approved_at = now();
        $slip->save();

        ActivityLogService::log(
            $slip,
            'reject',
            'Từ chối phiếu kho',
            $old,
            ['status' => 'rejected']
        );

        return response()->json([
            'message' => 'Từ chối phiếu thành công'
        ]);
    }
    private function getReceivedMap($purchaseOrderId)
    {
        return WarehouseSlipItem::query()
            ->selectRaw('product_id, SUM(quantity) as total')
            ->whereIn('slip_id', function ($q) use ($purchaseOrderId) {
                $q->select('id')
                    ->from('warehouse_slips')
                    ->where('purchase_order_id', $purchaseOrderId)
                    ->where('type', 'import')
                    ->whereIn('status', ['pending', 'approved']);
            })
            ->groupBy('product_id')
            ->pluck('total', 'product_id');
    }
}
