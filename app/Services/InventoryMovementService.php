<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\WarehouseProductStock;
use Illuminate\Database\Eloquent\Model;

class InventoryMovementService
{
    public function record(WarehouseProductStock $stock, string $type, float $quantity, float $unitCost, float $quantityBefore, float $valueBefore, Model $reference): InventoryMovement
    {
        $totalValue = round($quantity * $unitCost, 2);

        return InventoryMovement::create([
            'company_id' => $stock->company_id,
            'warehouse_id' => $stock->warehouse_id,
            'product_id' => $stock->product_id,
            'type' => $type,
            'quantity' => round($quantity, 3),
            'unit_cost' => round($unitCost, 6),
            'total_value' => $totalValue,
            'quantity_before' => round($quantityBefore, 3),
            'quantity_after' => round((float) $stock->quantity, 3),
            'value_before' => round($valueBefore, 2),
            'value_after' => round((float) $stock->stock_value, 2),
            'reference_type' => $reference::class,
            'reference_id' => $reference->getKey(),
            'created_by' => auth()->id(),
        ]);
    }
}
