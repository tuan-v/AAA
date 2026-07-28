<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserRoleSeeder extends Seeder
{
    public function run()
    {
        $admin = User::find(1);

        $admin?->assignRole('Supper Admin');

        $hrEmployee = User::find(2);

        $hrEmployee?->assignRole('Nhân viên nhân sự');

        $hrManager = User::find(3);

        $hrManager?->assignRole('Quản lý nhân sự');
    }
}
