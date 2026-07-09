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
        if (Schema::hasTable('warehouse_slip_items')) {
            Schema::table('warehouse_slip_items', function (Blueprint $table) {
                if (!Schema::hasColumn('warehouse_slip_items', 'cost_price')) {
                    $table->decimal(
                        'cost_price',
                        18,
                        2
                    )->default(0)
                        ->after('company_price');
                }

                if (!Schema::hasColumn('warehouse_slip_items', 'cost_amount')) {
                    $table->decimal(
                        'cost_amount',
                        18,
                        2
                    )->default(0)
                        ->after('cost_price');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_slip_items', function (Blueprint $table) {
            $table->dropColumn([
                'cost_price',
                'cost_amount',
            ]);
        });
    }
};
