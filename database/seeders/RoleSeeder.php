<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::firstOrCreate([
            'name' => 'Admin', 'guard_name' => 'web'
        ], [
            'description' => 'Quản trị hệ thống'
        ]);

        Role::firstOrCreate([
            'name' => 'HR', 'guard_name' => 'web'
        ], [
            'description' => 'Nhân sự'
        ]);

        Role::firstOrCreate([
            'name' => 'Manager', 'guard_name' => 'web'
        ], [
            'description' => 'Quản lý'
        ]);

        $director = Role::where('guard_name', 'web')
            ->whereIn('name', ['Giám đốc', 'Director'])
            ->orderByRaw("CASE WHEN name = 'Giám đốc' THEN 0 ELSE 1 END")
            ->first();

        if ($director) {
            $director->update([
                'name' => 'Giám đốc',
                'description' => 'Giám đốc - toàn quyền quản trị doanh nghiệp',
            ]);
        } else {
            Role::create([
                'name' => 'Giám đốc',
                'guard_name' => 'web',
                'description' => 'Giám đốc - toàn quyền quản trị doanh nghiệp',
            ]);
        }
    }
}
