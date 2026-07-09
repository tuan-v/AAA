<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;


class Account extends Model
{
    use BelongsToCompany;
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'currency_id',
        'opening_balance',
        'current_balance',
        'bank_id',
        'bank_account_no',
        'is_active'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function isUsed(): bool
    {
        return Transaction::where('from_account_id', $this->id)->exists()
            || Transaction::where('to_account_id', $this->id)->exists();
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function transactionsFrom()
    {
        return $this->hasMany(Transaction::class, 'from_account_id');
    }

    public function transactionsTo()
    {
        return $this->hasMany(Transaction::class, 'to_account_id');
    }
    public function ledgers()
    {

        return $this->hasMany(AccountLedger::class)
            ->latest('ledger_date');
    }
    public function increaseBalance(float $amount): void
    {
        $this->increment('current_balance', $amount);
    }

    public function decreaseBalance(float $amount): void
    {
        $this->decrement('current_balance', $amount);
    }
}
