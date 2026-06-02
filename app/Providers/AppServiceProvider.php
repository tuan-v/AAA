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
        $menuItems = [
            [
                'icon' => 'GridIcon',
                'name' => 'Dashboard',
                'path' => '/dashboard',
            ],
            [
                'icon' => 'GridIcon',
                'name' => 'Product',
                'path' => '/index',
            ],
            [
                'icon' => 'GridIcon',
                'name' => 'Quản lí',
                'subItems' => [
                    [
                        'name' => 'Nhân sự',
                        'path' => '/user',
                    ],
                    [
                        'name' => 'Quyền',
                        'path' => '/role',
                    ],
                    [
                        'name' => 'Phòng ban',
                        'path' => '/',
                    ],
                    [
                        'name' => 'Chức vụ',
                        'path' => '/',
                    ],
                ],
            ]
        ];

        return [
            [
                'title' => 'Menu',
                'items' => $menuItems,
            ],
        ];
    }
}
