<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('sales_channel', 20)->default('standard')->after('status');
            $table->foreignId('pos_warehouse_id')->nullable()->after('sales_channel')->constrained('warehouses')->nullOnDelete();
            $table->string('payment_method', 30)->nullable()->after('pos_warehouse_id');
            $table->decimal('paid_amount', 18, 2)->default(0)->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pos_warehouse_id');
            $table->dropColumn(['sales_channel', 'payment_method', 'paid_amount']);
        });
    }
};
