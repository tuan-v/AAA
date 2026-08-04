<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('storefront_slug')->nullable()->unique()->after('name');
            $table->boolean('storefront_enabled')->default(true)->after('storefront_slug');
        });

        DB::table('companies')->orderBy('id')->get(['id', 'name'])->each(function ($company) {
            $base = Str::slug($company->name) ?: 'cua-hang';
            $slug = $base;
            $suffix = 2;
            while (DB::table('companies')->where('storefront_slug', $slug)->exists()) {
                $slug = $base.'-'.$suffix++;
            }
            DB::table('companies')->where('id', $company->id)->update(['storefront_slug' => $slug]);
        });
    }

    public function down(): void
    {
        Schema::table('companies', fn (Blueprint $table) => $table->dropColumn([
            'storefront_slug', 'storefront_enabled',
        ]));
    }
};
