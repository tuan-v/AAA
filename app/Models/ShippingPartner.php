<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ShippingPartner extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'code', 'name', 'phone', 'email', 'tracking_url_template', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function orders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function reconciliations()
    {
        return $this->hasMany(CodReconciliation::class);
    }
}
