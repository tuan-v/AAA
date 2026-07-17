<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nhóm 4: Infrastructure — Công ty, Phòng ban, Địa lý, Ngân hàng
 *
 * Bảng (theo thứ tự tạo để FK hợp lệ):
 *   - companies
 *   - departments
 *   - provinces
 *   - wards
 *   - addresses       (tham chiếu provinces, wards)
 *   - banks           (danh sách ngân hàng toàn cầu — không gắn company)
 *
 * Sau khi tạo companies, migration này cũng thêm FK company_id vào bảng users
 * (bảng users được tạo trước ở 0001 nhưng chưa thể có FK lúc đó vì companies chưa tồn tại).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── companies ──────────────────────────────────────────────────────
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tax_code')->nullable()->unique()->comment('Mã số thuế');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('logo')->nullable();

            // Chủ sở hữu công ty (FK users — users đã tồn tại)
            $table->foreignId('owner_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
            $table->comment('Danh sách công ty trong hệ thống');
        });

        // ── departments ────────────────────────────────────────────────────
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->comment('Phòng ban / bộ phận của công ty');
        });

        // ── provinces ──────────────────────────────────────────────────────
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Mã tỉnh/thành theo ĐVHC');
            $table->string('name');
            $table->timestamps();

            $table->comment('Danh sách tỉnh / thành phố');
        });

        // ── wards ──────────────────────────────────────────────────────────
        Schema::create('wards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('code')->nullable()->comment('Mã xã/phường theo ĐVHC');
            $table->string('name');
            $table->timestamps();

            $table->comment('Danh sách xã / phường / thị trấn');
        });

        // ── addresses ─────────────────────────────────────────────────────
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained();
            $table->foreignId('ward_id')->constrained();
            $table->string('address_detail')->nullable()->comment('Số nhà, tên đường...');
            $table->timestamps();
        });

        // ── banks ──────────────────────────────────────────────────────────
        // Danh sách ngân hàng là dữ liệu toàn cục (không thuộc riêng công ty nào)
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Mã ngân hàng (VCB, TCB, MBB...)');
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->comment('Danh sách ngân hàng (dữ liệu toàn cục)');
        });

        // ── Thêm FK company_id & department_id vào users ──────────────────
        // (users tạo ở 0001 nhưng chưa thể constrain vì companies chưa tồn tại)
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('company_id')
                ->references('id')->on('companies')
                ->nullOnDelete();

            $table->foreign('department_id')
                ->references('id')->on('departments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Gỡ FK users trước khi drop companies / departments
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['department_id']);
        });

        Schema::dropIfExists('banks');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('wards');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('companies');
    }
};
