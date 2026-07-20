<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->enum('status_normalized', ['active', 'inactive'])
                ->default('active')
                ->after('status');
        });

        DB::table('warehouses')->update([
            'status_normalized' => DB::raw("CASE WHEN status IN (1, '1', 'active') THEN 'active' ELSE 'inactive' END"),
        ]);

        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->renameColumn('status_normalized', 'status');
        });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->boolean('status_boolean')->default(true)->after('status');
        });

        DB::table('warehouses')->update([
            'status_boolean' => DB::raw("CASE WHEN status = 'active' THEN 1 ELSE 0 END"),
        ]);

        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->renameColumn('status_boolean', 'status');
        });
    }
};
