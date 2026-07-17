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
        if (!Schema::hasColumn('warehouses', 'status')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->enum('status', ['active', 'inactive'])->default('active')
                    ->comment("active: Đang hoạt động, inactive: Không hoạt động")
                    ->after('total_inventory_value');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
