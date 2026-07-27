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
            'name' => 'don_ban.tao_tu_lich_su',
            'guard_name' => 'web',
        ], [
            'description' => 'Tạo từ lịch sử đơn bán',
            'group' => 'Đơn Bán',
        ]);

        Role::query()
            ->whereIn('name', [
                'Supper Admin',
                'Giám đốc',
                'Quản lý bán hàng',
            ])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::query()
            ->where('name', 'don_ban.tao_tu_lich_su')
            ->where('guard_name', 'web')
            ->first();

        if ($permission) {
            Role::query()
                ->get()
                ->each(fn (Role $role) => $role->revokePermissionTo($permission));

            $permission->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
