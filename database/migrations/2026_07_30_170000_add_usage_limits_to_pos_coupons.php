<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pos_coupons', 'usage_limit')) {
            Schema::table('pos_coupons', function (Blueprint $table) {
                $table->unsignedInteger('usage_limit')->nullable()->after('is_active');
            });
        }

        if (! Schema::hasColumn('pos_coupons', 'used_count')) {
            Schema::table('pos_coupons', function (Blueprint $table) {
                $table->unsignedInteger('used_count')->default(0)->after('usage_limit');
            });
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['usage_limit', 'used_count'],
            fn (string $column) => Schema::hasColumn('pos_coupons', $column)
        ));

        if ($columns !== []) {
            Schema::table('pos_coupons', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
