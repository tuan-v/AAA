<?php

namespace App\Models;

use App\Services\CodeGeneratorService;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use BelongsToCompany;
    protected $fillable = [
        'company_id',
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

    public function debts()
    {
        return $this->hasMany(SupplierDebt::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
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
    protected static function booted()
    {
        static::creating(function ($model) {

            if (!$model->code) {
                $model->code = app(CodeGeneratorService::class)
                    ->generate(self::class, 'NCC');
            }
        });
    }
}
