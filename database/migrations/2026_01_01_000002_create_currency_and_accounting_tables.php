<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nhóm 5: Tiền tệ & Kế toán nền
 *
 * Bảng (theo thứ tự tạo để FK hợp lệ):
 *   - currencies             (danh sách đơn vị tiền tệ)
 *   - currency_rates         (lịch sử tỷ giá)
 *   - transaction_categories (loại giao dịch)
 *   - accounts               (tài khoản ngân hàng / quỹ tiền mặt)
 *   - account_ledgers        (sổ cái từng tài khoản)
 *
 * currencies.fillable (từ model):
 *   company_id, code, name, symbol, exchange_rate, is_active
 *
 * Lưu ý: currencies là danh mục toàn hệ thống (không bắt buộc gắn company),
 *        nhưng model hiện có company_id (gắn theo context công ty hiện tại),
 *        nên để nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── currencies ─────────────────────────────────────────────────────
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();

            // Nullable: currency có thể là dữ liệu toàn cục (seed) hoặc riêng theo công ty
            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('code', 10)->comment('Mã ISO: VND, USD, EUR...');
            $table->string('name')->comment('Tên đầy đủ: Việt Nam Đồng...');
            $table->string('symbol', 10)->nullable()->comment('Ký hiệu: ₫, $, €...');

            // Tỷ giá hiện tại so với đồng tiền gốc của công ty (VND)
            // Lịch sử thay đổi lưu ở currency_rates
            $table->decimal('exchange_rate', 20, 6)->default(1)
                ->comment('Tỷ giá hiện tại (quy về đồng tiền gốc)');

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Mỗi công ty chỉ có 1 currency có cùng code (hoặc code là unique toàn cục nếu global)
            $table->unique(['company_id', 'code'], 'currencies_company_code_unique');
            $table->comment('Danh sách đơn vị tiền tệ');
        });

        // ── currency_rates ─────────────────────────────────────────────────
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('currency_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('rate', 20, 6)->comment('Tỷ giá tại thời điểm effective_date');
            $table->date('effective_date')->comment('Ngày áp dụng tỷ giá');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['currency_id', 'effective_date']);
            $table->comment('Lịch sử thay đổi tỷ giá');
        });

        // ── transaction_categories ─────────────────────────────────────────
        Schema::create('transaction_categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('code');
            $table->string('name');

            // income = Thu vào | expense = Chi ra | transfer = Chuyển khoản nội bộ
            $table->enum('type', ['income', 'expense', 'transfer'])
                ->comment('income=Thu | expense=Chi | transfer=Chuyển nội bộ');

            $table->text('description')->nullable();

            // active/inactive thay vì boolean để nhất quán với các bảng khác
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->unique(['company_id', 'code'], 'transaction_categories_company_code_unique');
            $table->comment('Loại giao dịch thu/chi (dùng để phân loại báo cáo)');
        });

        // ── accounts ───────────────────────────────────────────────────────
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('code');
            $table->string('name');

            // cash=Tiền mặt | bank=Ngân hàng | ewallet=Ví điện tử | other=Khác
            $table->enum('type', ['cash', 'bank', 'ewallet', 'other'])
                ->comment('cash=Tiền mặt | bank=Ngân hàng | ewallet=Ví điện tử | other=Khác');

            $table->foreignId('currency_id')
                ->constrained();

            $table->decimal('opening_balance', 20, 2)->default(0)
                ->comment('Số dư đầu kỳ');
            $table->decimal('current_balance', 20, 2)->default(0)
                ->comment('Số dư hiện tại (tự động cập nhật sau mỗi giao dịch)');

            // Thông tin ngân hàng (chỉ khi type = 'bank')
            $table->foreignId('bank_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('bank_account_no')->nullable()->comment('Số tài khoản ngân hàng');

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code'], 'accounts_company_code_unique');
            $table->comment('Tài khoản ngân hàng / quỹ tiền mặt của công ty');
        });

        // ── account_ledgers ────────────────────────────────────────────────
        Schema::create('account_ledgers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('account_id')
                ->constrained()
                ->cascadeOnDelete();

            // Giao dịch nguồn sinh ra dòng sổ cái này
            $table->foreignId('transaction_id')
                ->nullable()         // nullable để tạo bút toán khai trương (opening balance)
                ->constrained()
                ->nullOnDelete();

            $table->dateTime('ledger_date')->comment('Ngày ghi sổ');

            $table->decimal('debit', 20, 2)->default(0)->comment('Tiền vào');
            $table->decimal('credit', 20, 2)->default(0)->comment('Tiền ra');
            $table->decimal('balance_after', 20, 2)->comment('Số dư sau giao dịch');

            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'ledger_date']);
            $table->comment('Sổ cái từng tài khoản (chi tiết từng dòng thu/chi)');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_ledgers');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('transaction_categories');
        Schema::dropIfExists('currency_rates');
        Schema::dropIfExists('currencies');
    }
};
