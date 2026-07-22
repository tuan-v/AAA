<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL có thể dùng unique (department_id, name) làm index cho khóa
        // ngoại department_id. Tạo index độc lập trước để có thể xóa unique.
        Schema::table('positions', function (Blueprint $table) {
            $table->index('department_id', 'positions_department_id_index');
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->dropUnique('positions_department_id_name_unique');
            $table->unique(['company_id', 'name'], 'positions_company_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropUnique('positions_company_name_unique');
            $table->unique(['department_id', 'name'], 'positions_department_id_name_unique');
        });
    }
};
