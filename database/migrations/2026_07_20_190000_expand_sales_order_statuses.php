<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE sales_orders MODIFY status ENUM('pending','approved','partial','completed','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE sales_orders SET status = 'approved' WHERE status IN ('partial','completed')");
            DB::statement("ALTER TABLE sales_orders MODIFY status ENUM('pending','approved','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }
};
