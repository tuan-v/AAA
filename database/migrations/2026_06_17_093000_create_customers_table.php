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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('code')->unique();

            $table->string('name');

            $table->string('email')->nullable();

            $table->string('phone')->nullable();

            $table->foreignId('currency_id')->nullable();

            $table->decimal('opening_debt', 18, 2)
                ->default(0);

            $table->foreignId('province_id')
                ->nullable();

            $table->foreignId('ward_id')
                ->nullable();

            $table->text('address_detail')
                ->nullable();

            $table->enum('status', [
                'active',
                'inactive'
            ])->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
