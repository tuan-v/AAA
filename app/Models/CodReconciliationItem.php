<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodReconciliationItem extends Model
{
    protected $fillable = ['cod_reconciliation_id', 'sales_order_id', 'tracking_code', 'cod_amount'];

    protected $casts = ['cod_amount' => 'decimal:2'];

    public function reconciliation()
    {
        return $this->belongsTo(CodReconciliation::class);
    }

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }
}
