<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseTransferItem extends Model
{
    protected $fillable = ['warehouse_transfer_id', 'product_id', 'quantity'];
    protected $casts = ['quantity' => 'decimal:3'];
    public function product() { return $this->belongsTo(Product::class); }
}
