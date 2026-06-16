<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'category_id',
        'unit_id',
        'type',
        'purchase_price',
        'sell_price',
        'quantity',
        'image',
        'description',
        'status',
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
        );
    }

    public $timestamps = false;
}
