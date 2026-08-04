<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Company extends Model
{
    protected $fillable = [
        'name',
        'storefront_slug',
        'storefront_enabled',
        'address',
        'email',
        'phone',
        'tax_code',
        'logo',
        'owner_id',
    ];

    protected $casts = ['storefront_enabled' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (Company $company) {
            if ($company->storefront_slug) {
                return;
            }
            $base = Str::slug($company->name) ?: 'cua-hang';
            $slug = $base;
            $suffix = 2;
            while (static::where('storefront_slug', $slug)->exists()) {
                $slug = $base.'-'.$suffix++;
            }
            $company->storefront_slug = $slug;
        });
    }

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
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }
    public function transactionCategories()
    {
        return $this->hasMany(TransactionCategory::class);
    }
}
