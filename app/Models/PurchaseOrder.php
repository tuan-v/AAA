<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'code',
        'supplier_id',
        'currency_id',
        'expected_received_date',
        'note',
        'status'
    ];
    protected $appends = ['total_amount'];
    public function getTotalAmountAttribute()
    {
        return $this->items->sum('amount');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function items()
    {
        return $this->hasMany(
            PurchaseOrderItem::class
        );
    }
    public function order()
    {
        return $this->hasOne(Order::class, 'purchase_order_id');
    }
}
