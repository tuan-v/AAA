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
        Schema::create('account_ledgers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('transaction_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->dateTime('ledger_date');

            $table->decimal('debit', 20, 2)
                ->default(0);

            $table->decimal('credit', 20, 2)
                ->default(0);

            $table->decimal('balance_after', 20, 2);

            $table->text('description')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_ledgers');
    }
};
