<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class CodReconciliation extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'shipping_partner_id', 'code', 'reconciliation_date', 'cod_amount',
        'shipping_fee', 'service_fee', 'insurance_fee', 'adjustment_amount', 'received_amount',
        'account_id', 'transaction_id', 'payment_reference', 'status', 'note',
        'created_by', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'reconciliation_date' => 'date:Y-m-d', 'approved_at' => 'datetime',
        'cod_amount' => 'decimal:2', 'shipping_fee' => 'decimal:2', 'service_fee' => 'decimal:2',
        'insurance_fee' => 'decimal:2', 'adjustment_amount' => 'decimal:2', 'received_amount' => 'decimal:2',
    ];

    public function partner()
    {
        return $this->belongsTo(ShippingPartner::class, 'shipping_partner_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function items()
    {
        return $this->hasMany(CodReconciliationItem::class);
    }
}
