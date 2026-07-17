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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('code');
            $table->string('name');

            $table->enum('type', [
                'cash',
                'bank',
                'ewallet',
                'other'
            ]);

            $table->foreignId('currency_id')
                ->constrained();

            $table->decimal('opening_balance', 20, 2)
                ->default(0);

            $table->decimal('current_balance', 20, 2)
                ->default(0);

            $table->foreignId('bank_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('bank_account_no')
                ->nullable();

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
