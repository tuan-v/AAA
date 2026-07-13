<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Chỉ thêm cột nếu chưa có (vì lần trước đã lỡ tạo thành công)
        if (!Schema::hasColumn('warehouse_product_stocks', 'company_id')) {
            Schema::table('warehouse_product_stocks', function (Blueprint $table) {
                $table->foreignId('company_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('companies')
                    ->cascadeOnDelete();
            });
        }

        // 2. Backfill company_id dựa theo warehouse_id (chỉ ảnh hưởng dòng đang NULL)
        DB::statement('
            UPDATE warehouse_product_stocks AS wps
            INNER JOIN warehouses AS w ON w.id = wps.warehouse_id
            SET wps.company_id = w.company_id
            WHERE wps.company_id IS NULL
        ');

        // 3. Xóa các dòng tồn kho rác không thuộc kho nào (orphan) - vẫn còn NULL sau backfill
        DB::table('warehouse_product_stocks')
            ->whereNull('company_id')
            ->delete();

        // 4. Bắt buộc NOT NULL (idempotent - Doctrine change() chạy lại vẫn được vì nullable() gọi lại không lỗi)
        Schema::table('warehouse_product_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
        });

        // 5. Tạo index nếu chưa có
        $indexExists = collect(DB::select("SHOW INDEX FROM warehouse_product_stocks WHERE Key_name = 'wps_company_warehouse_product_idx'"))->isNotEmpty();

        if (!$indexExists) {
            Schema::table('warehouse_product_stocks', function (Blueprint $table) {
                $table->index(['company_id', 'warehouse_id', 'product_id'], 'wps_company_warehouse_product_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::table('warehouse_product_stocks', function (Blueprint $table) {
            $table->dropIndex('wps_company_warehouse_product_idx');
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
