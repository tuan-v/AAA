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
        $permission = Permission::updateOrCreate(
            ['name' => 'nhan_su.tu_choi', 'guard_name' => 'web'],
            ['description' => 'Từ chối nhân sự mới', 'group' => 'Nhân Sự']
        );
        Role::query()->whereIn('name', ['Supper Admin', 'Giám đốc'])->get()
            ->each(fn(Role $role) => $role->givePermissionTo($permission));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::query()->where('name', 'nhan_su.tu_choi')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
