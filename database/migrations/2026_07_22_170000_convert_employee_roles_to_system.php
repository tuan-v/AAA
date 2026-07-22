<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) return;

        $roles = [
            'Nhân viên nhân sự' => 'Vai trò nhân viên nhân sự hệ thống',
            'Nhân viên mua hàng' => 'Vai trò nhân viên mua hàng hệ thống',
            'Nhân viên kho' => 'Vai trò nhân viên kho hệ thống',
            'Nhân viên bán hàng' => 'Vai trò nhân viên bán hàng hệ thống',
            'Nhân viên kế toán' => 'Vai trò nhân viên kế toán hệ thống',
        ];

        foreach ($roles as $name => $description) {
            DB::table('roles')->where('name', $name)->where('guard_name', 'web')->update([
                'company_id' => null,
                'type' => 'system',
                'hierarchy_level' => 20,
                'is_protected' => false,
                'description' => $description,
                'updated_at' => now(),
            ]);
        }

        $genericRoleId = DB::table('roles')->where('name', 'Nhân viên')->where('guard_name', 'web')->value('id');
        if ($genericRoleId && ! DB::table('model_has_roles')->where('role_id', $genericRoleId)->exists()) {
            DB::table('role_has_permissions')->where('role_id', $genericRoleId)->delete();
            DB::table('roles')->where('id', $genericRoleId)->delete();
        }
    }

    public function down(): void
    {
        // Không chuyển ngược về một company cụ thể vì không thể xác định an toàn nguồn sở hữu cũ.
    }
};
