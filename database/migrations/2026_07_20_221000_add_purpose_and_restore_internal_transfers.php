<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('purpose', 30)->nullable()->after('payment_method')->index();
        });

        DB::table('transactions')
            ->where('type', 'payment')->where('payment_method', 'bank_transfer')
            ->whereNotNull('from_account_id')->whereNotNull('to_account_id')
            ->whereNull('customer_id')->whereNull('supplier_id')
            ->whereNull('sales_order_id')->whereNull('purchase_order_id')
            ->update(['type' => 'transfer', 'purpose' => 'internal_transfer']);

        DB::table('transactions')->whereNull('purpose')->orderBy('id')->eachById(function ($transaction) {
            $purpose = match (true) {
                $transaction->type === 'receipt' && $transaction->customer_id !== null => 'customer_receipt',
                $transaction->type === 'payment' && $transaction->supplier_id !== null => 'supplier_payment',
                $transaction->type === 'payment' && $transaction->customer_id !== null => 'customer_refund',
                $transaction->type === 'receipt' && $transaction->supplier_id !== null => 'supplier_refund',
                $transaction->type === 'receipt' => 'other_receipt',
                default => 'other_payment',
            };
            DB::table('transactions')->where('id', $transaction->id)->update(['purpose' => $purpose]);
        });

        DB::table('transaction_categories')->where('code', 'CHUYEN_KHOAN')->update([
            'name' => 'Chuyển tiền nội bộ', 'type' => 'transfer',
        ]);
    }

    public function down(): void
    {
        Schema::table('transactions', fn (Blueprint $table) => $table->dropColumn('purpose'));
    }
};
