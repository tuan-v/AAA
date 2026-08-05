<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'color')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->string('color')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('products', 'color')) {
            return;
        }

        DB::table('products')->whereNull('color')->update(['color' => '']);

        Schema::table('products', function (Blueprint $table) {
            $table->string('color')->nullable(false)->change();
        });
    }
};
