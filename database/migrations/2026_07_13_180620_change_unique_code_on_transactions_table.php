<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {

            // Xóa unique cũ
            $table->dropUnique('transactions_code_unique');

            // Tạo unique mới theo công ty
            $table->unique(
                ['company_id', 'code'],
                'transactions_company_code_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {

            $table->dropUnique('transactions_company_code_unique');

            $table->unique('code');
        });
    }
};
