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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('code')->unique();

            $table->dateTime('transaction_date');

            $table->enum('type', [
                'receipt',
                'payment',
                'transfer'
            ]);

            $table->foreignId('category_id')
                ->constrained('transaction_categories');

            $table->foreignId('currency_id')
                ->constrained();

            $table->decimal('amount', 20, 2);

            $table->foreignId('from_account_id')
                ->nullable()
                ->constrained('accounts');

            $table->foreignId('to_account_id')
                ->nullable()
                ->constrained('accounts');

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('sales_order_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('purchase_order_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->text('description')->nullable();

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
        Schema::dropIfExists('transactions');
    }
};
