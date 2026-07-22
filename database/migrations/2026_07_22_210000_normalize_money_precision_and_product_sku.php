<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('currencies', fn (Blueprint $table) => $table->decimal('exchange_rate', 20, 8)->default(1)->change());
        Schema::table('purchase_orders', fn (Blueprint $table) => $table->decimal('exchange_rate', 20, 8)->default(1)->change());
        Schema::table('sales_orders', fn (Blueprint $table) => $table->decimal('exchange_rate', 20, 8)->default(1)->change());
        Schema::table('transactions', fn (Blueprint $table) => $table->decimal('exchange_rate', 20, 8)->default(1)->change());

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_sku_unique');
            $table->unique(['company_id', 'sku'], 'products_company_sku_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_company_sku_unique');
            $table->unique('sku', 'products_sku_unique');
        });
        Schema::table('transactions', fn (Blueprint $table) => $table->decimal('exchange_rate', 20, 6)->default(1)->change());
        Schema::table('sales_orders', fn (Blueprint $table) => $table->decimal('exchange_rate', 20, 6)->default(1)->change());
        Schema::table('purchase_orders', fn (Blueprint $table) => $table->decimal('exchange_rate', 20, 6)->default(1)->change());
        Schema::table('currencies', fn (Blueprint $table) => $table->decimal('exchange_rate', 20, 6)->default(1)->change());
    }
};
