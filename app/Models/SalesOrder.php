<?php

namespace App\Models;

use App\Services\CodeGeneratorService;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use BelongsToCompany;

    protected $appends = ['effective_status'];

    protected $fillable = [
        'code',
        'company_id',
        'customer_id',
        'customer_account_id',
        'recipient_name',
        'recipient_phone',
        'recipient_email',
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
        'submitted_at',
        'shipping_started_at',
        'exchange_rate',
        'sales_channel',
        'pos_warehouse_id',
        'payment_method',
        'payment_status',
        'cod_status',
        'cod_amount',
        'cod_collected_at',
        'cod_reconciled_at',
        'shipping_partner_id',
        'shipping_method',
        'shipping_fee',
        'carrier_shipping_fee',
        'carrier_service_fee',
        'carrier_insurance_fee',
        'tracking_code',
        'shipping_note',
        'cancellation_reason',
        'payment_currency_id',
        'payment_exchange_rate',
        'payment_tendered_amount',
        'invoice_type',
        'paid_amount',
        'pos_coupon_id',
        'coupon_code_snapshot',
        'coupon_name_snapshot',
        'coupon_type_snapshot',
        'coupon_value_snapshot',
        'discount_amount',
        'tendered_amount',
        'change_amount',
        'payment_reference',
        'completed_at',
        'return_status',
        'returned_at',
        'source_order_id',
    ];

    protected $casts = [
        'expected_delivery_date' => 'date:Y-m-d',
        'paid_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tendered_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'payment_exchange_rate' => 'decimal:8',
        'payment_tendered_amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'cod_amount' => 'decimal:2',
        'cod_collected_at' => 'datetime',
        'cod_reconciled_at' => 'datetime',
        'approved_at' => 'datetime',
        'submitted_at' => 'datetime',
        'shipping_started_at' => 'datetime',
        'returned_at' => 'datetime',
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

    public function customerAccount()
    {
        return $this->belongsTo(CustomerAccount::class);
    }

    public function shippingPartner()
    {
        return $this->belongsTo(ShippingPartner::class);
    }

    public function codReconciliationItem()
    {
        return $this->hasOne(CodReconciliationItem::class);
    }

    public function posCoupon()
    {
        return $this->belongsTo(PosCoupon::class, 'pos_coupon_id');
    }

    public function couponUsage()
    {
        return $this->hasOne(CouponUsage::class);
    }

    public function paymentCurrency()
    {
        return $this->belongsTo(Currency::class, 'payment_currency_id');
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
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getEffectiveStatusAttribute(): string
    {
        return match ($this->return_status) {
            'pending_warehouse' => 'return_pending_warehouse',
            'pending_accountant' => 'return_pending_accountant',
            'returned' => 'returned',
            default => (string) $this->status,
        };
    }

    protected static function booted()
    {
        static::creating(function ($model) {

            if (! $model->code) {
                $model->code = app(CodeGeneratorService::class)
                    ->generate(self::class, 'SO');
            }
        });
    }
}
