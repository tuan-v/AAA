<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'company_id', 'warehouse_id', 'product_id', 'type', 'quantity',
        'unit_cost', 'total_value', 'quantity_before', 'quantity_after',
        'value_before', 'value_after', 'reference_type', 'reference_id', 'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:3', 'unit_cost' => 'decimal:6', 'total_value' => 'decimal:2',
        'quantity_before' => 'decimal:3', 'quantity_after' => 'decimal:3',
        'value_before' => 'decimal:2', 'value_after' => 'decimal:2',
    ];

    public function reference() { return $this->morphTo(); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
