<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserRoleSeeder extends Seeder
{
    public function run()
    {
        $admin = User::find(1);

        $admin->assignRole('Admin');

        $hr = User::find(2);

        $hr->assignRole('HR');

        $manager = User::find(3);

        $manager->assignRole('Manager');
    }
}
