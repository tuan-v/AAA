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
        if (Schema::hasTable('warehouse_slip_items') && !Schema::hasColumn('warehouse_slip_items', 'company_price')) {
            Schema::table('warehouse_slip_items', function (Blueprint $table) {
                $table->decimal('company_price', 18, 2)
                    ->default(0)
                    ->after('price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_slip_items', function (Blueprint $table) {
            //
        });
    }
};
