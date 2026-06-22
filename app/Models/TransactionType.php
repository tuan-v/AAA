<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionType extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'direction',
        'description',
        'is_active'
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function isUsed(): bool
    {
        return $this->transactions()->exists();
    }
}
