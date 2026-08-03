<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sales_orders', 'tendered_amount')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->decimal('tendered_amount', 18, 2)->default(0)->after('discount_amount');
            });
        }

        if (! Schema::hasColumn('sales_orders', 'change_amount')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->decimal('change_amount', 18, 2)->default(0)->after('tendered_amount');
            });
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['tendered_amount', 'change_amount'],
            fn (string $column) => Schema::hasColumn('sales_orders', $column)
        ));

        if ($columns !== []) {
            Schema::table('sales_orders', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
