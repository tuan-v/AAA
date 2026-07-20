<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bổ sung company_id cho bảng `units` — bị thiếu trong bộ migration cũ
 * (khác với categories/products/customers/suppliers/warehouses/purchase_orders/
 * sales_orders/transactions đã được thêm company_id ở các migration trước đó).
 *
 * Theo đúng khuôn mẫu của add_company_id_to_categories_table.php:
 *   1. Thêm cột nullable
 *   2. Gán company mặc định cho dữ liệu cũ (nếu có)
 *   3. Thêm foreign key
 *   4. Thêm unique index (company_id, symbol) — chỉ khi symbol khác NULL
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('units', 'company_id')) {
            // 1. Thêm cột nullable trước
            Schema::table('units', function (Blueprint $table) {
                $table->foreignId('company_id')
                    ->nullable()
                    ->after('id');
            });

            // 2. Gán company mặc định cho dữ liệu cũ (nếu đã có bản ghi)
            $defaultCompanyId = DB::table('companies')->value('id');

            if ($defaultCompanyId) {
                DB::table('units')
                    ->whereNull('company_id')
                    ->update(['company_id' => $defaultCompanyId]);
            }

            // 3. Thêm foreign key + unique index (company_id, symbol) trong cùng
            // một lần alter với bước thêm cột, để tránh phải dò index đã tồn tại
            // hay chưa (không phụ thuộc doctrine/dbal).
            Schema::table('units', function (Blueprint $table) {
                $table->foreign('company_id')
                    ->references('id')
                    ->on('companies')
                    ->cascadeOnDelete();

                $table->unique(['company_id', 'symbol'], 'units_company_symbol_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('units', 'company_id')) {
            Schema::table('units', function (Blueprint $table) {
                $table->dropUnique('units_company_symbol_unique');
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }
    }
};
