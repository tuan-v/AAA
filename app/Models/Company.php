<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'address',
        'email',
        'phone',
        'tax_code',
        'logo',
        'owner_id',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users()
    {
        return $this->morphedByMany(
            User::class,
            'model',
            'model_has_company',
            'company_id',
            'model_id'
        )->withTimestamps();
    }
    public function currencies()
    {
        return $this->belongsToMany(Currency::class, 'companies_has_currencies', 'company_id', 'currency_id')->withPivot('is_default')
            ->withTimestamps();;
    }
    public function getDefaultCurrencyAttribute()
    {
        return $this->currencies()
            ->wherePivot('is_default', 1)
            ->first();
    }
    public function getLogoAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }
}
