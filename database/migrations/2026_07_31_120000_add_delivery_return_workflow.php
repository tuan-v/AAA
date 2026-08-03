<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_slips', function (Blueprint $table) {
            $table->string('return_status', 32)->nullable()->after('status');
            $table->text('return_reason')->nullable()->after('return_status');
            $table->foreignId('return_requested_by')->nullable()->after('return_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('return_requested_at')->nullable()->after('return_requested_by');
            $table->foreignId('return_received_by')->nullable()->after('return_requested_at')->constrained('users')->nullOnDelete();
            $table->timestamp('return_received_at')->nullable()->after('return_received_by');
            $table->foreignId('return_approved_by')->nullable()->after('return_received_at')->constrained('users')->nullOnDelete();
            $table->timestamp('return_approved_at')->nullable()->after('return_approved_by');
        });

        Schema::table('warehouse_slip_items', function (Blueprint $table) {
            $table->decimal('returned_quantity', 18, 3)->default(0)->after('quantity');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('return_status', 32)->nullable()->after('status');
            $table->timestamp('returned_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', fn (Blueprint $table) => $table->dropColumn(['return_status', 'returned_at']));
        Schema::table('warehouse_slip_items', fn (Blueprint $table) => $table->dropColumn('returned_quantity'));
        Schema::table('warehouse_slips', function (Blueprint $table) {
            $table->dropConstrainedForeignId('return_requested_by');
            $table->dropConstrainedForeignId('return_received_by');
            $table->dropConstrainedForeignId('return_approved_by');
            $table->dropColumn(['return_status', 'return_reason', 'return_requested_at', 'return_received_at', 'return_approved_at']);
        });
    }
};
