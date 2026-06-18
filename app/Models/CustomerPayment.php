<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    protected $fillable = [
        'code',
        'customer_id',
        'amount',
        'payment_date',
        'note',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
