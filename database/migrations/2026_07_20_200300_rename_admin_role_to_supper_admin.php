<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')
            ->where('guard_name', 'web')
            ->whereIn('name', ['Admin', 'Super Admin'])
            ->update([
                'name' => 'Supper Admin',
                'description' => 'Quản trị cao nhất hệ thống',
                'type' => 'system',
                'company_id' => null,
            ]);
    }

    public function down(): void
    {
        DB::table('roles')
            ->where('guard_name', 'web')
            ->where('name', 'Supper Admin')
            ->update(['name' => 'Admin']);
    }
};
