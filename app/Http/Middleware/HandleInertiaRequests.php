<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;


class HandleInertiaRequests extends Middleware
{

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user()?->loadMissing(['company', 'roles']);

        return array_merge(parent::share($request), [
            'auths' => [
                'user' => $user,

                'companies' => $user && $user->company
                    ? [$user->company]
                    : [],

                'permissions' => $user
                    ? $user->getAllPermissions()->pluck('name')->toArray()
                    : [],

            ],
        ]);
    }
    // protected function webMenuItems(): array
    // {
    //     return [
    //         [
    //             'title' => 'Menu',
    //             'items' => [
    //                 ['icon' => 'GridIcon', 'name' => 'Dashboard', 'path' => '/dashboard'],
    //             ],
    //         ],
    //     ];
    // }

    // protected function warehouseMenuItems(): array
    // {
    //     return [
    //         [
    //             'title' => 'QUẢN LÝ KHO',
    //             'items' => [
    //                 ['icon' => 'BoxIcon', 'name' => 'Kho hàng', 'path' => '/warehouse'],
    //             ],
    //         ],
    //     ];
    // }

    // protected function salesMenuItems(): array
    // {
    //     return [
    //         [
    //             'title' => 'BÁN HÀNG',
    //             'items' => [
    //                 ['icon' => 'ShoppingCartIcon', 'name' => 'Đơn hàng', 'path' => '/orders'],
    //             ],
    //         ],
    //     ];
    // }

    // protected function purchaseMenuItems(): array
    // {
    //     return [
    //         [
    //             'title' => 'MUA HÀNG',
    //             'items' => [
    //                 ['icon' => 'TruckIcon', 'name' => 'Nhà cung cấp', 'path' => '/suppliers'],
    //             ],
    //         ],
    //     ];
    // }

    // protected function accountingMenuItems(): array
    // {
    //     return [
    //         [
    //             'title' => 'KẾ TOÁN',
    //             'items' => [
    //                 ['icon' => 'WalletIcon', 'name' => 'Thu chi', 'path' => '/cash-flow'],
    //             ],
    //         ],
    //     ];
    // }
    // protected function getMenuItems(Request $request): array
    // {
    //     $path = $request->path();

    //     return match (true) {

    //         str_starts_with($path, 'warehouse') => $this->warehouseMenuItems(),

    //         str_starts_with($path, 'purchase') => $this->purchaseMenuItems(),

    //         str_starts_with($path, 'sale') => $this->salesMenuItems(),

    //         str_starts_with($path, 'accounting') => $this->accountingMenuItems(),

    //         default => $this->webMenuItems(),
    //     };
    // }
}
