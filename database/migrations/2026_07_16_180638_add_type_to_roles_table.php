<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->cascadeOnDelete();

            if (! Schema::hasColumn('roles', 'type')) {
                // 'system'  -> vai trò có sẵn, do hệ thống seed, KHÔNG cho user tự tạo thêm loại này
                // 'user'    -> vai trò do người dùng (admin công ty) tự tạo
                $table->enum('type', ['system', 'user'])
                    ->default('user')
                    ->after('name');
            }

            if (! Schema::hasColumn('roles', 'is_protected')) {
                // Vai trò is_protected = true (vd Super Admin) sẽ:
                //  - Không hiển thị trong danh sách với user không phải Super Admin
                //  - Không thể sửa/xóa/gán quyền thêm bởi bất kỳ ai khác ngoài Super Admin
                $table->boolean('is_protected')->default(false)->after('type');
            }
        });

        // Đánh dấu các vai trò hệ thống đã tồn tại sẵn (điều chỉnh danh sách tên
        // cho khớp đúng với dữ liệu thật trong bảng roles của bạn).
        DB::table('roles')->whereIn('name', [
            'Super Admin',
            'Admin',
            'super-admin',
            'admin',
        ])->update(['type' => 'system']);

        // Riêng Super Admin -> protected, ẩn khỏi user thường
        DB::table('roles')->whereIn('name', [
            'Super Admin',
            'super-admin',
        ])->update(['is_protected' => true]);
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('roles', 'is_protected')) {
                $table->dropColumn('is_protected');
            }
        });
    }
};
