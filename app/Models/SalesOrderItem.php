<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrderItem extends Model
{
    protected $fillable = [
        'sales_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'vat_percent',
        'amount',
    ];

    public function order()
    {
        return $this->belongsTo(
            SalesOrder::class,
            'sales_order_id'
        );
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
