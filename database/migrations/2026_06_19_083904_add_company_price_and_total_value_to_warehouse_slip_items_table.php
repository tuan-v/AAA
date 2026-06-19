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
        Schema::table('warehouse_slip_items', function (Blueprint $table) {
            $table->decimal('total_value', 18, 2)->default(0)->after('company_price');
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_slip_items', function (Blueprint $table) {
            $table->dropColumn(['company_price', 'total_value']);
        });
    }
};
