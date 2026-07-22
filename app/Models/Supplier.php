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
        'opening_debt',
        'opening_debt_exchange_rate',
        'opening_debt_base',
        'total_debts',
        'total_advance',
        'opening_advance',
        'opening_advance_exchange_rate',
        'opening_advance_base',
        'note',

        'status',
    ];

    protected $casts = [
        'opening_debt' => 'decimal:2',
        'opening_debt_exchange_rate' => 'decimal:8',
        'opening_debt_base' => 'decimal:2',
        'opening_advance' => 'decimal:2',
        'opening_advance_exchange_rate' => 'decimal:8',
        'opening_advance_base' => 'decimal:2',
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

            if (! array_key_exists('opening_debt_base', $model->getAttributes())) {
                $model->opening_debt_exchange_rate = 1;
                $model->opening_debt_base = $model->opening_debt ?? 0;
            }
            if (! array_key_exists('opening_advance_base', $model->getAttributes())) {
                $model->opening_advance_exchange_rate = 1;
                $model->opening_advance_base = $model->opening_advance ?? 0;
            }

            if (!$model->code) {
                $model->code = app(CodeGeneratorService::class)
                    ->generate(self::class, 'NCC');
            }
        });
    }
}
