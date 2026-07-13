<?php

namespace App\Services;

use App\Models\WarehouseProductStock;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function applySlip($slip)
    {
        foreach ($slip->items as $item) {

            WarehouseProductStock::updateOrCreate(
                [
                    'company_id' => $slip->company_id,
                    'warehouse_id' => $slip->warehouse_id,
                    'product_id' => $item->product_id,
                ],
                [
                    'quantity' => DB::raw("quantity + {$item->quantity}")
                ]
            );
        }

        if ($slip->purchase_order_id) {
            app(PurchaseOrderService::class)
                ->updateStatus($slip->purchase_order_id);
        }
    }
}
