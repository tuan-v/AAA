<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm type 'refund' vào enum customer_debts.type.
     *
     * Lý do: khi công ty hoàn tiền cho KH (payment + customer_id),
     * cần ghi bút toán loại 'refund' để phân biệt với 'payment' (KH trả tiền).
     * MySQL không hỗ trợ ALTER COLUMN cho ENUM một cách an toàn,
     * nên dùng raw SQL MODIFY COLUMN.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("
            ALTER TABLE customer_debts
            MODIFY COLUMN type ENUM('opening', 'sale', 'payment', 'refund') NOT NULL
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Xóa 'refund' ra khỏi enum (cần đảm bảo không còn row nào dùng 'refund')
        DB::statement("
            ALTER TABLE customer_debts
            MODIFY COLUMN type ENUM('opening', 'sale', 'payment') NOT NULL
        ");
    }
};
