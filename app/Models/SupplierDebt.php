<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierDebt extends Model
{
    protected $fillable = [
        'supplier_id',
        'type',
        'amount',
        'reference_type',
        'reference_id',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Nhà cung cấp.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
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
        return $query->where('type', 'invoice');
    }

    /**
     * Scope chỉ lấy thanh toán.
     */
    public function scopePayment($query)
    {
        return $query->where('type', 'payment');
    }

    /**
     * Scope điều chỉnh công nợ.
     */
    public function scopeAdjustment($query)
    {
        return $query->where('type', 'adjustment');
    }
}