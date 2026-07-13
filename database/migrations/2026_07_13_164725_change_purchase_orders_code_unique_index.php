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
        Schema::table('purchase_orders', function (Blueprint $table) {

            // Xóa unique cũ của code
            $table->dropUnique('purchase_orders_code_unique');

            // Tạo unique mới theo công ty
            $table->unique(
                ['company_id', 'code'],
                'purchase_orders_company_code_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {

            $table->dropUnique('purchase_orders_company_code_unique');

            $table->unique(
                'code',
                'purchase_orders_code_unique'
            );
        });
    }
};
