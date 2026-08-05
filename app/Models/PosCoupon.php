<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosCoupon extends Model
{
    protected $fillable = [
        'company_id', 'code', 'name', 'description', 'type', 'value', 'minimum_order_amount',
        'maximum_discount', 'starts_at', 'ends_at', 'is_active', 'status', 'channels',
        'scope', 'usage_limit', 'usage_limit_per_customer', 'used_count', 'created_by',
    ];

    protected $casts = [
        'value' => 'decimal:2', 'minimum_order_amount' => 'decimal:2',
        'maximum_discount' => 'decimal:2', 'starts_at' => 'datetime',
        'ends_at' => 'datetime', 'is_active' => 'boolean', 'channels' => 'array',
        'usage_limit' => 'integer', 'usage_limit_per_customer' => 'integer', 'used_count' => 'integer',
    ];

    public function usages()
    {
        return $this->hasMany(CouponUsage::class, 'coupon_id');
    }

    public function assignments()
    {
        return $this->hasMany(CouponCustomerAssignment::class, 'coupon_id');
    }

    public function assignedCustomers()
    {
        return $this->belongsToMany(Customer::class, 'coupon_customer_assignments', 'coupon_id', 'customer_id')
            ->withPivot(['status', 'redeemed_at'])->withTimestamps();
    }

    public function supportsChannel(string $channel): bool
    {
        return empty($this->channels) || in_array($channel, $this->channels, true);
    }

    public function discountFor(float $subtotal): float
    {
        if (! $this->is_active || $this->status !== 'active'
            || ($this->usage_limit !== null && $this->used_count >= $this->usage_limit)
            || $subtotal < (float) $this->minimum_order_amount
            || ($this->starts_at && $this->starts_at->isFuture())
            || ($this->ends_at && $this->ends_at->isPast())) {
            return 0;
        }

        $discount = $this->type === 'percent'
            ? $subtotal * (float) $this->value / 100
            : (float) $this->value;
        if ($this->maximum_discount !== null) {
            $discount = min($discount, (float) $this->maximum_discount);
        }

        return round(min($subtotal, $discount), 2);
    }
}
