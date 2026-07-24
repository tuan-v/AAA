<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY status ENUM('active','inactive','blocked','pending','pending_edit') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('status');
            $table->foreignId('rejected_by')->nullable()->after('rejection_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropColumn(['rejection_reason', 'rejected_at']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::table('users')->where('status', 'pending_edit')->update(['status' => 'pending']);
            DB::statement("ALTER TABLE users MODIFY status ENUM('active','inactive','blocked','pending') NOT NULL DEFAULT 'pending'");
        }
    }
};
