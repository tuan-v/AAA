<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nhóm 7: Kho hàng
 *
 * Bảng:
 *   - warehouses   (kho chứa hàng)
 *
 * Quy tắc: kho đã dùng thì chỉ khóa/mở (status), không xóa.
 * Unique code theo phạm vi công ty: [company_id, code].
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('code')->comment('Mã kho (unique trong công ty)');
            $table->string('name');

            // Địa chỉ kho (tỉnh/thành + xã/phường + chi tiết)
            $table->foreignId('province_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('ward_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('address_detail')->nullable()->comment('Số nhà, đường, khu vực...');

            // Tổng giá trị hàng đang tồn trong kho (auto-update khi duyệt phiếu nhập/xuất)
            $table->decimal('total_inventory_value', 18, 2)->default(0)
                ->comment('Tổng giá trị tồn kho (VND)');

            $table->enum('status', ['active', 'inactive'])->default('active')
                ->comment('active=Đang hoạt động | inactive=Đã khóa');

            $table->timestamps();

            // Code phải unique trong phạm vi công ty
            $table->unique(['company_id', 'code'], 'warehouses_company_code_unique');
            $table->index(['company_id', 'status']);
            $table->comment('Danh sách kho hàng');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
