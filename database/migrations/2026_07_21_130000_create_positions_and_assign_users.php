<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->string('code', 50);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->unique(['company_id', 'code']);
            $table->unique(['department_id', 'name']);
            $table->index(['company_id', 'status']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('position_id')->nullable()->after('department_id')->constrained('positions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('position_id'));
        Schema::dropIfExists('positions');
    }
};
