<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Bảng users
        if (!Schema::hasColumn('users', 'company_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->onDelete('cascade');
            });
        }

        // 2. Bảng products
        if (!Schema::hasColumn('products', 'company_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->onDelete('cascade');
            });
        }

        // 3. Bảng customers
        if (!Schema::hasColumn('customers', 'company_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->onDelete('cascade');
            });
        }

        // 4. Bảng suppliers
        if (!Schema::hasColumn('suppliers', 'company_id')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->onDelete('cascade');
            });
        }

        // 5. Bảng warehouses
        if (!Schema::hasColumn('warehouses', 'company_id')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->onDelete('cascade');
            });
        }

        // 6. Bảng purchase_orders
        if (!Schema::hasColumn('purchase_orders', 'company_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->onDelete('cascade');
            });
        }

        // 7. Bảng sales_orders
        if (!Schema::hasColumn('sales_orders', 'company_id')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->onDelete('cascade');
            });
        }

        // 8. Bảng transactions
        if (!Schema::hasColumn('transactions', 'company_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Khi xóa (rollback), chỉ xóa nếu cột đó thực sự tồn tại
        $tables = ['transactions', 'sales_orders', 'purchase_orders', 'warehouses', 'suppliers', 'customers', 'products', 'users'];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $tableGroup) {
                    $tableGroup->dropForeign(['company_id']);
                    $tableGroup->dropColumn('company_id');
                });
            }
        }
    }
};
