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
        Schema::create('sales_orders', function (Blueprint $table) {

            $table->id();

            $table->string('code')
                ->unique();

            $table->foreignId('company_id');

            $table->foreignId('customer_id');

            $table->foreignId('currency_id')
                ->nullable();

            $table->foreignId('province_id')
                ->nullable();

            $table->foreignId('ward_id')
                ->nullable();

            $table->text('address_detail')
                ->nullable();

            $table->date('expected_delivery_date')
                ->nullable();

            $table->text('note')
                ->nullable();

            $table->decimal('subtotal', 18, 2)
                ->default(0);

            $table->decimal('vat_amount', 18, 2)
                ->default(0);

            $table->decimal('total_amount', 18, 2)
                ->default(0);

            $table->enum('status', [
                'pending',
                'approved',
                'partial',
                'completed',
                'cancelled'
            ])->default('pending');

            $table->foreignId('created_by');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
