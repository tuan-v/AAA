<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use App\Models\Account;
use App\Models\Transaction;

class Currency extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'is_active'
    ];
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'companies_has_currencies', 'currency_id', 'company_id');
    }
    public function isUsed(): bool
    {
        return
            Customer::where('currency_id', $this->id)->exists()
            || Supplier::where('currency_id', $this->id)->exists()
            || SalesOrder::where('currency_id', $this->id)->exists()
            || PurchaseOrder::where('currency_id', $this->id)->exists();
        // || Account::where('currency_id', $this->id)->exists()
        // || Transaction::where('currency_id', $this->id)->exists();
    }
}
