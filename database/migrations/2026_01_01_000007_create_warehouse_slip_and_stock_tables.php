<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nhóm 10: Phiếu kho & Tồn kho
 *
 * Bảng (theo thứ tự tạo để FK hợp lệ):
 *   - warehouse_slips          (phiếu nhập / xuất kho)
 *   - warehouse_slip_items     (chi tiết từng dòng sản phẩm trong phiếu)
 *   - warehouse_product_stocks (tồn kho thực tế theo [kho × sản phẩm])
 *   - warehouse_stock_movements (lịch sử biến động tồn kho)
 *
 * Luồng nghiệp vụ:
 *   Duyệt phiếu → tự động tăng/giảm warehouse_product_stocks + ghi warehouse_stock_movements
 *
 * Một PO/SO có thể có nhiều phiếu (nhập/xuất nhiều lần — partial).
 * Unique code: [company_id, code]
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── warehouse_slips ────────────────────────────────────────────────
        Schema::create('warehouse_slips', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('code')->comment('Mã phiếu (unique trong công ty)');

            // import=Phiếu nhập | export=Phiếu xuất
            $table->enum('type', ['import', 'export'])
                ->comment('import=Phiếu nhập kho | export=Phiếu xuất kho');

            // Đơn hàng nguồn (một trong hai, không bắt buộc cả hai)
            $table->foreignId('purchase_order_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('sales_order_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained()
                ->restrictOnDelete();

            // pending=Chờ duyệt | approved=Đã duyệt | rejected=Từ chối
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

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

            $table->unique(['company_id', 'code'], 'warehouse_slips_company_code_unique');
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'type']);
            $table->comment('Phiếu nhập / xuất kho');
        });

        // ── warehouse_slip_items ───────────────────────────────────────────
        Schema::create('warehouse_slip_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('slip_id')
                ->constrained('warehouse_slips')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();

            // Tham chiếu về dòng đơn hàng nguồn (để biết received_quantity)
            $table->foreignId('purchase_order_item_id')
                ->nullable()
                ->constrained('purchase_order_items')
                ->nullOnDelete();

            $table->foreignId('sales_order_item_id')
                ->nullable()
                ->constrained('sales_order_items')
                ->nullOnDelete();

            $table->decimal('quantity', 18, 2)->comment('Số lượng thực nhập/xuất');

            // Giá theo tiền tệ của đơn hàng nguồn
            $table->decimal('price', 18, 2)->default(0)
                ->comment('Đơn giá (theo tiền tệ của đơn nguồn)');

            // Giá quy đổi về đồng tiền gốc công ty
            $table->decimal('company_price', 18, 2)->default(0)
                ->comment('Đơn giá quy đổi về đồng tiền gốc (VND)');

            // Tổng giá trị dòng này (company_price * quantity)
            $table->decimal('total_value', 18, 2)->default(0)
                ->comment('Thành tiền (VND)');

            // Giá vốn (dùng cho báo cáo lợi nhuận — tính theo FIFO hoặc weighted average)
            $table->decimal('cost_price', 18, 2)->default(0)
                ->comment('Giá vốn đơn vị tại thời điểm nhập/xuất');
            $table->decimal('cost_amount', 18, 2)->default(0)
                ->comment('Tổng giá vốn (cost_price * quantity)');

            $table->timestamps();

            $table->index('slip_id');
            $table->comment('Chi tiết sản phẩm trong phiếu nhập / xuất kho');
        });

        // ── warehouse_product_stocks ───────────────────────────────────────
        Schema::create('warehouse_product_stocks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            // Tồn kho hiện tại (số lượng)
            $table->decimal('quantity', 18, 2)->default(0)
                ->comment('Số lượng tồn kho hiện tại');

            // Giá trị tồn kho (quantity × giá vốn bình quân)
            $table->decimal('stock_value', 18, 2)->default(0)
                ->comment('Giá trị tồn kho (VND)');

            $table->timestamps();

            // Mỗi [kho × sản phẩm] chỉ có 1 dòng tồn kho
            $table->unique(['warehouse_id', 'product_id'], 'wps_warehouse_product_unique');

            // Index tổng hợp để query theo công ty
            $table->index(['company_id', 'warehouse_id', 'product_id'], 'wps_company_warehouse_product_idx');
            $table->comment('Tồn kho thực tế theo [kho × sản phẩm]');
        });

        // ── warehouse_stock_movements ──────────────────────────────────────
        Schema::create('warehouse_stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('warehouse_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            // import=Nhập vào | export=Xuất ra
            $table->enum('type', ['import', 'export'])
                ->comment('import=Nhập vào kho | export=Xuất ra khỏi kho');

            $table->decimal('quantity', 18, 2)->comment('Số lượng biến động (luôn dương)');

            // Giá vốn đơn vị tại thời điểm biến động (dùng tính stock_value)
            $table->decimal('unit_price', 18, 2)->default(0)
                ->comment('Giá vốn đơn vị tại thời điểm ghi nhận');

            // Tham chiếu phiếu kho nguồn
            $table->foreignId('slip_id')
                ->constrained('warehouse_slips')
                ->cascadeOnDelete();

            $table->foreignId('slip_item_id')
                ->constrained('warehouse_slip_items')
                ->cascadeOnDelete();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['warehouse_id', 'product_id', 'created_at']);
            $table->comment('Lịch sử biến động tồn kho (audit trail)');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_stock_movements');
        Schema::dropIfExists('warehouse_product_stocks');
        Schema::dropIfExists('warehouse_slip_items');
        Schema::dropIfExists('warehouse_slips');
    }
};
