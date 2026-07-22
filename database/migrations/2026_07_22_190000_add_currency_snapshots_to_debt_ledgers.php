<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['customer_debts', 'supplier_debts'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('currency_id')->nullable()->after('amount')->constrained('currencies')->nullOnDelete();
                $table->decimal('original_amount', 20, 2)->nullable()->after('currency_id');
                $table->decimal('exchange_rate', 20, 8)->default(1)->after('original_amount');
                $table->decimal('amount_base', 20, 2)->nullable()->after('exchange_rate');
            });
        }

        $this->backfill('customer_debts', 'customers', 'customer_id');
        $this->backfill('supplier_debts', 'suppliers', 'supplier_id');
    }

    private function backfill(string $ledgerTable, string $partyTable, string $partyForeignKey): void
    {
        DB::table($ledgerTable)
            ->select("{$ledgerTable}.id", "{$ledgerTable}.amount", "{$partyTable}.company_id")
            ->join($partyTable, "{$partyTable}.id", '=', "{$ledgerTable}.{$partyForeignKey}")
            ->orderBy("{$ledgerTable}.id")
            ->chunk(200, function ($rows) use ($ledgerTable) {
                foreach ($rows as $row) {
                    $currencyId = DB::table('companies_has_currencies')
                        ->where('company_id', $row->company_id)
                        ->where('is_default', true)
                        ->value('currency_id');

                    DB::table($ledgerTable)->where('id', $row->id)->update([
                        'currency_id' => $currencyId,
                        'original_amount' => $row->amount,
                        'exchange_rate' => 1,
                        'amount_base' => $row->amount,
                    ]);
                }
            });
    }

    public function down(): void
    {
        foreach (['customer_debts', 'supplier_debts'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['currency_id']);
                $table->dropColumn(['currency_id', 'original_amount', 'exchange_rate', 'amount_base']);
            });
        }
    }
};
