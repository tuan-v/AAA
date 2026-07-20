<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', fn (Blueprint $table) => $table->decimal('quantity', 18, 2)->default(0)->change());
        Schema::table('warehouse_product_stocks', fn (Blueprint $table) => $table->decimal('quantity', 18, 2)->default(0)->change());
        Schema::table('warehouse_slip_items', fn (Blueprint $table) => $table->decimal('quantity', 18, 2)->change());
        Schema::table('warehouse_stock_movements', fn (Blueprint $table) => $table->decimal('quantity', 18, 2)->change());
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $table) => $table->integer('quantity')->default(0)->change());
        Schema::table('warehouse_product_stocks', fn (Blueprint $table) => $table->integer('quantity')->default(0)->change());
        Schema::table('warehouse_slip_items', fn (Blueprint $table) => $table->integer('quantity')->change());
        Schema::table('warehouse_stock_movements', fn (Blueprint $table) => $table->integer('quantity')->change());
    }
};
