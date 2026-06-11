<?php

namespace App\Services;

use App\Models\WarehouseProductStock;

class StockService
{
    public function change($warehouseId, $productId, $qty, $type)
    {
        $stock = WarehouseProductStock::firstOrCreate([
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
        ]);

        if ($type === 'import') {
            $stock->quantity += $qty;
        }

        if ($type === 'export') {
            if ($stock->quantity < $qty) {
                throw new \Exception("Không đủ tồn kho");
            }

            $stock->quantity -= $qty;
        }

        $stock->save();

        return $stock;
    }
}
