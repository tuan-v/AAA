<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nhóm 6: Danh mục dùng chung
 *
 * Bảng (theo thứ tự tạo để FK hợp lệ):
 *   - categories   (danh mục sản phẩm đa cấp)
 *   - units        (đơn vị tính)
 *   - products     (sản phẩm / hàng hoá / dịch vụ)
 *
 * Quy tắc chung (đã dùng thì khóa/mở, chưa dùng thì sửa tự do):
 *   → Cột status = 'active' / 'inactive'
 *   → Logic kiểm tra "đã dùng" xử lý ở tầng Service / Model (isUsed())
 *
 * products — bỏ cột `color` (không có trong nghiệp vụ).
 * Giữ quantity, purchase_price, sell_price (tham chiếu giá gốc / tồn nhanh).
 * Tồn kho chi tiết vẫn qua warehouse_product_stocks.
 *
 * Để thêm cột về sau: chỉ cần tạo một migration mới, gọi Schema::table('products', ...).
 * Không cần sửa file này.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── categories ─────────────────────────────────────────────────────
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Hỗ trợ đa cấp (self-referencing)
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete()
                ->comment('Danh mục cha (null = gốc)');

            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active')
                ->comment('active=Đang dùng | inactive=Đã khóa');

            $table->timestamps();

            $table->unique(['company_id', 'code'], 'categories_company_code_unique');
            $table->comment('Danh mục sản phẩm (hỗ trợ đa cấp)');
        });

        // ── units ──────────────────────────────────────────────────────────
        Schema::create('units', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name');
            $table->string('symbol')->nullable()->comment('kg, cái, hộp, thùng...');

            // Cho phép nhập số lẻ (decimal) hay chỉ số nguyên
            $table->boolean('allow_decimal')->default(false)
                ->comment('true=Cho nhập số lẻ (kg, lít...) | false=Chỉ số nguyên (cái, hộp...)');

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->unique(['company_id', 'name'], 'units_company_name_unique');
            $table->comment('Đơn vị tính sản phẩm');
        });

        // ── products ───────────────────────────────────────────────────────
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete()    // không xóa category đang có sản phẩm
                ->cascadeOnUpdate();

            $table->foreignId('unit_id')
                ->constrained('units')
                ->restrictOnDelete()    // không xóa unit đang có sản phẩm
                ->cascadeOnUpdate();

            // ── Thông tin cơ bản ──────────────────────────────────────────
            $table->string('name');
            $table->string('sku')->nullable()->comment('Mã SKU (unique trong công ty)');
            $table->string('barcode')->nullable()->comment('Mã vạch');
            $table->string('image')->nullable();
            $table->text('description')->nullable();

            // hang_hoa=Hàng hoá | vat_tu=Vật tư | dich_vu=Dịch vụ
            $table->enum('type', ['hang_hoa', 'vat_tu', 'dich_vu'])->default('hang_hoa');

            // ── Giá tham chiếu (giá thực tế ghi vào order_items) ─────────
            // Đây là giá mặc định / gợi ý, không bắt buộc dùng khi tạo đơn.
            $table->decimal('purchase_price', 18, 2)->default(0)
                ->comment('Giá mua tham chiếu (đồng tiền gốc công ty)');
            $table->decimal('sell_price', 18, 2)->default(0)
                ->comment('Giá bán tham chiếu (đồng tiền gốc công ty)');

            // ── Tồn kho nhanh ─────────────────────────────────────────────
            // Tồn kho chi tiết theo kho: xem warehouse_product_stocks
            // Cột này tổng hợp nhanh, tự động cập nhật khi duyệt phiếu nhập/xuất.
            $table->integer('quantity')->default(0)
                ->comment('Tổng tồn kho nhanh (tổng hợp từ warehouse_product_stocks)');

            $table->enum('status', ['active', 'inactive'])->default('active')
                ->comment('active=Đang kinh doanh | inactive=Ngừng kinh doanh');

            $table->timestamps();

            // SKU unique trong phạm vi công ty
            $table->unique(['company_id', 'sku'], 'products_company_sku_unique');
            $table->index(['company_id', 'status']);
            $table->comment('Danh sách sản phẩm / hàng hoá / dịch vụ');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('units');
        Schema::dropIfExists('categories');
    }
};
