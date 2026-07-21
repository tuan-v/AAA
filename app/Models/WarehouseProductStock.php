<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseProductStock extends Model
{
    protected $fillable = [
        'company_id',
        'warehouse_id',
        'product_id',
        'quantity',
        'stock_value',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
