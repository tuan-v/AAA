<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CustomerAccount extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['company_id', 'customer_id', 'email', 'password', 'is_active', 'last_login_at'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['password' => 'hashed', 'is_active' => 'boolean', 'last_login_at' => 'datetime'];

    public function company() { return $this->belongsTo(Company::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function addresses() { return $this->hasMany(CustomerAddress::class); }
    public function orders() { return $this->hasMany(SalesOrder::class); }
}
