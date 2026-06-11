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
        'note',
        'purchase_order_id'
    ];
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function slips()
    {
        return $this->hasMany(WarehouseSlip::class);
    }
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }
}
