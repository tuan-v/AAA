<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDebt extends Model
{
    protected $fillable = [
        'customer_id',
        'type',
        'amount',
        'reference_type',
        'reference_id',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // -------------------------------------------------------------------------
    // RELATIONS
    // -------------------------------------------------------------------------

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Quan hệ đa hình tới chứng từ phát sinh công nợ.
     * - SalesOrder::class → phát sinh khi đơn bán được duyệt
     * - Transaction::class → khi KH thanh toán / hoàn tiền
     */
    public function reference()
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }

    // -------------------------------------------------------------------------
    // SCOPES
    // -------------------------------------------------------------------------

    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /** Bút toán phát sinh công nợ (đơn bán) */
    public function scopeSale($query)
    {
        return $query->where('type', 'sale');
    }

    /** Bút toán KH đã thanh toán */
    public function scopePayment($query)
    {
        return $query->where('type', 'payment');
    }

    /** Bút toán hoàn tiền cho KH */
    public function scopeRefund($query)
    {
        return $query->where('type', 'refund');
    }
}
