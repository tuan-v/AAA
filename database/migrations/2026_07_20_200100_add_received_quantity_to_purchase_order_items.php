<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('purchase_order_items', 'received_quantity')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->decimal('received_quantity', 18, 2)->default(0)->after('quantity');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('purchase_order_items', 'received_quantity')) {
            Schema::table('purchase_order_items', fn (Blueprint $table) => $table->dropColumn('received_quantity'));
        }
    }
};
