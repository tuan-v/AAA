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
        Schema::table('customers', function (Blueprint $table) {
            // Xóa unique cũ
            $table->dropUnique('customers_code_unique');

            // Unique theo company + code
            $table->unique(
                ['company_id', 'code'],
                'customers_company_code_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Xóa unique mới
            $table->dropUnique('customers_company_code_unique');

            // Khôi phục unique cũ
            $table->unique('code');
        });
    }
};
