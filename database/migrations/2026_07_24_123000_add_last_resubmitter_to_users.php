<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('last_resubmitted_by')
                ->nullable()
                ->after('resubmit_expires_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('last_resubmitted_at')->nullable()->after('last_resubmitted_by');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('last_resubmitted_by');
            $table->dropColumn('last_resubmitted_at');
        });
    }
};
