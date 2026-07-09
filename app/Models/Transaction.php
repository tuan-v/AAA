<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Account;
use App\Models\TransactionCategory;
use App\Models\Currency;
use App\Traits\BelongsToCompany;

class Transaction extends Model
{
    use BelongsToCompany;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'transaction_date',
        'type',
        'category_id',
        'currency_id',
        'amount',
        'exchange_rate',
        'amount_base',
        'from_account_id',
        'to_account_id',
        'reference_type',
        'reference_id',
        'customer_id',
        'supplier_id',
        'sales_order_id',
        'purchase_order_id',
        'description',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'amount_base' => 'decimal:2',
    ];

    // RELATIONS
    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function category()
    {
        return $this->belongsTo(TransactionCategory::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    // SCOPES
    public function scopeCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // HELPERS
    public function isReceipt()
    {
        return $this->type === 'receipt';
    }

    public function isPayment()
    {
        return $this->type === 'payment';
    }

    public function isTransfer()
    {
        return $this->type === 'transfer';
    }

    // ACCESSORS
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedBaseAmountAttribute()
    {
        return number_format($this->amount_base, 2);
    }
}
