<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'address_id',
        'total_inventory_value',
        'status',
    ];

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
    public function stocks()
    {
        return $this->hasMany(WarehouseProductStock::class);
    }
}
