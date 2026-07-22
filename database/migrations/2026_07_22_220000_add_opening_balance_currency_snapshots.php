<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('opening_debt_exchange_rate', 20, 8)->default(1)->after('opening_debt');
            $table->decimal('opening_debt_base', 20, 2)->default(0)->after('opening_debt_exchange_rate');
        });
        Schema::table('suppliers', function (Blueprint $table) {
            $table->decimal('opening_debt_exchange_rate', 20, 8)->default(1)->after('opening_debt');
            $table->decimal('opening_debt_base', 20, 2)->default(0)->after('opening_debt_exchange_rate');
            $table->decimal('opening_advance_exchange_rate', 20, 8)->default(1)->after('opening_advance');
            $table->decimal('opening_advance_base', 20, 2)->default(0)->after('opening_advance_exchange_rate');
        });

        DB::table('customers')->update([
            'opening_debt_exchange_rate' => 1,
            'opening_debt_base' => DB::raw('opening_debt'),
        ]);
        DB::table('suppliers')->update([
            'opening_debt_exchange_rate' => 1,
            'opening_debt_base' => DB::raw('opening_debt'),
            'opening_advance_exchange_rate' => 1,
            'opening_advance_base' => DB::raw('opening_advance'),
        ]);
    }

    public function down(): void
    {
        Schema::table('customers', fn (Blueprint $table) =>
            $table->dropColumn(['opening_debt_exchange_rate', 'opening_debt_base'])
        );
        Schema::table('suppliers', fn (Blueprint $table) =>
            $table->dropColumn([
                'opening_debt_exchange_rate', 'opening_debt_base',
                'opening_advance_exchange_rate', 'opening_advance_base',
            ])
        );
    }
};
