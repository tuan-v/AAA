<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    private array $modules = [
        'tong_quan' => 'tổng quan trang chủ',
        'nhan_su' => 'nhân sự', 'phong_ban' => 'phòng ban', 'chuc_vu' => 'chức vụ',
        'vai_tro' => 'vai trò', 'quyen' => 'quyền',
        'nhat_ky' => 'nhật ký hoạt động', 'tai_khoan' => 'tài khoản',
        'ngan_hang' => 'ngân hàng', 'tien_te' => 'tiền tệ',
        'cong_no_khach_hang' => 'công nợ khách hàng',
        'cong_no_nha_cung_cap' => 'công nợ nhà cung cấp',
        'danh_muc_mua_hang' => 'danh mục mua hàng', 'don_mua' => 'đơn mua',
        'san_pham_mua_hang' => 'sản phẩm mua hàng', 'don_vi_mua_hang' => 'đơn vị tính mua hàng',
        'khach_hang' => 'khách hàng', 'don_ban' => 'đơn bán',
        'nha_cung_cap' => 'nhà cung cấp', 'giao_dich' => 'giao dịch',
        'loai_giao_dich' => 'loại giao dịch', 'kho' => 'kho',
        'danh_muc_kho' => 'danh mục kho', 'san_pham_kho' => 'sản phẩm kho',
        'phieu_kho' => 'phiếu kho', 'chuyen_kho' => 'chuyển kho', 'don_vi_kho' => 'đơn vị tính kho',
    ];

    private array $actions = [
        'xem' => 'Xem', 'them' => 'Thêm', 'sua' => 'Sửa', 'xoa' => 'Xóa',
        'khoa' => 'Khóa', 'mo_khoa' => 'Mở khóa', 'xem_chi_tiet' => 'Xem chi tiết',
        'duyet' => 'Duyệt', 'tu_choi' => 'Từ chối', 'huy' => 'Hủy',
        'xem_lich_su' => 'Xem lịch sử',
    ];

    public function run(): void
    {
        $permissions = collect([
            'tong_quan.xem',
            'nhan_su.xem',
            'nhan_su.them',
            'nhan_su.sua',
            'nhan_su.xoa',
            'nhan_su.duyet',
            'nhan_su.tu_choi',
            'phong_ban.them',
            'chuc_vu.them',
            'vai_tro.xem',
            'vai_tro.them',
            'vai_tro.sua',
            'vai_tro.xoa',
        ])->merge(
            collect(app('router')->getRoutes())
                ->flatMap(fn ($route) => $route->gatherMiddleware())
                ->filter(fn ($middleware) => is_string($middleware) && str_starts_with($middleware, 'permission:'))
                ->map(fn ($middleware) => explode('|', substr($middleware, strlen('permission:'))))
                ->flatten()
        )->unique()->values();

        foreach ($permissions as $permission) {
            [$module, $action] = array_pad(explode('.', $permission, 2), 2, '');
            $moduleLabel = $this->modules[$module] ?? str_replace('_', ' ', $module);
            Permission::updateOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ], [
                'description' => ($this->actions[$action] ?? ucfirst($action)).' '.$moduleLabel,
                'group' => mb_convert_case($moduleLabel, MB_CASE_TITLE, 'UTF-8'),
            ]);
        }
    }
}
