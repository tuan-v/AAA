<?php

namespace App\Models;

use App\Services\CodeGeneratorService;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use BelongsToCompany;
    protected $table = 'products';

    protected $fillable = [
        'company_id',
        'name',
        'sku',
        'barcode',
        'category_id',
        'unit_id',
        'type',
        'purchase_price',
        'sell_price',
        'promotional_price',
        'promotion_starts_at',
        'promotion_ends_at',
        'quantity',
        'image',
        'description',
        'status',
        'storefront_visible',
    ];
    protected $casts = [
        'storefront_visible' => 'boolean',
        'promotion_starts_at' => 'datetime',
        'promotion_ends_at' => 'datetime',
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function stocks()
    {
        return $this->hasMany(
            WarehouseProductStock::class,
            'product_id'
        )->with('warehouse');
    }
    public function salesOrderItems()
    {
        return $this->hasMany(SalesOrderItem::class);
    }
    protected $appends = ['stock_quantity'];

    public function getStockQuantityAttribute()
    {
        return $this->stocks()->sum('quantity');
    }
    public $timestamps = false;
    // protected static function booted()
    // {
    //     static::creating(function ($model) {

    //         if (!$model->code) {

    //             $model->code = app(CodeGeneratorService::class)
    //                 ->generate(self::class, 'SP');
    //         }
    //     });
    // }
}
