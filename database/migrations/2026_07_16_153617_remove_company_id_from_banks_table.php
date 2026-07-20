<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });
        Schema::table('banks', function (Blueprint $table) {
            $table->dropUnique('banks_company_code_unique');
            $table->dropColumn('company_id');
            $table->unique('code', 'banks_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            //
        });
    }
};
