<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;


use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //

        Inertia::share([
            'auth' => function () {

                $user = auth()->user();

                return [

                    'user' => $user,

                    'permissions' => $user
                        ? $user->getAllPermissions()
                        ->pluck('name')
                        ->toArray()
                        : [],

                    'menuItems' => $this->getMenuItems(),
                ];
            },
        ]);
    }
    private function getMenuItems()
    {
        $menuItems = [];

        $hosting = request()->getHost();
        switch ($hosting) {
            default:
                $menuItems = $this->webMenuItems();
                break;
        }

        return $menuItems;
    }

    private function webMenuItems()
    {
        $permissions = Auth::user()->getAllPermissions()->pluck('name')->toArray();

        $can = function ($permission) use ($permissions) {
            return in_array($permission, $permissions);
        };

        $menuItems = [];

        // DASHBOARD (ai cũng thấy)
        $menuItems[] = [
            'icon' => 'GridIcon',
            'name' => 'Dashboard',
            'path' => '/dashboard',
        ];

        // PRODUCT (ai có quyền product.view mới thấy)
        if ($can('product.view')) {
            $menuItems[] = [
                'icon' => 'GridIcon',
                'name' => 'Sản phẩm',
                'path' => '/products',
            ];
        }

        // QUẢN LÝ (chỉ hiện nếu có ít nhất 1 quyền admin system)
        if (
            $can('user.view') ||
            $can('role.view') ||
            $can('permission.view')
        ) {
            $menuItems[] = [
                'icon' => 'GridIcon',
                'name' => 'Quản lí',
                'subItems' => array_values(array_filter([
                    $can('user.view') ? [
                        'name' => 'Nhân sự',
                        'path' => '/users',
                    ] : null,

                    $can('permission.view') ? [
                        'name' => 'Quyền',
                        'path' => '/permissions',
                    ] : null,

                    $can('role.view') ? [
                        'name' => 'Vai trò',
                        'path' => '/roles',
                    ] : null,
                ])),
            ];
        }

        return [
            [
                'title' => 'Menu',
                'items' => $menuItems,
            ],
        ];
    }
}
