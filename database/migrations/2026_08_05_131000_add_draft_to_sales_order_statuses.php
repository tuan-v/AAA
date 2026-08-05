<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE sales_orders MODIFY status ENUM('draft','pending','approved','partial','completed','cancelled') NOT NULL DEFAULT 'pending'"
            );
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('sales_orders')->where('status', 'draft')->update(['status' => 'pending']);
            DB::statement(
                "ALTER TABLE sales_orders MODIFY status ENUM('pending','approved','partial','completed','cancelled') NOT NULL DEFAULT 'pending'"
            );
        }
    }
};
