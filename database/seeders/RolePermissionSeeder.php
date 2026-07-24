<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = Role::findByName('Supper Admin');

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

        $employeePermissions = [
            'Nhân viên nhân sự' => ['nhan_su.xem', 'nhan_su.xem_chi_tiet'],
            'Nhân viên mua hàng' => [
                'nha_cung_cap.xem', 'nha_cung_cap.xem_chi_tiet', 'nha_cung_cap.them', 'nha_cung_cap.sua',
                'danh_muc_mua_hang.xem', 'don_vi_mua_hang.xem',
                'san_pham_mua_hang.xem', 'san_pham_mua_hang.them',
                'don_mua.xem', 'don_mua.xem_chi_tiet', 'don_mua.them', 'don_mua.sua',
            ],
            'Nhân viên kho' => [
                'kho.xem', 'san_pham_kho.xem', 'phieu_kho.xem', 'phieu_kho.xem_chi_tiet',
                'phieu_kho.them', 'phieu_kho.sua', 'chuyen_kho.xem', 'chuyen_kho.them',
            ],
            'Nhân viên bán hàng' => [
                'khach_hang.xem', 'khach_hang.them', 'khach_hang.sua',
                'don_ban.xem', 'don_ban.xem_chi_tiet', 'don_ban.them', 'don_ban.sua',
            ],
            'Nhân viên kế toán' => [
                'tien_te.xem', 'ngan_hang.xem', 'tai_khoan.xem', 'loai_giao_dich.xem',
                'giao_dich.xem', 'giao_dich.them', 'giao_dich.sua',
                'cong_no_khach_hang.xem', 'cong_no_khach_hang.xem_chi_tiet',
                'cong_no_nha_cung_cap.xem', 'cong_no_nha_cung_cap.xem_chi_tiet',
            ],
        ];

        foreach ($employeePermissions as $roleName => $permissions) {
            Role::findByName($roleName)->syncPermissions($permissions);
        }

        $managerModules = [
            'Quản lý nhân sự' => ['nhan_su', 'phong_ban', 'chuc_vu', 'vai_tro', 'quyen', 'nhat_ky'],
            'Quản lý mua hàng' => ['nha_cung_cap', 'danh_muc_mua_hang', 'don_vi_mua_hang', 'san_pham_mua_hang', 'don_mua'],
            'Quản lý kho' => ['kho', 'danh_muc_kho', 'don_vi_kho', 'san_pham_kho', 'phieu_kho', 'chuyen_kho'],
            'Quản lý bán hàng' => ['khach_hang', 'don_ban'],
            'Quản lý kế toán' => ['tien_te', 'ngan_hang', 'tai_khoan', 'loai_giao_dich', 'giao_dich', 'cong_no_khach_hang', 'cong_no_nha_cung_cap'],
        ];

        foreach ($managerModules as $roleName => $modules) {
            $permissions = Permission::query()
                ->where(function ($query) use ($modules) {
                    foreach ($modules as $module) {
                        $query->orWhere('name', 'like', $module.'.%');
                    }
                })
                ->pluck('name')
                ->reject(fn ($permission) => $roleName === 'Quản lý nhân sự'
                    && in_array($permission, ['nhan_su.duyet', 'nhan_su.tu_choi'], true));

            Role::findByName($roleName)->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
