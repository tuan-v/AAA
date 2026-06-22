<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'account_id',
        'transaction_id',
        'ledger_date',
        'debit',
        'credit',
        'balance_after',
        'description',
    ];

    protected $casts = [
        'ledger_date' => 'datetime',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
