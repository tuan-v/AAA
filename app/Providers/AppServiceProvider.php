<?php

namespace App\Providers;

use App\Repositories\DashboardRepository;
use App\Repositories\DashboardRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Services\CurrencyService;
use App\Services\CodeGeneratorService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->useLangPath(base_path('lang'));

        $this->app->singleton(CurrencyService::class);

        $this->app->singleton(CodeGeneratorService::class);
        $this->app->bind(
            DashboardRepositoryInterface::class,
            DashboardRepository::class
        );
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        Inertia::share([
            'routeName' => fn() => Route::currentRouteName(),
            'auth' => function () {

                $user = auth()->user();

                return [
                    'user' => $user,
                    'permissions' => $user
                        ? $user->getAllPermissions()->pluck('name')->toArray()
                        : [],
                    'menuItems' => $this->getMenuItems(),
                ];
            },
        ]);
    }

    private function getMenuItems(): array
    {
        return $this->webMenuItems();
    }

    private function userPermissions(): array
    {
        return auth()->user()
            ? auth()->user()->getAllPermissions()->pluck('name')->toArray()
            : [];
    }

    private function can(string $permission): bool
    {
        return in_array($permission, $this->userPermissions());
    }

    /**
     * QUAN TRỌNG: menu theo từng module (Kho/Bán hàng/Mua hàng/Kế toán) được xây dựng
     * ĐỘC LẬP với menu "Quản lý" bên dưới. Trước đây toàn bộ nằm chung 1 điều kiện,
     * khiến user không có quyền quản lý (user/role/permission/auditlog) thì các module
     * khác cũng KHÔNG hiện được menu, dù họ có đủ quyền xem module đó.
     */
    private function webMenuItems(): array
    {
        $segment = request()->segment(1);

        // Nếu đang ở trong 1 module cụ thể (warehouse/sale/purchase/accountant)
        // thì chỉ trả về menu của module đó, mỗi item đều được lọc theo can().
        if ($segment && in_array($segment, ['warehouse', 'sale', 'purchase', 'accountant'])) {
            $moduleMenu = $this->moduleMenuItems($segment);

            if (empty($moduleMenu)) {
                return [];
            }

            return [
                [
                    'title' => 'Menu',
                    'items' => $moduleMenu,
                ],
            ];
        }

        // Ngoài các module trên (vd /dashboard, /user, /role...) -> menu Quản lý
        return $this->managementMenuItems();
    }

    private function managementMenuItems(): array
    {
        $canSeeManagement =
            $this->can('nhan_su.xem') ||
            $this->can('vai_tro.xem') ||
            $this->can('quyen.xem') ||
            $this->can('nhat_ky.xem');

        $canSeeOverview = $this->can('tong_quan.xem');

        if (! $canSeeManagement && ! $canSeeOverview) {
            return [];
        }

        $menuItems = array_values(array_filter([
            $canSeeOverview ? [
                'icon' => 'GridIcon',
                'name' => 'Tổng quan',
                'path' => '/dashboard',
            ] : null,
            $canSeeManagement ? [
                'icon' => 'GridIcon',
                'name' => 'Quản lý',
                'subItems' => array_values(array_filter([
                    $this->can('nhan_su.xem') ? [
                        'name' => 'Nhân sự',
                        'icon' => 'UserGroupIcon',
                        'path' => '/user',
                    ] : null,

                    $this->can('nhan_su.xem') ? [
                        'name' => 'Phòng ban',
                        'icon' => 'CompanyIcon',
                        'path' => '/departments',
                    ] : null,

                    $this->can('nhan_su.xem') ? [
                        'name' => 'Chức vụ',
                        'icon' => 'UserPosition',
                        'path' => '/positions',
                    ] : null,

                    $this->can('quyen.xem') ? [
                        'name' => 'Quyền',
                        'icon' => 'PersonIcon',
                        'path' => '/permission',
                    ] : null,

                    $this->can('vai_tro.xem') ? [
                        'name' => 'Vai trò',
                        'icon' => 'UserPosition',
                        'path' => '/role',
                    ] : null,

                    $this->can('nhat_ky.xem') ? [
                        'name' => 'Lịch sử hoạt động',
                        'icon' => 'LockClock',
                        'path' => '/audit-logs',
                    ] : null,
                ])),
            ] : null,
        ]));

        return [
            [
                'title' => 'Menu',
                'items' => $menuItems,
            ],
        ];
    }

    private function moduleMenuItems(string $segment): array
    {
        return match ($segment) {
            'warehouse' => array_values(array_filter([
                ($this->can('kho.xem') || $this->can('san_pham_kho.xem') || $this->can('phieu_kho.xem') || $this->can('chuyen_kho.xem'))
                    ? ['icon' => 'GridIcon', 'name' => 'Tổng quan', 'path' => '/warehouse'] : null,
                $this->can('kho.xem') ? [
                    'icon' => 'WarehouseIcon',
                    'name' => 'Kho hàng',
                    'path' => '/warehouse/list',
                ] : null,

                $this->can('san_pham_kho.xem') ? [
                    'icon' => 'BoxIcon',
                    'name' => 'Sản phẩm',
                    'path' => '/warehouse/products',
                ] : null,

                $this->can('phieu_kho.xem') ? [
                    'icon' => 'AddOder',
                    'name' => 'Đơn hàng',
                    'path' => '/warehouse/orders',
                ] : null,

                $this->can('chuyen_kho.xem') ? [
                    'icon' => 'TransactionIcon',
                    'name' => 'Chuyển kho',
                    'path' => '/warehouse/transfers',
                ] : null,

                $this->can('kho.xem') ? [
                    'icon' => 'ListCheckIcon',
                    'name' => 'Sổ biến động tồn',
                    'path' => '/warehouse/inventory-movements',
                ] : null,

                $this->can('phieu_kho.xem') ? [
                    'icon' => 'MovetoinboxIcon',
                    'name' => 'Phiếu nhập/xuất',
                    'path' => '/warehouse/slips',
                ] : null,
            ])),

            'sale' => array_values(array_filter([
                ($this->can('khach_hang.xem') || $this->can('don_ban.xem'))
                    ? ['icon' => 'GridIcon', 'name' => 'Tổng quan', 'path' => '/sale'] : null,
                $this->can('khach_hang.xem') ? [
                    'icon' => 'UserIcon',
                    'name' => 'Khách hàng',
                    'path' => '/sale/customers',
                ] : null,

                $this->can('don_ban.xem') ? [
                    'icon' => 'ShoppingCartIcon',
                    'name' => 'Đơn hàng',
                    'path' => '/sale/orders',
                ] : null,
            ])),

            'purchase' => array_values(array_filter([
                ($this->can('nha_cung_cap.xem') || $this->can('san_pham_mua_hang.xem') || $this->can('don_mua.xem'))
                    ? ['icon' => 'GridIcon', 'name' => 'Tổng quan', 'path' => '/purchase'] : null,
                $this->can('nha_cung_cap.xem') ? [
                    'icon' => 'handpakageIcon',
                    'name' => 'Nhà cung cấp',
                    'path' => '/purchase/suppliers',
                ] : null,

                $this->can('san_pham_mua_hang.xem') ? [
                    'icon' => 'BoxIcon',
                    'name' => 'Sản phẩm',
                    'path' => '/purchase/products',
                ] : null,

                $this->can('don_mua.xem') ? [
                    'icon' => 'AddOder',
                    'name' => 'Đơn mua',
                    'path' => '/purchase/orders',
                ] : null,
            ])),

            'accountant' => array_values(array_filter([
                ($this->can('tien_te.xem') || $this->can('ngan_hang.xem') || $this->can('tai_khoan.xem') || $this->can('giao_dich.xem') || $this->can('cong_no_khach_hang.xem') || $this->can('cong_no_nha_cung_cap.xem'))
                    ? ['icon' => 'GridIcon', 'name' => 'Tổng quan', 'path' => '/accountant'] : null,
                $this->can('tien_te.xem') ? [
                    'icon' => 'CurrencyIcon',
                    'name' => 'Tiền tệ',
                    'path' => '/accountant/currencies',
                ] : null,

                $this->can('ngan_hang.xem') ? [
                    'icon' => 'BankIcon',
                    'name' => 'Ngân hàng',
                    'path' => '/accountant/banks',
                ] : null,

                $this->can('tai_khoan.xem') ? [
                    'icon' => 'AccountBankIcon',
                    'name' => 'Tài khoản',
                    'path' => '/accountant/accounts',
                ] : null,

                $this->can('loai_giao_dich.xem') ? [
                    'icon' => 'ReceiptIcon',
                    'name' => 'Loại giao dịch',
                    'path' => '/accountant/transaction-categories',
                ] : null,

                $this->can('giao_dich.xem') ? [
                    'icon' => 'TransactionIcon',
                    'name' => 'Giao dịch',
                    'path' => '/accountant/transactions',
                ] : null,

                $this->can('giao_dich.xem') ? [
                    'icon' => 'ListCheckIcon',
                    'name' => 'Lịch sử giao dịch',
                    'path' => '/accountant/account-ledgers',
                ] : null,

                $this->can('giao_dich.xem') ? [
                    'icon' => 'BarChartIcon',
                    'name' => 'Báo cáo lãi lỗ',
                    'path' => '/accountant/profit-loss-report',
                ] : null,

                $this->can('cong_no_khach_hang.xem') ? [
                    'icon' => 'UserIcon',
                    'name' => 'Công nợ khách hàng',
                    'path' => '/accountant/customer-debts',
                ] : null,

                $this->can('cong_no_nha_cung_cap.xem') ? [
                    'icon' => 'TruckIcon',
                    'name' => 'Công nợ nhà cung cấp',
                    'path' => '/accountant/supplier-debts',
                ] : null,

                // "Báo cáo" chưa có permission tương ứng trong routes/api.php hiện tại,
                // tạm thời không gate quyền (luôn hiện) -> cần bạn xác nhận tên quyền thật
            ])),

            default => [],
        };
    }
}
