<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'company_id', 'code', 'name', 'description', 'status', 'manager_id',
    ];

    public function company() { return $this->belongsTo(Company::class); }
    public function manager() { return $this->belongsTo(User::class, 'manager_id'); }
    public function users() { return $this->hasMany(User::class); }
    public function positions() { return $this->hasMany(Position::class); }
}
