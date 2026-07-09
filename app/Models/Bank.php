<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class Bank extends Model
{
    use BelongsToCompany;
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
