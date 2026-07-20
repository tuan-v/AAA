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
        Schema::create('warehouse_stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // in / out
            $table->enum('type', ['import', 'export']);

            $table->integer('quantity'); // luôn dương
            $table->decimal('unit_price', 15, 2)->nullable();

            // link về phiếu nhập/xuất
            $table->foreignId('slip_id')->constrained('warehouse_slips')->cascadeOnDelete();

            $table->foreignId('slip_item_id')
                ->constrained('warehouse_slip_items')
                ->cascadeOnDelete();

            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_stock_movements_tale');
    }
};
