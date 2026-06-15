<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Inertia::share([
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
        $host = request()->getHost();
        $path = request()->path();

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

    private function webMenuItems(): array
    {
        $menuItems = [];
        if (
            $this->can('user.view') ||
            $this->can('role.view') ||
            $this->can('permission.view')
        ) {
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
                    ])),
                ],
            ];
            if (request()->segment(1)) {
                switch (request()->segment(1)) {

                    case "warehouse":
                        $menuItems = [
                            [
                                'icon' => 'WarehouseIcon',
                                'name' => 'Kho hàng',
                                'path' => '/warehouse'
                            ],
                            [
                                'icon' => 'BoxIcon',
                                'name' => 'Sản phẩm',
                                'path' => '/warehouse/products'
                            ],
                            [
                                'icon' => 'AddOder',
                                'name' => 'Đơn hàng',
                                'path' => '/warehouse/orders'
                            ],
                            [
                                'icon' => 'MovetoinboxIcon',
                                'name' => 'Phiếu nhập/xuất',
                                'path' => '/warehouse/slips'
                            ],
                        ];
                        break;

                    case "sale":
                        $menuItems = $this->salesMenuItems();
                        break;
                    case "purchase":
                        $menuItems = [
                            [
                                'icon' => 'handpakageIcon',
                                'name' => 'Nhà cung cấp',
                                'path' => '/purchase/suppliers'
                            ],
                            [
                                'icon' => 'BoxIcon',
                                'name' => 'Sản phẩm',
                                'path' => '/purchase/products'
                            ],
                            [
                                'icon' => 'AddOder',
                                'name' => 'Đơn mua',
                                'path' => '/purchase/orders'
                            ],
                            [
                                'icon' => '',
                                'name' => 'Danh mục',
                                'path' => '/purchase/categories'
                            ],
                            [
                                'icon' => '',
                                'name' => 'Đơn vị',
                                'path' => '/purchase/units'
                            ]

                        ];
                        break;
                }
            }

            return [
                [
                    'title' => 'Menu',
                    'items' => $menuItems,
                ],
            ];
        }
        return [];
    }

    // private function warehouseMenuItems(): array
    // {
    //     return [
    //         'title' => 'QUẢN LÝ KHO',
    //         'items' => [
    //             ['icon' => 'GridIcon', 'name' => 'Sản phẩm', 'path' => '/product'],
    //             ['icon' => 'GridIcon', 'name' => 'Dashboard', 'path' => '/warehouse/dashboard'],
    //             ['icon' => 'BoxIcon', 'name' => 'Kho hàng', 'path' => '/warehouse'],
    //             ['icon' => 'ArrowDownIcon', 'name' => 'Nhập kho', 'path' => '/warehouse/import'],
    //             ['icon' => 'ArrowUpIcon', 'name' => 'Xuất kho', 'path' => '/warehouse/export'],
    //         ],
    //     ];
    // }

    // function salesMenuItems(): array
    // {
    //     return [
    //         [
    //             'title' => 'BÁN HÀNG',
    //             'items' => [
    //                 ['icon' => 'GridIcon', 'name' => 'Dashboard', 'path' => '/dashboard'],
    //                 ['icon' => 'ShoppingCartIcon', 'name' => 'Đơn hàng', 'path' => '/orders'],
    //                 ['icon' => 'UserIcon', 'name' => 'Khách hàng', 'path' => '/customers'],
    //                 ['icon' => 'ReceiptIcon', 'name' => 'Báo giá', 'path' => '/quotations'],
    //             ],
    //         ],
    //     ];
    // }

    // function purchaseMenuItems(): array
    // {
    //     return [
    //         [
    //             'title' => 'MUA HÀNG',
    //             'items' => [
    //                 ['icon' => 'GridIcon', 'name' => 'Dashboard', 'path' => '/dashboard'],
    //                 ['icon' => 'TruckIcon', 'name' => 'Nhà cung cấp', 'path' => 'puchase/suppliers'],
    //                 ['icon' => 'FileIcon', 'name' => 'Đơn mua', 'path' => '/purchase-orders'],
    //             ],
    //         ],
    //     ];
    // }

    // function accountingMenuItems(): array
    // {
    //     return [
    //         [
    //             'title' => 'KẾ TOÁN',
    //             'items' => [
    //                 ['icon' => 'GridIcon', 'name' => 'Dashboard', 'path' => '/dashboard'],
    //                 ['icon' => 'WalletIcon', 'name' => 'Thu chi', 'path' => '/cash-flow'],
    //                 ['icon' => 'ChartIcon', 'name' => 'Báo cáo', 'path' => '/reports'],
    //             ],
    //         ],
    // ];
    // }
}
