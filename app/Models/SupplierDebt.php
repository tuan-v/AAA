<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierDebt extends Model
{
    public const TYPE_INVOICE = 'invoice';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_REFUND = 'refund';
    public const TYPE_ADVANCE = 'advance';
    public const TYPE_ADVANCE_REFUND = 'advance_refund';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_ADVANCE_OFFSET = 'advance_offset';
    protected $fillable = [
        'supplier_id',
        'type',
        'amount',
        'currency_id',
        'original_amount',
        'exchange_rate',
        'amount_base',
        'reference_type',
        'reference_id',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:8',
        'amount_base' => 'decimal:2',
    ];

    /**
     * Nhà cung cấp.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Quan hệ đa hình tới chứng từ phát sinh công nợ.
     *
     * Ví dụ:
     * - PurchaseOrder
     * - Transaction
     * - Invoice (sau này)
     */
    public function reference()
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }

    /**
     * Scope lấy theo nhà cung cấp.
     */
    public function scopeSupplier($query, int $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope chỉ lấy phát sinh công nợ.
     */
    public function scopeInvoice($query)
    {
        return $query->where('type', self::TYPE_INVOICE);
    }

    /**
     * Scope chỉ lấy thanh toán.
     */
    public function scopePayment($query)
    {
        return $query->where('type', self::TYPE_PAYMENT);
    }
    public function scopeAdvance($query)
    {
        return $query->where('type', self::TYPE_ADVANCE);
    }
    public function scopeRefund($query)
    {
        return $query->where('type', self::TYPE_REFUND);
    }
    public function scopeAdvanceRefund($query)
    {
        return $query->where('type', self::TYPE_ADVANCE_REFUND);
    }
    /**
     * Scope điều chỉnh công nợ.
     */
    public function scopeAdjustment($query)
    {
        return $query->where('type', self::TYPE_ADJUSTMENT);
    }
}
