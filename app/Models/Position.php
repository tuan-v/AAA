<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['company_id', 'department_id', 'code', 'name', 'description', 'status'];

    public function company() { return $this->belongsTo(Company::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function users() { return $this->hasMany(User::class); }
}
