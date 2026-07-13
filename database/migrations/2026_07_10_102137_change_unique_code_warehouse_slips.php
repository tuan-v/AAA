<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_slips', function (Blueprint $table) {

            // Xóa unique cũ của code
            $table->dropUnique('warehouse_slips_code_unique');

            // Tạo unique mới theo company + code
            $table->unique(
                ['company_id', 'code'],
                'warehouse_slips_company_code_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_slips', function (Blueprint $table) {

            $table->dropUnique('warehouse_slips_company_code_unique');

            $table->unique(
                'code',
                'warehouse_slips_code_unique'
            );
        });
    }
};
