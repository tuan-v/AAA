<?php

namespace App\Services;

use App\Models\WarehouseProductStock;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function applySlip($slip)
    {
        DB::transaction(function () use ($slip) {

            foreach ($slip->items as $item) {

                // 1. tạo ledger
                WarehouseStockMovement::create([
                    'warehouse_id' => $slip->warehouse_id,
                    'product_id' => $item->product_id,
                    'type' => $slip->type === 'import' ? 'import' : 'export',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price,
                    'slip_id' => $slip->id,
                    'slip_item_id' => $item->id,
                ]);

                // 2. update stock
                $stock = WarehouseProductStock::firstOrCreate([
                    'warehouse_id' => $slip->warehouse_id,
                    'product_id' => $item->product_id,
                ]);

                if ($slip->type === 'import') {
                    $stock->quantity += $item->quantity;
                } else {
                    if ($stock->quantity < $item->quantity) {
                        throw new \Exception("Không đủ tồn kho");
                    }
                    $stock->quantity -= $item->quantity;
                }

                $stock->save();
            }
        });
    }
}
