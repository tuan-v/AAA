<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class WarehouseTransfer extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'code', 'from_warehouse_id', 'to_warehouse_id', 'status', 'note', 'created_by', 'approved_by', 'approved_at'];
    protected $casts = ['approved_at' => 'datetime'];

    public function items() { return $this->hasMany(WarehouseTransferItem::class); }
    public function fromWarehouse() { return $this->belongsTo(Warehouse::class, 'from_warehouse_id'); }
    public function toWarehouse() { return $this->belongsTo(Warehouse::class, 'to_warehouse_id'); }
}
