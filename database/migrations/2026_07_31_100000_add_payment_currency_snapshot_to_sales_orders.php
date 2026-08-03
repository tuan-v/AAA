<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('payment_currency_id')->nullable()->after('payment_method')->constrained('currencies')->nullOnDelete();
            $table->decimal('payment_exchange_rate', 20, 8)->default(1)->after('payment_currency_id');
            $table->decimal('payment_tendered_amount', 20, 2)->default(0)->after('payment_exchange_rate');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_currency_id');
            $table->dropColumn(['payment_exchange_rate', 'payment_tendered_amount']);
        });
    }
};
