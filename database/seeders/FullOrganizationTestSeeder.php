<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class FullOrganizationTestSeeder extends Seeder
{
    public const PASSWORD = 'Test@123456';

    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            CurrencySeeder::class,
            RoleSeeder::class,
            RolePermissionSeeder::class,
        ]);

        if (! Company::where('tax_code', '0101234567')->exists()) {
            $this->call(DemoDataSeeder::class);
        }

        $this->call([
            DepartmentDemoSeeder::class,
            PositionDemoSeeder::class,
            DepartmentEmployeeDemoSeeder::class,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $company = Company::where('tax_code', '0101234567')->firstOrFail();

        $accounts = [
            [
                'department' => 'PB-001',
                'position_code' => 'CV-101',
                'position' => 'Quản trị hệ thống',
                'role' => 'Supper Admin',
                'name' => 'Quản trị hệ thống Demo',
                'email' => 'supper.admin@demo.vn',
                'username' => 'supper_admin_demo',
                'phone' => '0902000101',
                'type' => User::TYPE_SYSTEM,
            ],
            [
                'department' => 'PB-002',
                'position_code' => 'CV-107',
                'position' => 'Điều phối nhân sự',
                'role' => 'HR',
                'name' => 'Điều phối nhân sự Demo',
                'email' => 'hr.role@demo.vn',
                'username' => 'hr_role_demo',
                'phone' => '0902000107',
                'type' => User::TYPE_USER,
            ],
            [
                'department' => 'PB-001',
                'position_code' => 'CV-108',
                'position' => 'Quản lý vận hành',
                'role' => 'Manager',
                'name' => 'Quản lý vận hành Demo',
                'email' => 'manager.role@demo.vn',
                'username' => 'manager_role_demo',
                'phone' => '0902000108',
                'type' => User::TYPE_USER,
            ],
        ];

        DB::transaction(function () use ($company, $accounts): void {
            foreach ($accounts as $account) {
                $department = Department::where('company_id', $company->id)
                    ->where('code', $account['department'])
                    ->firstOrFail();

                $position = Position::updateOrCreate(
                    ['company_id' => $company->id, 'code' => $account['position_code']],
                    [
                        'department_id' => $department->id,
                        'name' => $account['position'],
                        'description' => 'Chức vụ mẫu phục vụ kiểm thử đầy đủ luồng phân quyền.',
                        'status' => 'active',
                    ]
                );

                $user = User::updateOrCreate(
                    ['email' => $account['email']],
                    [
                        'name' => $account['name'],
                        'username' => $account['username'],
                        'phone' => $account['phone'],
                        'password' => Hash::make(self::PASSWORD),
                        'type' => $account['type'],
                        'status' => User::STATUS_ACTIVE,
                        'company_id' => $company->id,
                        'department_id' => $department->id,
                        'position_id' => $position->id,
                        'email_verified_at' => now(),
                    ]
                );

                $user->companies()->syncWithoutDetaching([$company->id]);
                $user->syncRoles([$account['role']]);
            }

            User::where('company_id', $company->id)->update([
                'password' => Hash::make(self::PASSWORD),
            ]);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
