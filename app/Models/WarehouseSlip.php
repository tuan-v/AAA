<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\BelongsToCompany;

class WarehouseSlip extends Model
{
    use BelongsToCompany;
    protected $fillable = [
        'company_id',
        'code',
        'warehouse_id',
        'purchase_order_id',
        'sales_order_id',
        'type',
        'note',
        'created_by',
        'approved_by',
        'approved_at',
        'status',
    ];
    protected $casts = [
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(
            WarehouseSlipItem::class,
            'slip_id'
        );
    }
    public function purchaseOrder()
    {
        return $this->belongsTo(
            PurchaseOrder::class,
            'purchase_order_id'
        );
    }
    public function saleOrder()
    {
        return $this->belongsTo(
            SalesOrder::class,
            'sales_order_id'
        );
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function logs()
    {
        return $this->morphMany(ActivityLog::class, 'model');
    }
}
