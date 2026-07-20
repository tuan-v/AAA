<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'subtotal')) {
                $table->decimal('subtotal', 18, 2)->default(0);
            }
            if (! Schema::hasColumn('purchase_orders', 'vat_amount')) {
                $table->decimal('vat_amount', 18, 2)->default(0);
            }
            if (! Schema::hasColumn('purchase_orders', 'total_amount')) {
                $table->decimal('total_amount', 18, 2)->default(0);
            }
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_order_items', 'vat_percent')) {
                $table->decimal('vat_percent', 5, 2)->default(0);
            }
        });

        DB::table('purchase_orders')->orderBy('id')->each(function ($order) {
            $subtotal = (float) DB::table('purchase_order_items')
                ->where('purchase_order_id', $order->id)
                ->sum('amount');
            $vatAmount = (float) DB::table('purchase_order_items')
                ->where('purchase_order_id', $order->id)
                ->selectRaw('COALESCE(SUM(amount * vat_percent / 100), 0) AS total')
                ->value('total');

            DB::table('purchase_orders')->where('id', $order->id)->update([
                'subtotal' => round($subtotal, 2),
                'vat_amount' => round($vatAmount, 2),
                'total_amount' => round($subtotal + $vatAmount, 2),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_order_items', 'vat_percent')) {
                $table->dropColumn('vat_percent');
            }
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $columns = collect(['subtotal', 'vat_amount', 'total_amount'])
                ->filter(fn ($column) => Schema::hasColumn('purchase_orders', $column))
                ->all();
            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
