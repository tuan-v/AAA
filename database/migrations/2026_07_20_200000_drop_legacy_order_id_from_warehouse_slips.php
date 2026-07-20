<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('warehouse_slips', 'order_id')) {
            return;
        }

        Schema::table('warehouse_slips', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_slips', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->constrained('orders')->cascadeOnDelete();
        });
    }
};
