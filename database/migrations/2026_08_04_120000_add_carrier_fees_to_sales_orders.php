<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->decimal('carrier_shipping_fee', 18, 2)->default(0)->after('shipping_fee');
            $table->decimal('carrier_service_fee', 18, 2)->default(0)->after('carrier_shipping_fee');
            $table->decimal('carrier_insurance_fee', 18, 2)->default(0)->after('carrier_service_fee');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['carrier_shipping_fee', 'carrier_service_fee', 'carrier_insurance_fee']);
        });
    }
};
