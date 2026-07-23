<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::query()
            ->where('name', 'san_pham_mua_hang.them')
            ->where('guard_name', 'web')
            ->first();

        $role = Role::query()
            ->where('name', 'Nhân viên mua hàng')
            ->where('guard_name', 'web')
            ->first();

        if ($permission && $role) {
            $role->givePermissionTo($permission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::query()
            ->where('name', 'san_pham_mua_hang.them')
            ->where('guard_name', 'web')
            ->first();

        $role = Role::query()
            ->where('name', 'Nhân viên mua hàng')
            ->where('guard_name', 'web')
            ->first();

        if ($permission && $role) {
            $role->revokePermissionTo($permission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
