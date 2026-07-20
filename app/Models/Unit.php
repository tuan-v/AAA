<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'symbol',
        'allow_decimal',
        'status',
    ];

    protected $casts = ['allow_decimal' => 'boolean'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
