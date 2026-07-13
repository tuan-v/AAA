<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banks', function (Blueprint $table) {

            // Xóa unique cũ: chỉ cho phép toàn hệ thống có 1 mã NH001
            $table->dropUnique('banks_code_unique');

            // Tạo unique mới theo công ty
            // Mỗi công ty có thể có NH001 riêng
            $table->unique(
                ['company_id', 'code'],
                'banks_company_code_unique'
            );
        });
    }


    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {

            // Xóa unique mới
            $table->dropUnique('banks_company_code_unique');

            // Khôi phục unique cũ
            $table->unique(
                'code',
                'banks_code_unique'
            );
        });
    }
};
