<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable()->after('read_at');
            $table->index(['customer_account_id', 'delivered_at']);
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['customer_account_id', 'delivered_at']);
            $table->dropColumn('delivered_at');
        });
    }
};
