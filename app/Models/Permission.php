<?php

use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
// use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends Model
{
    protected $table = 'permissions';
    protected $fillable = [
        'name',
        'guard_name',
        'group',
    ];
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
