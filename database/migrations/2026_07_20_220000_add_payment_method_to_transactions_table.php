<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_method', 20)->default('cash')->after('type');
        });
        DB::table('transactions')->where('type', 'transfer')->update(['type' => 'payment', 'payment_method' => 'bank_transfer']);
        DB::table('transaction_categories')->where('type', 'transfer')->update(['type' => 'expense']);
    }

    public function down(): void
    {
        Schema::table('transactions', fn (Blueprint $table) => $table->dropColumn('payment_method'));
    }
};
