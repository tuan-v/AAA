<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PURPOSES = [
        'THU_KH' => 'customer_receipt',
        'THU_KHAC' => 'opening_customer_receipt',
        'TAM_UNG_KH' => 'customer_advance',
        'HOAN_TAM_UNG_KH' => 'customer_advance_refund',
        'CHI_NCC' => 'supplier_payment',
        'CHI_KHAC' => 'opening_supplier_payment',
        'TAM_UNG_NCC' => 'supplier_advance',
        'HOAN_TAM_UNG_NCC' => 'supplier_advance_refund',
        'CHUYEN_KHOAN' => 'internal_transfer',
        'THU_COD' => 'cod_receipt',
    ];

    public function up(): void
    {
        foreach (self::PURPOSES as $categoryCode => $purpose) {
            $categoryIds = DB::table('transaction_categories')
                ->where('code', $categoryCode)
                ->pluck('id');

            DB::table('transactions')
                ->whereIn('category_id', $categoryIds)
                ->update(['purpose' => $purpose]);
        }
    }

    public function down(): void
    {
        DB::table('transactions')->whereIn('purpose', [
            'opening_customer_receipt',
            'customer_advance',
            'customer_advance_refund',
        ])->update(['purpose' => 'customer_receipt']);

        DB::table('transactions')->whereIn('purpose', [
            'opening_supplier_payment',
            'supplier_advance',
            'supplier_advance_refund',
        ])->update(['purpose' => 'supplier_payment']);

        DB::table('transactions')->where('purpose', 'cod_receipt')->update(['purpose' => 'other_receipt']);
    }
};
