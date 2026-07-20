<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('roles', 'hierarchy_level')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->unsignedSmallInteger('hierarchy_level')->default(10)->after('type')->index();
            });
        }

        foreach ([
            'Supper Admin' => 100,
            'Giám đốc' => 90,
            'Quản lý nhân sự' => 50,
            'Quản lý mua hàng' => 50,
            'Quản lý kho' => 50,
            'Quản lý bán hàng' => 50,
            'Quản lý kế toán' => 50,
            'Manager' => 40,
            'HR' => 30,
        ] as $name => $level) {
            DB::table('roles')->where('name', $name)->update(['hierarchy_level' => $level]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('roles', 'hierarchy_level')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropIndex(['hierarchy_level']);
                $table->dropColumn('hierarchy_level');
            });
        }
    }
};
