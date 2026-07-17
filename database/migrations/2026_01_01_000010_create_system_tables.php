<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nhóm 13: Hệ thống & Audit
 *
 * Bảng:
 *   - activity_logs   (audit log toàn bộ hành động người dùng)
 *
 * Ghi lại mọi thao tác: create, update, delete, approve, reject, export, import...
 * Dùng để: xem lịch sử hoạt động, kiểm tra ai sửa gì, lúc nào.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // Ai thực hiện hành động
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Công ty (để lọc log theo công ty)
            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Hành động: create | update | delete | approve | reject | export | import | login | logout
            $table->string('action')->comment('Loại hành động thực hiện');

            // Đối tượng bị tác động
            $table->string('model_type')->comment('Tên Model (VD: SalesOrder, WarehouseSlip...)');
            $table->unsignedBigInteger('model_id')->comment('ID bản ghi bị tác động');

            // Dữ liệu thay đổi (chỉ ghi các field thực sự thay đổi)
            $table->json('old_values')->nullable()
                ->comment('Giá trị trước khi thay đổi');
            $table->json('new_values')->nullable()
                ->comment('Giá trị sau khi thay đổi');

            // Metadata
            $table->text('description')->nullable()
                ->comment('Mô tả tóm tắt hành động');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // Indexes để query nhanh
            $table->index(['model_type', 'model_id'], 'activity_logs_model_idx');
            $table->index('user_id');
            $table->index(['company_id', 'action']);
            $table->index('created_at');
            $table->comment('Audit log — lịch sử mọi hành động trong hệ thống');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
