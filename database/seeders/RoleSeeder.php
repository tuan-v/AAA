<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::updateOrCreate([
            'name' => 'Supper Admin', 'guard_name' => 'web'
        ], [
            'description' => 'Quản trị cao nhất hệ thống',
            'company_id' => null,
            'type' => 'system',
            'hierarchy_level' => 100,
            'is_protected' => false,
        ]);

        Role::updateOrCreate([
            'name' => 'HR', 'guard_name' => 'web'
        ], [
            'description' => 'Nhân sự',
            'company_id' => null,
            'type' => 'system',
            'hierarchy_level' => 30,
            'is_protected' => false,
        ]);

        Role::updateOrCreate([
            'name' => 'Manager', 'guard_name' => 'web'
        ], [
            'description' => 'Quản lý',
            'company_id' => null,
            'type' => 'system',
            'hierarchy_level' => 40,
            'is_protected' => false,
        ]);

        $director = Role::where('guard_name', 'web')
            ->whereIn('name', ['Giám đốc', 'Director'])
            ->orderByRaw("CASE WHEN name = 'Giám đốc' THEN 0 ELSE 1 END")
            ->first();

        if ($director) {
            $director->update([
                'name' => 'Giám đốc',
                'description' => 'Giám đốc - toàn quyền quản trị doanh nghiệp',
                'company_id' => null,
                'type' => 'system',
                'hierarchy_level' => 90,
                'is_protected' => false,
            ]);
        } else {
            Role::create([
                'name' => 'Giám đốc',
                'guard_name' => 'web',
                'description' => 'Giám đốc - toàn quyền quản trị doanh nghiệp',
                'company_id' => null,
                'type' => 'system',
                'hierarchy_level' => 90,
                'is_protected' => false,
            ]);
        }
    }
}
