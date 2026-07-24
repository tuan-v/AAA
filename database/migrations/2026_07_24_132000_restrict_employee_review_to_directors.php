<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', ['Supper Admin', 'Giám đốc'])
            ->get()
            ->each(fn (Role $role) => $role->revokePermissionTo([
                'nhan_su.duyet',
                'nhan_su.tu_choi',
            ]));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Không tự cấp lại quyền phê duyệt nhạy cảm cho vai trò quản lý.
    }
};
