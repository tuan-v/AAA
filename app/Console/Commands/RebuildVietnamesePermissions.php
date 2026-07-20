<?php

namespace App\Console\Commands;

use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class RebuildVietnamesePermissions extends Command
{
    protected $signature = 'permissions:rebuild-vietnamese {--force : Xác nhận xóa và tạo lại toàn bộ quyền}';

    protected $description = 'Xóa quyền cũ, tạo lại quyền tiếng Việt và phân quyền lại theo vai trò';

    public function handle(PermissionRegistrar $registrar): int
    {
        if (! $this->option('force')) {
            $this->error('Hãy thêm --force để xác nhận thao tác tạo lại quyền.');

            return self::FAILURE;
        }

        $registrar->forgetCachedPermissions();

        DB::transaction(function () {
            DB::table('role_has_permissions')->delete();
            DB::table('model_has_permissions')->delete();
            DB::table('permissions')->delete();
        });

        $this->call('db:seed', ['--class' => RoleSeeder::class, '--force' => true]);
        $this->call('db:seed', ['--class' => PermissionSeeder::class, '--force' => true]);
        $this->call('db:seed', ['--class' => RolePermissionSeeder::class, '--force' => true]);

        $registrar->forgetCachedPermissions();
        $permissionCount = DB::table('permissions')->count();
        $assignmentCount = DB::table('role_has_permissions')->count();
        $this->info("Đã tạo lại {$permissionCount} quyền tiếng Việt và {$assignmentCount} lượt gán quyền theo vai trò.");

        return self::SUCCESS;
    }
}
