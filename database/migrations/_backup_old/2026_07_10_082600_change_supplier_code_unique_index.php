<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Xóa unique cũ
            $table->dropUnique('suppliers_code_unique');

            // Unique theo company
            $table->unique(['company_id', 'code'], 'suppliers_company_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropUnique('suppliers_company_code_unique');

            $table->unique('code');
        });
    }
};
