<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nhóm 1: Auth & Users
 *
 * Bảng:
 *   - users
 *   - social_accounts
 *   - password_reset_tokens
 *   - sessions
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── users ──────────────────────────────────────────────────────────
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Quan hệ (company_id thêm sau khi bảng companies tồn tại — xem migration 0003)
            // Để ở đây dưới dạng bigInteger plain (chưa có FK), FK sẽ được thêm ở migration 0003
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('department_id')->nullable()->index();

            // Thông tin đăng nhập
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable()->unique();
            $table->string('password');

            // Loại tài khoản: 'user' = nhân viên thường, 'system' = tài khoản hệ thống
            $table->enum('type', ['user', 'system'])->default('user');

            // Trạng thái tài khoản
            $table->enum('status', ['active', 'inactive', 'blocked', 'pending'])
                ->default('pending')
                ->comment('active=Đang hoạt động | inactive=Không hoạt động | blocked=Bị khóa | pending=Chờ duyệt');

            // Thông tin cá nhân
            $table->string('avatar')->nullable();
            $table->text('address')->nullable();
            $table->unsignedBigInteger('creater_id')->nullable()->comment('ID người tạo tài khoản này');

            // Social / Zalo
            $table->boolean('zalo_verified')->default(false);
            $table->timestamp('zalo_verified_at')->nullable();
            $table->string('zalo_user_id')->nullable();

            // Session tracking
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();

            $table->timestamps();
            $table->rememberToken();

            $table->comment('Tài khoản nhân viên / người dùng hệ thống');
        });

        // ── social_accounts ────────────────────────────────────────────────
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider');           // google | facebook | github ...
            $table->string('provider_id');        // ID trên hệ thống bên thứ ba
            $table->string('provider_email')->nullable();
            $table->string('avatar')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_id']);
        });

        // ── password_reset_tokens ──────────────────────────────────────────
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        // ── sessions ───────────────────────────────────────────────────────
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable()->comment('IP đăng nhập');
            $table->text('user_agent')->nullable()->comment('User Agent thiết bị');
            $table->longText('payload')->comment('Token đăng nhập');
            $table->timestamp('login_at')->nullable()->comment('Thời gian đăng nhập');
            $table->time('logout_at')->nullable()->comment('Thời gian đăng xuất');
            $table->string('device_name')->nullable()->comment('Tên thiết bị');
            $table->enum('session_type', ['web', 'mobile', 'api'])->default('web');
            $table->integer('last_activity')->index()->comment('Unix timestamp hoạt động cuối');

            $table->comment('Lịch sử đăng nhập');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('social_accounts');
        Schema::dropIfExists('users');
    }
};
