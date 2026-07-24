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

        $permissions = collect([
            'phong_ban.them' => ['Thêm phòng ban', 'Phòng Ban'],
            'chuc_vu.them' => ['Thêm chức vụ', 'Chức Vụ'],
        ])->map(fn($meta, $name) => Permission::updateOrCreate(
            ['name' => $name, 'guard_name' => 'web'],
            ['description' => $meta[0], 'group' => $meta[1]]
        ));

        Role::query()
            ->whereIn('name', ['Supper Admin', 'Giám đốc'])
            ->get()
            ->each(fn(Role $role) => $role->givePermissionTo($permissions));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::query()->whereIn('name', ['phong_ban.them', 'chuc_vu.them'])->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
