<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'code',
        'type',
        'status',
        'warehouse_id',
        'note'
    ];
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
