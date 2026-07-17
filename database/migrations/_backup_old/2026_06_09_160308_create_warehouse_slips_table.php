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
        Schema::create('warehouse_slips', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();

            // import | export
            $table->enum('type', ['import', 'export']);

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // người duyệt phiếu
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // thời gian duyệt
            $table->timestamp('approved_at')->nullable();

            // trạng thái
            $table->enum(
                'status',
                [
                    'pending',      // chờ duyệt
                    'approved',     // đã duyệt
                    'rejected'      // từ chối
                ]
            )->default('draft');

            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_slips');
    }
};
