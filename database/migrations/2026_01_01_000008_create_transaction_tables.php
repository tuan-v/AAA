<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nhóm 11: Giao dịch tài chính & Chuyển khoản nội bộ
 *
 * Bảng:
 *   - transactions     (giao dịch thu/chi/chuyển khoản)
 *   - fund_transfers   (chuyển quỹ nội bộ giữa các tài khoản)
 *
 * Luồng nghiệp vụ:
 *   - Nhận thanh toán từ KH → type=receipt, from_account=null, to_account=tài_khoản_nhận
 *   - Thanh toán cho NCC    → type=payment, from_account=tài_khoản_trả, to_account=null
 *   - Chuyển khoản nội bộ  → type=transfer, from_account + to_account đều có giá trị
 *
 * Trạng thái:
 *   pending = Chờ duyệt
 *   approve = Đã duyệt (tác động số liệu: cập nhật số dư + công nợ)
 *   reject  = Từ chối
 *
 * Unique code: [company_id, code]
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── transactions ───────────────────────────────────────────────────
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('code')->comment('Mã giao dịch (unique trong công ty)');

            $table->dateTime('transaction_date')->comment('Ngày giờ giao dịch');

            // receipt=Thu vào | payment=Chi ra | transfer=Chuyển khoản nội bộ
            $table->enum('type', ['receipt', 'payment', 'transfer'])
                ->comment('receipt=Thu vào | payment=Chi ra | transfer=Chuyển nội bộ');

            $table->foreignId('category_id')
                ->constrained('transaction_categories')
                ->restrictOnDelete();

            // Tiền tệ & tỷ giá
            $table->foreignId('currency_id')
                ->constrained()
                ->restrictOnDelete();

            $table->decimal('exchange_rate', 20, 6)->default(1)
                ->comment('Tỷ giá tại thời điểm giao dịch');

            // Số tiền
            $table->decimal('amount', 20, 2)
                ->comment('Số tiền (theo tiền tệ của giao dịch)');

            $table->decimal('amount_base', 20, 2)->default(0)
                ->comment('Số tiền quy đổi về đồng tiền gốc (VND)');

            // Tài khoản nguồn & đích
            $table->foreignId('from_account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete()
                ->comment('Tài khoản trả ra (null với type=receipt)');

            $table->foreignId('to_account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete()
                ->comment('Tài khoản nhận vào (null với type=payment)');

            // Đối tượng liên quan (KH hoặc NCC — không bắt buộc cả hai)
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Đơn hàng liên quan (để ghi công nợ đúng đơn)
            $table->foreignId('sales_order_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('purchase_order_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Tham chiếu đa hình (dùng khi đối tượng không phải KH/NCC hoặc không có đơn hàng)
            $table->string('reference_type')->nullable()
                ->comment('Tên Model tham chiếu (polymorphic)');
            $table->unsignedBigInteger('reference_id')->nullable()
                ->comment('ID của bản ghi tham chiếu');

            $table->text('description')->nullable()->comment('Nội dung / ghi chú giao dịch');

            // pending=Chờ duyệt | approve=Đã duyệt | reject=Từ chối
            $table->enum('status', ['pending', 'approve', 'reject'])->default('pending')
                ->comment('pending=Chờ duyệt | approve=Đã duyệt (tác động số liệu) | reject=Từ chối');

            // Người tạo & duyệt
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'code'], 'transactions_company_code_unique');
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
            $table->comment('Giao dịch thu / chi / chuyển khoản');
        });

        // ── fund_transfers ─────────────────────────────────────────────────
        Schema::create('fund_transfers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('code')->comment('Mã phiếu chuyển quỹ');

            $table->foreignId('from_account_id')
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->foreignId('to_account_id')
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->decimal('amount', 20, 2)->comment('Số tiền chuyển');

            $table->foreignId('currency_id')
                ->constrained()
                ->restrictOnDelete();

            $table->text('note')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            $table->index(['company_id', 'from_account_id']);
            $table->index(['company_id', 'to_account_id']);
            $table->comment('Chuyển quỹ nội bộ giữa các tài khoản / quỹ');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_transfers');
        Schema::dropIfExists('transactions');
    }
};
