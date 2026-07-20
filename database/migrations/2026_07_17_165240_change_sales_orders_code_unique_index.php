<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            // Xóa unique cũ
            $table->dropUnique('sales_orders_code_unique');

            // Unique theo công ty + mã
            $table->unique(
                ['company_id', 'code'],
                'sales_orders_company_code_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropUnique('sales_orders_company_code_unique');

            $table->unique('code');
        });
    }
};
