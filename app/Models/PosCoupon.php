<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosCoupon extends Model
{
    protected $fillable = [
        'company_id', 'code', 'name', 'type', 'value', 'minimum_order_amount',
        'maximum_discount', 'starts_at', 'ends_at', 'is_active', 'usage_limit', 'used_count',
    ];

    protected $casts = [
        'value' => 'decimal:2', 'minimum_order_amount' => 'decimal:2',
        'maximum_discount' => 'decimal:2', 'starts_at' => 'datetime',
        'ends_at' => 'datetime', 'is_active' => 'boolean',
        'usage_limit' => 'integer', 'used_count' => 'integer',
    ];

    public function discountFor(float $subtotal): float
    {
        if (! $this->is_active || ($this->usage_limit !== null && $this->used_count >= $this->usage_limit)
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
