<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_partners', function (Blueprint $table) {
            $table->string('tracking_url_template')->nullable()->after('email');
        });
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->text('shipping_note')->nullable()->after('tracking_code');
            $table->unique(['company_id', 'tracking_code']);
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'tracking_code']);
            $table->dropColumn('shipping_note');
        });
        Schema::table('shipping_partners', function (Blueprint $table) {
            $table->dropColumn('tracking_url_template');
        });
    }
};
