<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',

        'currency_id',

        'province_code',
        'province_name',

        'ward_code',
        'ward_name',

        'address_detail',

        'total_debts',
        'total_advance',

        'status',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    protected $appends = ['full_address'];

    public function getFullAddressAttribute()
    {
        return collect([
            $this->address_detail,
            $this->ward_name,
            $this->province_name,
        ])
            ->filter()
            ->implode(', ');
    }
}
