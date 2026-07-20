<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('warehouse_slip_items', 'company_price')) {
            Schema::table('warehouse_slip_items', function (Blueprint $table) {
                $table->decimal('company_price', 18, 2)->default(0);
            });
        }

        if (! Schema::hasColumn('warehouse_slip_items', 'cost_price')) {
            Schema::table('warehouse_slip_items', function (Blueprint $table) {
                $table->decimal('cost_price', 18, 2)->default(0);
            });
        }

        if (! Schema::hasColumn('warehouse_slip_items', 'cost_amount')) {
            Schema::table('warehouse_slip_items', function (Blueprint $table) {
                $table->decimal('cost_amount', 18, 2)->default(0);
            });
        }

        if (! Schema::hasColumn('warehouse_slip_items', 'vat_percent')) {
            Schema::table('warehouse_slip_items', function (Blueprint $table) {
                $table->decimal('vat_percent', 5, 2)->default(0);
            });
        }

        Schema::table('customer_debts', function (Blueprint $table) {
            $table->unique(
                ['type', 'reference_type', 'reference_id'],
                'customer_debts_type_reference_unique'
            );
        });

        Schema::table('supplier_debts', function (Blueprint $table) {
            $table->unique(
                ['type', 'reference_type', 'reference_id'],
                'supplier_debts_type_reference_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('supplier_debts', function (Blueprint $table) {
            $table->dropUnique('supplier_debts_type_reference_unique');
        });

        Schema::table('customer_debts', function (Blueprint $table) {
            $table->dropUnique('customer_debts_type_reference_unique');
        });

        if (Schema::hasColumn('warehouse_slip_items', 'vat_percent')) {
            Schema::table('warehouse_slip_items', function (Blueprint $table) {
                $table->dropColumn('vat_percent');
            });
        }
    }
};
