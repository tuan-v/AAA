<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $admin = Role::findByName('Admin');

        $admin->syncPermissions(Permission::all());

        $director = Role::findByName('Giám đốc');
        $director->syncPermissions(Permission::all());

        $hr = Role::findByName('HR');

        $hr->syncPermissions([
            'nhan_su.xem',
            'nhan_su.them'
        ]);

        $manager = Role::findByName('Manager');

        $manager->syncPermissions([
            'nhan_su.xem'
        ]);
    }
}
