<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Đổi hẳn enum status: ('draft','posted','cancelled') -> ('pending','approve','reject')
     *
     * Ánh xạ dữ liệu cũ:
     *   draft     -> pending
     *   posted    -> approve
     *   cancelled -> reject
     *
     * Thực hiện theo 3 bước để không mất dữ liệu:
     *  1. Mở rộng enum, thêm 3 giá trị mới ĐỒNG THỜI giữ 3 giá trị cũ
     *     (để UPDATE ở bước 2 không bị MySQL từ chối do giá trị chưa hợp lệ)
     *  2. UPDATE toàn bộ bản ghi: map giá trị cũ -> giá trị mới
     *  3. Thu gọn enum lại chỉ còn 3 giá trị mới, đổi default -> 'pending'
     */
    public function up(): void
    {
        // BƯỚC 1: mở rộng enum, giữ cả cũ lẫn mới
        DB::statement("
            ALTER TABLE transactions
            MODIFY status ENUM('draft','posted','cancelled','pending','approve','reject')
            NOT NULL DEFAULT 'draft'
        ");

        // BƯỚC 2: map dữ liệu cũ sang tên mới
        DB::table('transactions')->where('status', 'draft')->update(['status' => 'pending']);
        DB::table('transactions')->where('status', 'posted')->update(['status' => 'approve']);
        DB::table('transactions')->where('status', 'cancelled')->update(['status' => 'reject']);

        // BƯỚC 3: thu gọn enum, chỉ còn 3 giá trị mới, default = pending
        DB::statement("
            ALTER TABLE transactions
            MODIFY status ENUM('pending','approve','reject')
            NOT NULL DEFAULT 'pending'
        ");

        // Thêm cột theo dõi người duyệt / thời điểm duyệt (nếu chưa có)
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'approved_by')) {
                $table->foreignId('approved_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('created_by');
            }

            if (! Schema::hasColumn('transactions', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });

        // Backfill: giao dịch cũ giờ đang là 'approve' (trước là 'posted')
        // coi như đã "duyệt" ngay tại thời điểm tạo, vì code cũ tác động
        // số liệu ngay lúc create(). Gán approved_by/approved_at cho nhất quán.
        DB::table('transactions')
            ->where('status', 'approve')
            ->whereNull('approved_at')
            ->update([
                'approved_by' => DB::raw('created_by'),
                'approved_at' => DB::raw('created_at'),
            ]);
    }

    public function down(): void
    {
        // Mở rộng lại để map ngược
        DB::statement("
            ALTER TABLE transactions
            MODIFY status ENUM('draft','posted','cancelled','pending','approve','reject')
            NOT NULL DEFAULT 'pending'
        ");

        DB::table('transactions')->where('status', 'pending')->update(['status' => 'draft']);
        DB::table('transactions')->where('status', 'approve')->update(['status' => 'posted']);
        DB::table('transactions')->where('status', 'reject')->update(['status' => 'cancelled']);

        DB::statement("
            ALTER TABLE transactions
            MODIFY status ENUM('draft','posted','cancelled')
            NOT NULL DEFAULT 'posted'
        ");

        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }
            if (Schema::hasColumn('transactions', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
        });
    }
};
