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
        Schema::create('fund_transfers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained();

            $table->string('code');

            $table->foreignId('from_account_id')
                ->constrained('accounts');

            $table->foreignId('to_account_id')
                ->constrained('accounts');

            $table->decimal('amount', 20, 2);

            $table->foreignId('currency_id')
                ->constrained();

            $table->text('note')
                ->nullable();

            $table->foreignId('created_by')
                ->constrained('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_transfers');
    }
};
