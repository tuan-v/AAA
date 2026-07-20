<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseSlipItem extends Model
{
    protected $fillable = [
        'slip_id',
        'product_id',
        'quantity',
        'price',
        'company_price',
        'cost_price',
        'cost_amount',
        'vat_percent',
    ];

    public function slip()
    {
        return $this->belongsTo(
            WarehouseSlip::class,
            'slip_id'
        );
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
