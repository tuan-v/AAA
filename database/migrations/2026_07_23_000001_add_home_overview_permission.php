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

        $permission = Permission::updateOrCreate([
            'name' => 'tong_quan.xem',
            'guard_name' => 'web',
        ], [
            'description' => 'Xem tổng quan trang chủ',
            'group' => 'Tổng Quan Trang Chủ',
        ]);

        Role::query()
            ->whereIn('name', ['Supper Admin', 'Giám đốc'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::query()
            ->where('name', 'tong_quan.xem')
            ->where('guard_name', 'web')
            ->first();

        if ($permission) {
            Role::query()
                ->whereIn('name', ['Supper Admin', 'Giám đốc'])
                ->get()
                ->each(fn (Role $role) => $role->revokePermissionTo($permission));

            $permission->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
