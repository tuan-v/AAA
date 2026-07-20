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
                if (!Schema::hasColumn('warehouse_slip_items', 'purchase_order_item_id')) {
                    $table->foreignId('purchase_order_item_id')
                        ->nullable()
                        ->after('product_id')
                        ->constrained('purchase_order_items')
                        ->nullOnDelete();
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
            //
        });
    }
};
