<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $fillable = [
        'code',
        'company_id',
        'customer_id',
        'currency_id',
        'province_id',
        'ward_id',
        'address_detail',
        'expected_delivery_date',
        'note',
        'subtotal',
        'vat_amount',
        'total_amount',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'exchange_rate',
    ];
    protected $casts = [
        'expected_delivery_date' => 'datetime',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }
    public function warehouseSlips()
    {
        return $this->hasMany(WarehouseSlip::class, 'sales_order_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_vy');
    }
}
