<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        foreach (DB::table('companies')->pluck('id') as $companyId) {
            DB::table('transaction_categories')->updateOrInsert(
                ['company_id' => $companyId, 'code' => 'TAM_UNG_NCC'],
                ['name' => 'Tạm ứng nhà cung cấp', 'type' => 'expense', 'status' => 1, 'updated_at' => $now, 'created_at' => $now]
            );
            DB::table('transaction_categories')->updateOrInsert(
                ['company_id' => $companyId, 'code' => 'HOAN_TAM_UNG_NCC'],
                ['name' => 'Nhà cung cấp hoàn tạm ứng', 'type' => 'income', 'status' => 1, 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }

    public function down(): void
    {
        DB::table('transaction_categories')
            ->whereIn('code', ['TAM_UNG_NCC', 'HOAN_TAM_UNG_NCC'])
            ->delete();
    }
};
