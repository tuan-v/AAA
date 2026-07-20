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
        Schema::create('debt_histories', function (Blueprint $table) {
            $table->id();

            $table->enum('party_type', [
                'customer',
                'supplier'
            ]);

            $table->unsignedBigInteger('party_id');

            $table->foreignId('currency_id')
                ->constrained();

            $table->foreignId('sales_order_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('purchase_order_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('transaction_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->enum('type', [
                'increase',
                'decrease'
            ]);

            $table->decimal('amount', 20, 2);

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
        Schema::dropIfExists('debt_histories');
    }
};
