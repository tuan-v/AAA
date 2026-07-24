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
            DB::statement("ALTER TABLE users MODIFY status ENUM('active','inactive','blocked','pending','pending_edit','rejected_final','expired') NOT NULL DEFAULT 'pending'");
        }
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('rejection_count')->default(0)->after('rejected_at');
            $table->string('rejection_type', 30)->nullable()->after('rejection_count');
            $table->timestamp('resubmit_expires_at')->nullable()->after('rejection_type')->index();
        });
    }

    public function down(): void
    {
        DB::table('users')->whereIn('status', ['rejected_final', 'expired'])->update(['status' => 'pending']);
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rejection_count', 'rejection_type', 'resubmit_expires_at']);
        });
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY status ENUM('active','inactive','blocked','pending','pending_edit') NOT NULL DEFAULT 'pending'");
        }
    }
};
