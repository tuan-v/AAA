<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class DepartmentEmployeeDemoSeeder extends Seeder
{
    public const PASSWORD = 'Test@123456';

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $company = Company::where('tax_code', '0101234567')->firstOrFail();

        $profiles = [
            [
                'department' => 'PB-002', 'position_code' => 'CV-102', 'position' => 'Chuyên viên Nhân sự',
                'role' => 'Nhân viên nhân sự', 'name' => 'Nguyễn Thu Hà', 'email' => 'hr.staff@demo.vn',
                'username' => 'hr_staff_demo', 'phone' => '0902000102',
                'permissions' => ['nhan_su.xem', 'nhan_su.xem_chi_tiet'],
            ],
            [
                'department' => 'PB-003', 'position_code' => 'CV-103', 'position' => 'Nhân viên Mua hàng',
                'role' => 'Nhân viên mua hàng', 'name' => 'Trần Minh Quân', 'email' => 'purchase.staff@demo.vn',
                'username' => 'purchase_staff_demo', 'phone' => '0902000103',
                'permissions' => [
                    'nha_cung_cap.xem', 'nha_cung_cap.xem_chi_tiet', 'nha_cung_cap.them', 'nha_cung_cap.sua',
                    'danh_muc_mua_hang.xem', 'don_vi_mua_hang.xem', 'san_pham_mua_hang.xem',
                    'don_mua.xem', 'don_mua.xem_chi_tiet', 'don_mua.them', 'don_mua.sua',
                ],
            ],
            [
                'department' => 'PB-004', 'position_code' => 'CV-104', 'position' => 'Nhân viên Kho',
                'role' => 'Nhân viên kho', 'name' => 'Lê Hoàng Nam', 'email' => 'warehouse.staff@demo.vn',
                'username' => 'warehouse_staff_demo', 'phone' => '0902000104',
                'permissions' => [
                    'kho.xem', 'san_pham_kho.xem', 'phieu_kho.xem', 'phieu_kho.xem_chi_tiet',
                    'phieu_kho.them', 'phieu_kho.sua', 'chuyen_kho.xem', 'chuyen_kho.them',
                ],
            ],
            [
                'department' => 'PB-005', 'position_code' => 'CV-105', 'position' => 'Nhân viên Kinh doanh',
                'role' => 'Nhân viên bán hàng', 'name' => 'Phạm Ngọc Mai', 'email' => 'sales.staff@demo.vn',
                'username' => 'sales_staff_demo', 'phone' => '0902000105',
                'permissions' => [
                    'khach_hang.xem', 'khach_hang.them', 'khach_hang.sua',
                    'don_ban.xem', 'don_ban.xem_chi_tiet', 'don_ban.them', 'don_ban.sua',
                ],
            ],
            [
                'department' => 'PB-006', 'position_code' => 'CV-106', 'position' => 'Kế toán viên',
                'role' => 'Nhân viên kế toán', 'name' => 'Vũ Đức Anh', 'email' => 'accountant.staff@demo.vn',
                'username' => 'accountant_staff_demo', 'phone' => '0902000106',
                'permissions' => [
                    'tien_te.xem', 'ngan_hang.xem', 'tai_khoan.xem', 'loai_giao_dich.xem',
                    'giao_dich.xem', 'giao_dich.them', 'giao_dich.sua',
                    'cong_no_khach_hang.xem', 'cong_no_khach_hang.xem_chi_tiet',
                    'cong_no_nha_cung_cap.xem', 'cong_no_nha_cung_cap.xem_chi_tiet',
                ],
            ],
        ];

        DB::transaction(function () use ($company, $profiles) {
            foreach ($profiles as $profile) {
                $department = Department::where('company_id', $company->id)
                    ->where('code', $profile['department'])->firstOrFail();

                $position = Position::updateOrCreate(
                    ['company_id' => $company->id, 'code' => $profile['position_code']],
                    [
                        'department_id' => $department->id, 'name' => $profile['position'],
                        'description' => 'Chức vụ nhân viên mẫu dùng để kiểm thử phân quyền.', 'status' => 'active',
                    ]
                );

                $role = Role::updateOrCreate(
                    ['name' => $profile['role'], 'guard_name' => 'web'],
                    [
                        'company_id' => $company->id, 'type' => 'user', 'hierarchy_level' => 20,
                        'is_protected' => false, 'description' => 'Vai trò demo giới hạn theo nghiệp vụ '.$profile['position'],
                    ]
                );
                $role->syncPermissions($profile['permissions']);

                $user = User::updateOrCreate(
                    ['email' => $profile['email']],
                    [
                        'name' => $profile['name'], 'username' => $profile['username'], 'phone' => $profile['phone'],
                        'password' => Hash::make(self::PASSWORD), 'type' => User::TYPE_USER,
                        'status' => User::STATUS_ACTIVE, 'company_id' => $company->id,
                        'department_id' => $department->id, 'position_id' => $position->id,
                        'email_verified_at' => now(),
                    ]
                );
                $user->companies()->syncWithoutDetaching([$company->id]);
                $user->syncRoles([$role]);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
