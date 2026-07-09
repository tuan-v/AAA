<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaction_categories', function (Blueprint $table) {

            $table->tinyInteger('status')
                ->default(1)
                ->comment('0: inactive, 1: active')
                ->after('description');

        });

        // chuyển dữ liệu cũ
        DB::statement("
            UPDATE transaction_categories 
            SET status = CASE 
                WHEN is_active = 1 THEN 1
                ELSE 0
            END
        ");

        Schema::table('transaction_categories', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_categories', function (Blueprint $table) {

            $table->boolean('is_active')
                ->default(true)
                ->after('description');

        });

        DB::statement("
            UPDATE transaction_categories 
            SET is_active = CASE 
                WHEN status = 1 THEN 1
                ELSE 0
            END
        ");

        Schema::table('transaction_categories', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
