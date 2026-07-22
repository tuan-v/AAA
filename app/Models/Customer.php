<?php

namespace App\Models;

use App\Services\CodeGeneratorService;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use BelongsToCompany; // <-- 2. Kích hoạt sử dụng Trait ở đây

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'email',
        'phone',
        'currency_id',
        'opening_debt',
        'opening_debt_exchange_rate',
        'opening_debt_base',

        'province_id',
        'ward_id',
        'address_detail',
        'status',
    ];

    protected $casts = [
        'opening_debt' => 'decimal:2',
        'opening_debt_exchange_rate' => 'decimal:8',
        'opening_debt_base' => 'decimal:2',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function currency()
    {
        return $this->belongsTo(
            Currency::class,
            'currency_id'
        );
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    public function debts()
    {
        return $this->hasMany(CustomerDebt::class);
    }

    public function orders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function payments()
    {
        return $this->hasMany(CustomerPayment::class);
    }
    protected static function booted()
    {
        static::creating(function ($model) {

            if (! array_key_exists('opening_debt_base', $model->getAttributes())) {
                $model->opening_debt_exchange_rate = 1;
                $model->opening_debt_base = $model->opening_debt ?? 0;
            }

            if (!$model->code) {
                $model->code = app(CodeGeneratorService::class)
                    ->generate(self::class, 'KH');
            }
        });
    }
}
