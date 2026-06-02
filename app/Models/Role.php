<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Permission;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = [
        'name',
        'guard_name',
        'description',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'model_has_roles', 'role_id', 'model_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id');
    }
}
