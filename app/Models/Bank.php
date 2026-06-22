<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'short_name',
        'status',
        'logo'
    ];
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function isUsed(): bool
    {
        return $this->accounts()->exists();
    }
}
