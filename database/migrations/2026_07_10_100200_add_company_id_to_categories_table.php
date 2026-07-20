<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'company_id')) {

            // 1. Thêm cột nullable trước
            Schema::table('categories', function (Blueprint $table) {
                $table->foreignId('company_id')
                    ->nullable()
                    ->after('id');
            });

            // 2. Gán company mặc định cho dữ liệu cũ
            $defaultCompanyId = DB::table('companies')->value('id');

            if ($defaultCompanyId) {
                DB::table('categories')
                    ->whereNull('company_id')
                    ->update([
                        'company_id' => $defaultCompanyId,
                    ]);
            }

            // 3. Thêm foreign key
            Schema::table('categories', function (Blueprint $table) {
                $table->foreign('company_id')
                    ->references('id')
                    ->on('companies')
                    ->cascadeOnDelete();
            });

            // 4. Nếu muốn bắt buộc company_id thì bỏ comment
            /*
            Schema::table('categories', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable(false)->change();
            });
            */
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('categories', 'company_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }
    }
};
