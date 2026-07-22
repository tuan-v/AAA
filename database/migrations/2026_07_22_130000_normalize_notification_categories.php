<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notifications')) return;

        $mapping = [
            'user' => 'management',
            'general' => 'system',
            'order' => 'accountant',
            'purchase_order' => 'purchase',
            'sale_order' => 'sale',
            'import_warehouse' => 'warehouse',
            'export_warehouse' => 'warehouse',
        ];

        foreach ($mapping as $from => $to) {
            DB::table('notifications')->where('category', $from)->update(['category' => $to]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('notifications')) return;

        DB::table('notifications')->where('category', 'management')->update(['category' => 'user']);
        DB::table('notifications')->where('category', 'accountant')->update(['category' => 'order']);
        DB::table('notifications')->where('category', 'purchase')->update(['category' => 'purchase_order']);
        DB::table('notifications')->where('category', 'sale')->update(['category' => 'sale_order']);
        DB::table('notifications')->where('category', 'warehouse')->update(['category' => 'import_warehouse']);
        DB::table('notifications')->where('category', 'system')->update(['category' => 'general']);
    }
};
