<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('warehouse_slip_item') && ! Schema::hasTable('warehouse_slip_items')) {
            Schema::rename('warehouse_slip_item', 'warehouse_slip_items');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('warehouse_slip_items') && ! Schema::hasTable('warehouse_slip_item')) {
            Schema::rename('warehouse_slip_items', 'warehouse_slip_item');
        }
    }
};
