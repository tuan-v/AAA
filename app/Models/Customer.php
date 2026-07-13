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

        'province_id',
        'ward_id',
        'address_detail',
        'status',
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

            if (!$model->code) {
                $model->code = app(CodeGeneratorService::class)
                    ->generate(self::class, 'KH');
            }
        });
    }
}
