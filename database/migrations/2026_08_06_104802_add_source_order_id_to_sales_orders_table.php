<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Thêm cột source_order_id để theo dõi đơn hàng gốc khi tạo từ lịch sử.
     */
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('source_order_id')->nullable()->after('company_id');
            $table->foreign('source_order_id')->references('id')->on('sales_orders')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['source_order_id']);
            $table->dropColumn('source_order_id');
        });
    }
};
