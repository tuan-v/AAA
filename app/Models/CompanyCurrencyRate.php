<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CompanyCurrencyRate extends Model
{
    protected $appends = ['rate'];
    protected $fillable = ['company_id', 'currency_id', 'rate_to_base', 'effective_date', 'created_by'];
    protected $casts = ['rate_to_base' => 'decimal:8', 'effective_date' => 'date'];
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function getRateAttribute(): float { return (float) $this->rate_to_base; }
}
