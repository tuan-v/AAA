<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('type', 30); // import, export, transfer_in, transfer_out
            $table->decimal('quantity', 20, 3);
            $table->decimal('unit_cost', 20, 6)->default(0);
            $table->decimal('total_value', 20, 2)->default(0);
            $table->decimal('quantity_before', 20, 3)->default(0);
            $table->decimal('quantity_after', 20, 3)->default(0);
            $table->decimal('value_before', 20, 2)->default(0);
            $table->decimal('value_after', 20, 2)->default(0);
            $table->nullableMorphs('reference');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'warehouse_id', 'product_id', 'created_at'], 'inventory_movement_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
