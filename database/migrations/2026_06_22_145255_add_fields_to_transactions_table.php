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
        Schema::table('transactions', function (Blueprint $table) {

            // 1. Trạng thái giao dịch
            $table->enum('status', [
                'draft',
                'posted',
                'cancelled'
            ])->default('posted')->after('amount');

            // 2. Tỉ giá
            $table->decimal('exchange_rate', 20, 6)
                ->default(1)
                ->after('currency_id');

            // 3. Số tiền quy đổi hệ thống
            $table->decimal('amount_base', 20, 2)
                ->default(0)
                ->after('amount');

            // 4. Chuẩn hóa đối tượng (thay customer/supplier/order)
            $table->string('reference_type')
                ->nullable()
                ->after('purchase_order_id');

            $table->unsignedBigInteger('reference_id')
                ->nullable()
                ->after('reference_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'exchange_rate',
                'amount_base',
                'reference_type',
                'reference_id'
            ]);
        });
    }
};
