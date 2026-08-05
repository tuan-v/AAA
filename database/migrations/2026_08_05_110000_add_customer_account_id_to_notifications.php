<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('customer_account_id')->nullable()->after('user_id')
                ->constrained('customer_accounts')->cascadeOnDelete();
            $table->index(['customer_account_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['customer_account_id', 'read_at']);
            $table->dropConstrainedForeignId('customer_account_id');
        });
    }
};
