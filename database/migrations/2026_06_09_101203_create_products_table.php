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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('unit_id')
                ->constrained('units')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->string('barcode')->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity');
            $table->string("color");
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('sell_price', 15, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active')
                ->comment("active: Còn hàng, inactive: Hết hàng");
            $table->enum('type', [
                'hang_hoa',
                'vat_tu',
                'dich_vu'
            ])->default('hang_hoa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
