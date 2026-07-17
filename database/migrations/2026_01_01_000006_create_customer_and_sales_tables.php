<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nhóm 9: Khách hàng & Đơn bán hàng
 *
 * Bảng (theo thứ tự tạo để FK hợp lệ):
 *   - customers            (khách hàng)
 *   - sales_orders         (đơn bán hàng — SO)
 *   - sales_order_items    (chi tiết dòng sản phẩm trong SO)
 *
 * Luồng nghiệp vụ:
 *   SO pending → approved → sinh phiếu xuất kho → giảm tồn + công nợ KH
 *
 * Unique:
 *   customers:    [company_id, code], [company_id, name]
 *   sales_orders: [company_id, code]
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── customers ──────────────────────────────────────────────────────
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('code')->comment('Mã KH (auto-generate, unique trong công ty)');
            $table->string('name');

            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // Đơn vị tiền tệ giao dịch với KH này
            $table->foreignId('currency_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Địa chỉ giao hàng mặc định
            $table->foreignId('province_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ward_id')->nullable()->constrained()->nullOnDelete();
            $table->text('address_detail')->nullable();

            // Công nợ tổng hợp (auto-update)
            $table->decimal('opening_debt', 18, 2)->default(0)
                ->comment('Công nợ đầu kỳ (nhập tay khi khai báo ban đầu)');
            $table->decimal('total_debts', 18, 2)->default(0)
                ->comment('Tổng công nợ phải thu KH hiện tại');
            $table->decimal('total_advance', 18, 2)->default(0)
                ->comment('Tổng KH đã ứng/thanh toán trước');

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->unique(['company_id', 'code'], 'customers_company_code_unique');
            $table->unique(['company_id', 'name'], 'customers_company_name_unique');
            $table->index(['company_id', 'status']);
            $table->comment('Danh sách khách hàng');
        });

        // ── sales_orders ───────────────────────────────────────────────────
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('code')->comment('Mã SO (unique trong công ty)');

            $table->foreignId('customer_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('currency_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Tỷ giá tại thời điểm tạo đơn
            $table->decimal('exchange_rate', 20, 6)->default(1)
                ->comment('Tỷ giá tại thời điểm tạo SO');

            // Địa chỉ giao hàng (có thể khác địa chỉ mặc định của KH)
            $table->foreignId('province_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ward_id')->nullable()->constrained()->nullOnDelete();
            $table->text('address_detail')->nullable();

            $table->date('expected_delivery_date')->nullable()
                ->comment('Ngày dự kiến giao hàng');

            $table->text('note')->nullable();

            // Tổng tiền (tự tính từ items)
            $table->decimal('subtotal', 18, 2)->default(0)
                ->comment('Tổng tiền trước thuế');
            $table->decimal('vat_amount', 18, 2)->default(0)
                ->comment('Tiền VAT');
            $table->decimal('total_amount', 18, 2)->default(0)
                ->comment('Tổng tiền sau thuế (theo tiền tệ của SO)');
            $table->decimal('total_amount_in_company_currency', 18, 2)->default(0)
                ->comment('Tổng tiền quy đổi về đồng tiền gốc (VND)');

            // pending=Chờ xử lý | approved=Đã duyệt | cancelled=Đã hủy
            $table->enum('status', ['pending', 'approved', 'cancelled'])->default('pending');

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

            $table->unique(['company_id', 'code'], 'sales_orders_company_code_unique');
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'customer_id']);
            $table->comment('Đơn bán hàng (Sales Order)');
        });

        // ── sales_order_items ──────────────────────────────────────────────
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sales_order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();

            $table->decimal('quantity', 18, 2)->comment('Số lượng bán');

            // Giá theo tiền tệ của đơn hàng
            $table->decimal('unit_price', 18, 2)->default(0)
                ->comment('Đơn giá (theo tiền tệ của SO)');

            // Giá quy đổi về đồng tiền gốc công ty
            $table->decimal('unit_price_in_company_currency', 18, 2)->default(0)
                ->comment('Đơn giá quy đổi về đồng tiền gốc (VND)');

            // VAT áp dụng cho dòng này (%)
            $table->decimal('vat_percent', 5, 2)->default(0)
                ->comment('% VAT áp dụng cho sản phẩm này');

            // Thành tiền (unit_price * quantity, chưa gồm VAT)
            $table->decimal('amount', 18, 2)->default(0)
                ->comment('Thành tiền (theo tiền tệ của SO, chưa gồm VAT)');

            $table->timestamps();

            $table->index('sales_order_id');
            $table->comment('Chi tiết sản phẩm trong đơn bán hàng');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
        Schema::dropIfExists('customers');
    }
};
