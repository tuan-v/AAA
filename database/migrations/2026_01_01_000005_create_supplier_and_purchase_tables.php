<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nhóm 8: Nhà cung cấp & Đơn mua hàng
 *
 * Bảng (theo thứ tự tạo để FK hợp lệ):
 *   - suppliers              (nhà cung cấp)
 *   - purchase_orders        (đơn mua hàng — PO)
 *   - purchase_order_items   (chi tiết dòng sản phẩm trong PO)
 *
 * Luồng nghiệp vụ:
 *   PO pending → approved → sinh phiếu nhập kho → tăng tồn + công nợ NCC
 *
 * Unique code: [company_id, code] cho cả suppliers và purchase_orders.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── suppliers ──────────────────────────────────────────────────────
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('code')->comment('Mã NCC (unique trong công ty)');
            $table->string('name');

            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Đơn vị tiền tệ giao dịch với NCC này
            $table->foreignId('currency_id')
                ->constrained()
                ->restrictOnDelete();

            // Địa chỉ NCC
            $table->foreignId('province_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ward_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address_detail')->nullable();

            // Công nợ tổng hợp (auto-update)
            $table->decimal('opening_debt', 18, 2)->default(0)
                ->comment('Công nợ đầu kỳ');
            $table->decimal('total_debts', 18, 2)->default(0)
                ->comment('Tổng công nợ phải trả NCC hiện tại');
            $table->decimal('total_advance', 18, 2)->default(0)
                ->comment('Tổng đã ứng/trả trước cho NCC');

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->unique(['company_id', 'code'], 'suppliers_company_code_unique');
            $table->index(['company_id', 'status']);
            $table->comment('Danh sách nhà cung cấp');
        });

        // ── purchase_orders ────────────────────────────────────────────────
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('code')->comment('Mã PO (unique trong công ty)');

            $table->foreignId('supplier_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('currency_id')
                ->constrained()
                ->restrictOnDelete();

            // Tỷ giá tại thời điểm tạo đơn (để quy đổi sang VND)
            $table->decimal('exchange_rate', 20, 6)->default(1)
                ->comment('Tỷ giá tại thời điểm tạo PO');

            // Tổng tiền (tự tính từ items)
            $table->decimal('total_amount', 18, 2)->default(0)
                ->comment('Tổng tiền theo tiền tệ của đơn');
            $table->decimal('total_amount_in_company_currency', 18, 2)->default(0)
                ->comment('Tổng tiền quy đổi về đồng tiền gốc công ty');

            $table->date('expected_received_date')->nullable()
                ->comment('Ngày dự kiến nhận hàng');

            // pending=Chờ xử lý | approved=Đã duyệt | partial=Nhập một phần
            // completed=Đã nhập đủ | cancelled=Đã hủy
            $table->enum('status', ['pending', 'approved', 'partial', 'completed', 'cancelled'])
                ->default('pending');

            $table->text('note')->nullable();

            // Người tạo & duyệt
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'code'], 'purchase_orders_company_code_unique');
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'supplier_id']);
            $table->comment('Đơn mua hàng (Purchase Order)');
        });

        // ── purchase_order_items ───────────────────────────────────────────
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();

            $table->decimal('quantity', 18, 2)->comment('Số lượng đặt mua');

            // Giá theo tiền tệ của đơn hàng
            $table->decimal('price', 18, 2)->default(0)
                ->comment('Đơn giá (theo tiền tệ của PO)');

            // Giá quy đổi về đồng tiền gốc công ty
            $table->decimal('price_in_company_currency', 18, 2)->default(0)
                ->comment('Đơn giá quy đổi về đồng tiền gốc (VND)');

            // Thành tiền (price * quantity)
            $table->decimal('amount', 18, 2)->default(0)
                ->comment('Thành tiền (theo tiền tệ của PO)');

            // Theo dõi tiến độ nhập kho
            $table->decimal('received_quantity', 18, 2)->default(0)
                ->comment('Số lượng đã nhập kho thực tế');

            $table->timestamps();

            $table->index('purchase_order_id');
            $table->comment('Chi tiết sản phẩm trong đơn mua hàng');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
    }
};
