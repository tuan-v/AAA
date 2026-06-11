<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseSlip extends Model
{
    protected $fillable = [
        'code',
        'order_id',
        'warehouse_id',
        'type',
        'note',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(
            WarehouseSlipItem::class,
            'slip_id'
        );
    }
}
