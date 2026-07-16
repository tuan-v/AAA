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
        Schema::table('warehouses', function (Blueprint $table) {
            // Xóa unique cũ
            $table->dropUnique('warehouses_code_unique');

            // Tạo unique mới theo company_id + code
            $table->unique(['company_id', 'code'], 'warehouses_company_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropUnique('warehouses_company_code_unique');

            $table->unique('code');
        });
    }
};