<?php

namespace App\Models;

use App\Services\CodeGeneratorService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    // RELATION
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'category_id');
    }

    // SCOPES
    public function scopeCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    // HELPERS
    public function isIncome()
    {
        return $this->type === 'income';
    }

    public function isExpense()
    {
        return $this->type === 'expense';
    }

    public function isTransfer()
    {
        return $this->type === 'transfer';
    }

    // ACCESSOR
    public function getFormattedTypeAttribute()
    {
        return match ($this->type) {
            'income' => 'Thu nhập',
            'expense' => 'Chi phí',
            'transfer' => 'Chuyển khoản',
            default => $this->type,
        };
    }
    protected static function booted()
    {
        static::creating(function ($model) {

            if (!$model->code) {
                $model->code = app(CodeGeneratorService::class)
                    ->generate(self::class, 'LGD');
            }
        });
    }
}
