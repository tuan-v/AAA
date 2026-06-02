<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $admin = Role::findByName('Admin');

        $admin->syncPermissions([
            'user.view',
            'user.create',
            'user.update',
            'user.delete'
        ]);

        $hr = Role::findByName('HR');

        $hr->syncPermissions([
            'user.view',
            'user.create'
        ]);

        $manager = Role::findByName('Manager');

        $manager->syncPermissions([
            'user.view'
        ]);
    }
}
