<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nhóm 12: Công nợ
 *
 * Bảng:
 *   - customer_debts    (lịch sử công nợ phải thu từ KH)
 *   - supplier_debts    (lịch sử công nợ phải trả cho NCC)
 *   - debt_histories    (sổ cái công nợ tổng hợp — cả KH lẫn NCC)
 *
 * Luồng sinh công nợ:
 *   KH: SO approved → customer_debts (type=sale, amount+) → debt_histories (increase)
 *       Thanh toán  → customer_debts (type=payment, amount-) → debt_histories (decrease)
 *       Hoàn tiền  → customer_debts (type=refund)
 *
 *   NCC: PO approved → supplier_debts (type=invoice, amount+) → debt_histories (increase)
 *        Thanh toán → supplier_debts (type=payment, amount-) → debt_histories (decrease)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── customer_debts ─────────────────────────────────────────────────
        Schema::create('customer_debts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            // opening=Đầu kỳ | sale=Phát sinh từ SO | payment=KH thanh toán | refund=Công ty hoàn tiền
            $table->enum('type', ['opening', 'sale', 'payment', 'refund'])
                ->comment('opening=Đầu kỳ | sale=Phát sinh bán hàng | payment=KH trả | refund=Hoàn tiền KH');

            // Dương (+) = tăng công nợ phải thu | Âm (-) = giảm công nợ
            $table->decimal('amount', 18, 2)
                ->comment('Số tiền (+tăng nợ / -giảm nợ)');

            // Tham chiếu bản ghi nguồn (polymorphic — SalesOrder, Transaction...)
            $table->string('reference_type')->nullable()
                ->comment('Model nguồn: SalesOrder | Transaction');
            $table->unsignedBigInteger('reference_id')->nullable()
                ->comment('ID bản ghi nguồn');

            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('customer_id');
            $table->index(['reference_type', 'reference_id']);
            $table->index('type');
            $table->comment('Lịch sử công nợ phải thu của từng khách hàng');
        });

        // ── supplier_debts ─────────────────────────────────────────────────
        Schema::create('supplier_debts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('supplier_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            // opening=Đầu kỳ | invoice=Phát sinh từ PO | payment=Đã thanh toán NCC | adjustment=Điều chỉnh
            $table->string('type', 30)
                ->comment('opening=Đầu kỳ | invoice=Phát sinh mua hàng | payment=Trả NCC | adjustment=Điều chỉnh');

            // Dương (+) = tăng công nợ phải trả | Âm (-) = giảm công nợ
            $table->decimal('amount', 18, 2)
                ->comment('Số tiền (+tăng nợ / -giảm nợ)');

            // Tham chiếu bản ghi nguồn (PurchaseOrder hoặc Transaction)
            $table->string('reference_type')->nullable()
                ->comment('Model nguồn: PurchaseOrder | Transaction');
            $table->unsignedBigInteger('reference_id')->nullable()
                ->comment('ID bản ghi nguồn');

            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('supplier_id');
            $table->index('company_id');
            $table->index(['reference_type', 'reference_id']);
            $table->index('type');
            $table->comment('Lịch sử công nợ phải trả của từng nhà cung cấp');
        });

        // ── debt_histories ─────────────────────────────────────────────────
        Schema::create('debt_histories', function (Blueprint $table) {
            $table->id();

            // customer | supplier (polymorphic đơn giản — không dùng morphMap)
            $table->enum('party_type', ['customer', 'supplier'])
                ->comment('customer=Khách hàng | supplier=Nhà cung cấp');
            $table->unsignedBigInteger('party_id')
                ->comment('ID của khách hàng hoặc nhà cung cấp');

            $table->foreignId('currency_id')
                ->constrained()
                ->restrictOnDelete();

            // Đơn hàng nguồn (một trong hai)
            $table->foreignId('sales_order_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('purchase_order_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Giao dịch thanh toán liên quan
            $table->foreignId('transaction_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // increase=Tăng công nợ | decrease=Giảm công nợ
            $table->enum('type', ['increase', 'decrease'])
                ->comment('increase=Tăng công nợ | decrease=Giảm công nợ (thanh toán)');

            $table->decimal('amount', 20, 2)->comment('Số tiền biến động');

            $table->decimal('balance_after', 20, 2)
                ->comment('Số dư công nợ sau biến động');

            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['party_type', 'party_id']);
            $table->index(['party_type', 'party_id', 'created_at']);
            $table->comment('Sổ cái công nợ tổng hợp (KH & NCC)');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_histories');
        Schema::dropIfExists('supplier_debts');
        Schema::dropIfExists('customer_debts');
    }
};
