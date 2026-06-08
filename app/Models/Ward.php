<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    protected $fillable = [
        'province_id',
        'code',
        'name',
    ];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }
}
