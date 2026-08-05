<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    protected $fillable = ['company_id', 'coupon_id', 'sales_order_id', 'customer_id', 'channel', 'discount_amount', 'status', 'redeemed_at', 'reversed_at'];

    protected $casts = ['discount_amount' => 'decimal:2', 'redeemed_at' => 'datetime', 'reversed_at' => 'datetime'];

    public function coupon()
    {
        return $this->belongsTo(PosCoupon::class, 'coupon_id');
    }

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
