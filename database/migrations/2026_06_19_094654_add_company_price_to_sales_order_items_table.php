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
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->decimal(
                'company_unit_price',
                18,
                2
            )->default(0)
                ->after('unit_price');

            $table->decimal(
                'company_amount',
                18,
                2
            )->default(0)
                ->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropColumn([
                'company_unit_price',
                'company_amount'
            ]);
        });
    }
};
