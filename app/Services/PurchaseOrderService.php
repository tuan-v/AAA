<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\WarehouseSlipItem;

class PurchaseOrderService
{
    public function updateStatus($orderId)
    {
        $order = PurchaseOrder::with('items')->findOrFail($orderId);

        $importedMap = WarehouseSlipItem::query()
            ->selectRaw('product_id, SUM(quantity) as total')
            ->whereHas('slip', function ($q) use ($orderId) {
                $q->where('purchase_order_id', $orderId)
                    ->where('type', 'import')
                    ->where('status', 'approved');
            })
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $hasImported = false;
        $completed = true;

        foreach ($order->items as $item) {

            $importedQty = $importedMap[$item->product_id] ?? 0;

            if ($importedQty > 0) {
                $hasImported = true;
            }

            if ($importedQty < $item->quantity) {
                $completed = false;
            }
        }

        $order->status = $completed
            ? 'completed'
            : ($hasImported ? 'partial' : 'approved');

        $order->save();
    }
}
