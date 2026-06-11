<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseProductStock extends Model
{
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'quantity'
    ];
}
