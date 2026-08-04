<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    protected $fillable = ['customer_account_id', 'label', 'recipient_name', 'phone', 'province_id', 'ward_id',
        'province_name', 'ward_name', 'address_detail', 'address', 'is_default'];
    protected $casts = ['is_default' => 'boolean'];
    public function account() { return $this->belongsTo(CustomerAccount::class, 'customer_account_id'); }
}
