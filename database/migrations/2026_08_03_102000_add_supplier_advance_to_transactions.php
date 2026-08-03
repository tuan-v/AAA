<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('advance_applied_amount', 20, 2)->default(0)->after('amount_base');
            $table->decimal('advance_applied_base', 20, 2)->default(0)->after('advance_applied_amount');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', fn (Blueprint $table) => $table->dropColumn(['advance_applied_amount', 'advance_applied_base']));
    }
};
