<?php

namespace App\Models;

use App\Services\CodeGeneratorService;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use BelongsToCompany;
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'address_detail',
        'total_inventory_value',
        'status',
        'province_code',
        'ward_code'
    ];

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
    public function stocks()
    {
        return $this->hasMany(WarehouseProductStock::class);
    }
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_code');
    }
    public function ward()
    {
        return $this->belongsTo(Ward::class, 'ward_code');
    }
    public function slips()
    {
        return $this->hasMany(WarehouseSlip::class);
    }
    protected static function booted()
    {
        static::creating(function ($model) {

            if (!$model->code) {
                $model->code = app(CodeGeneratorService::class)
                    ->generate(self::class, 'WH');
            }
        });
    }
}
