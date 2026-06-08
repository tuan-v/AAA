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

        if (str_starts_with($host, 'warehouse')) {
            return $this->warehouseMenuItems();
        }

        if (str_starts_with($path, 'sale')) {
            return $this->salesMenuItems();
        }

        if (str_starts_with($path, 'purchase')) {
            return $this->purchaseMenuItems();
        }

        if (str_starts_with($path, 'accounting')) {
            return $this->accountingMenuItems();
        }

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

        // $menuItems[] = [
        //     'icon' => 'GridIcon',
        //     'name' => 'Dashboard',
        //     'path' => '/dashboard',
        // ];

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
                                'icon' => 'BoxIcon',
                                'name' => 'Kho hàng',
                                'path' => '/warehouse'
                            ],
                            [
                                'icon' => 'GridIcon',
                                'name' => 'Sản phẩm',
                                'path' => 'warehouse/products'
                            ],
                            [
                                'icon' => 'GridIcon',
                                'name' => 'Danh mục',
                                'path' => '/warehouse/categories'
                            ],
                            [
                                'icon' => 'GridIcon',
                                'name' => 'Đơn vị',
                                'path' => 'warehouse/units'
                            ],
                            [
                                'icon' => 'GridIcon',
                                'name' => 'Đơn hàng',
                                'path' => 'warehouse/orders'
                            ],
                            [
                                'icon' => 'ArrowDownIcon',
                                'name' => 'Phiếu nhập',
                                'path' => '/warehouse/imports'
                            ],
                            [
                                'icon' => 'ArrowUpIcon',
                                'name' => 'Phiếu xuất',
                                'path' => '/warehouse/exports'
                            ]
                        ];
                        break;

                    case "sale":
                        $menuItems = $this->salesMenuItems();
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
    //                 ['icon' => 'TruckIcon', 'name' => 'Nhà cung cấp', 'path' => '/suppliers'],
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
