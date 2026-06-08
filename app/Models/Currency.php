<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'companies_has_currencies', 'currency_id', 'company_id');
    }
}
