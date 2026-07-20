<?php

namespace App\Models;

use App\Services\CodeGeneratorService;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'company_id',
        'parent_id',
        'name',
        'code',
        'description',
        'status'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
    protected static function booted()
    {
        static::creating(function ($model) {

            if (!$model->code) {

                $model->code = app(CodeGeneratorService::class)
                    ->generate(self::class, 'DM');
            }
        });
    }
}
