<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponCustomerAssignment extends Model
{
    protected $fillable = ['company_id', 'coupon_id', 'customer_id', 'status', 'created_by', 'redeemed_at'];

    protected $casts = ['redeemed_at' => 'datetime'];

    public function coupon()
    {
        return $this->belongsTo(PosCoupon::class, 'coupon_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
