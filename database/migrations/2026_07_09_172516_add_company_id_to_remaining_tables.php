<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Bảng Phiếu nhập/xuất kho (warehouse_slips)
        if (!Schema::hasColumn('warehouse_slips', 'company_id')) {
            Schema::table('warehouse_slips', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->onDelete('cascade');
            });
        }

        // Thay tên bảng 'banks' bằng tên bảng ngân hàng thực tế của bạn nếu nó tên khác
        // 3. Bảng Ngân hàng (banks) nếu có bảng riêng độc lập với bảng accounts
        if (Schema::hasTable('banks') && !Schema::hasColumn('banks', 'company_id')) {
            Schema::table('banks', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('banks') && Schema::hasColumn('banks', 'company_id')) {
            Schema::table('banks', function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }
        if (Schema::hasColumn('warehouse_slips', 'company_id')) {
            Schema::table('warehouse_slips', function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }
    }
};
