<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    protected $fillable = [
        'currency_id',
        'rate',
        'effective_date',
        'created_by',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'effective_date' => 'date',
    ];

    /**
     * Tiền tệ
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Người tạo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
