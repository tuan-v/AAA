<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_code_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('model_type', 191);
            $table->string('prefix', 30);
            $table->unsignedBigInteger('current_number')->default(0);
            $table->timestamps();
            $table->unique(['company_id', 'model_type', 'prefix'], 'company_code_sequence_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_code_sequences');
    }
};
