<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sales_orders')
            ->where('status', 'cancelled')
            ->where('return_status', 'returned')
            ->update(['status' => 'partial']);
    }

    public function down(): void
    {
        DB::table('sales_orders')
            ->where('status', 'partial')
            ->where('return_status', 'returned')
            ->update(['status' => 'cancelled']);
    }
};
