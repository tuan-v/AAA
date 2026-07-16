<?php

namespace App\Providers;

use App\Repositories\DashboardRepository;
use App\Repositories\DashboardRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Services\CurrencyService;
use App\Services\CodeGeneratorService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CurrencyService::class);

        $this->app->singleton(CodeGeneratorService::class);
        $this->app->bind(
            DashboardRepositoryInterface::class,
            DashboardRepository::class
        );
    }

    public function boot(): void
    {
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
            $this->can('user.view') ||
            $this->can('role.view') ||
            $this->can('permission.view') ||
            $this->can('auditlog.view');

        if (! $canSeeManagement) {
            return [];
        }

        $menuItems = [
            [
                'icon' => 'GridIcon',
                'name' => 'Dashboard',
                'path' => '/dashboard',
            ],
            [
                'icon' => 'GridIcon',
                'name' => 'Quản lí',
                'subItems' => array_values(array_filter([
                    $this->can('user.view') ? [
                        'name' => 'Nhân sự',
                        'path' => '/user',
                    ] : null,

                    $this->can('permission.view') ? [
                        'name' => 'Quyền',
                        'path' => '/permission',
                    ] : null,

                    $this->can('role.view') ? [
                        'name' => 'Vai trò',
                        'path' => '/role',
                    ] : null,

                    $this->can('auditlog.view') ? [
                        'name' => 'Lịch sử hoạt động',
                        'path' => '/audit-logs',
                    ] : null,
                ])),
            ],
        ];

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
                $this->can('warehouse.view') ? [
                    'icon' => 'WarehouseIcon',
                    'name' => 'Kho hàng',
                    'path' => '/warehouse',
                ] : null,

                $this->can('warehouse_product.view') ? [
                    'icon' => 'BoxIcon',
                    'name' => 'Sản phẩm',
                    'path' => '/warehouse/products',
                ] : null,

                $this->can('warehouse_slip.view') ? [
                    'icon' => 'AddOder',
                    'name' => 'Đơn hàng',
                    'path' => '/warehouse/orders',
                ] : null,

                $this->can('warehouse_slip.view') ? [
                    'icon' => 'MovetoinboxIcon',
                    'name' => 'Phiếu nhập/xuất',
                    'path' => '/warehouse/slips',
                ] : null,
            ])),

            'sale' => array_values(array_filter([
                $this->can('sale_customer.view') ? [
                    'icon' => 'UserIcon',
                    'name' => 'Khách hàng',
                    'path' => '/sale/customers',
                ] : null,

                $this->can('sale_order.view') ? [
                    'icon' => 'ShoppingCartIcon',
                    'name' => 'Đơn hàng',
                    'path' => '/sale/orders',
                ] : null,
            ])),

            'purchase' => array_values(array_filter([
                $this->can('supplier.view') ? [
                    'icon' => 'handpakageIcon',
                    'name' => 'Nhà cung cấp',
                    'path' => '/purchase/suppliers',
                ] : null,

                $this->can('purchase_product.view') ? [
                    'icon' => 'BoxIcon',
                    'name' => 'Sản phẩm',
                    'path' => '/purchase/products',
                ] : null,

                $this->can('purchase_order.view') ? [
                    'icon' => 'AddOder',
                    'name' => 'Đơn mua',
                    'path' => '/purchase/orders',
                ] : null,
            ])),

            'accountant' => array_values(array_filter([
                $this->can('currency.view') ? [
                    'icon' => 'CurrencyIcon',
                    'name' => 'Tiền tệ',
                    'path' => '/accountant/currencies',
                ] : null,

                $this->can('bank.view') ? [
                    'icon' => 'BankIcon',
                    'name' => 'Ngân hàng',
                    'path' => '/accountant/banks',
                ] : null,

                $this->can('account.view') ? [
                    'icon' => 'AccountBankIcon',
                    'name' => 'Tài khoản',
                    'path' => '/accountant/accounts',
                ] : null,

                $this->can('transaction_category.view') ? [
                    'icon' => 'ReceiptIcon',
                    'name' => 'Loại giao dịch',
                    'path' => '/accountant/transaction-categories',
                ] : null,

                $this->can('transaction.view') ? [
                    'icon' => 'transaction',
                    'name' => 'Giao dịch',
                    'path' => '/accountant/transactions',
                ] : null,

                $this->can('customer_debt.view') ? [
                    'icon' => 'UserIcon',
                    'name' => 'Công nợ khách hàng',
                    'path' => '/accountant/customer-debts',
                ] : null,

                $this->can('supplier_debt.view') ? [
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
