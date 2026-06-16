<?php

use App\Http\Controllers\AddressController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseSlipController;
use App\Http\Controllers\WarehouseInventoryController;

Route::apiResource(
    'warehouses',
    WarehouseController::class
);
Route::prefix('warehouse')->group(function () {
    Route::apiResource('products', ProductController::class)->names('warehouse.products.index');
    Route::apiResource('categories', CategoryController::class)->names('warehouse.categories');
    Route::apiResource('units', UnitController::class)->names('warehouse.units');
    Route::apiResource('slips', WarehouseSlipController::class)->names('warehouse.slips');

    Route::get('/inventory', [WarehouseInventoryController::class, 'index'])
        ->name('warehouse.inventory');

    Route::post('/slips/{id}/approve', [WarehouseSlipController::class, 'approve'])
        ->name('warehouse.slips.approve');
});
Route::prefix('purchase')->group(function () {
    Route::get('/suppliers/all', [SupplierController::class, 'all']);
    Route::apiResource('suppliers', SupplierController::class)->names('purchase.suppliers');
    Route::apiResource('categories', CategoryController::class)->names('purchase.categories');
    Route::apiResource('units', UnitController::class)->names('purchase.units');
    Route::apiResource('orders', PurchaseOrderController::class)->names('purchase.orders');
    Route::apiResource('products', ProductController::class)->names('purchase.products');
});

Route::post('/purchase/orders/{id}/approve', [PurchaseOrderController::class, 'approve']);
Route::get(
    '/warehouse/orders/{id}/stock-in',
    [PurchaseOrderController::class, 'stockInData']
);
Route::get(
    '/warehouse/orders',
    [PurchaseOrderController::class, 'warehouseIndex']
);
Route::post('/warehouse/slips/{id}/reject', [WarehouseSlipController::class, 'reject']);

Route::get('/products/for-select', [ProductController::class, 'forSelect']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/units', [UnitController::class, 'index']);


Route::get('/currencies', [CurrencyController::class, 'index']);
//

Route::middleware('permission:role.view')->group(function () {
    Route::get('/roles', [RoleController::class, 'index']);
});

Route::get('/permissions/all', [RoleController::class, 'permissions']);

Route::middleware('permission:role.create')->group(function () {
    Route::post('/roles', [RoleController::class, 'store']);
});

Route::middleware('permission:role.update')->group(function () {
    Route::put('/roles/{id}', [RoleController::class, 'update']);
});

Route::middleware('permission:role.delete')->group(function () {
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
});

Route::middleware('permission:user.view')->group(function () {

    Route::get('/users/user', [UserController::class, 'index']);
});
Route::middleware('permission:user.view')->group(function () {
    Route::get('/users/user/{id}', [UserController::class, 'show']);
});
Route::middleware('permission:user.create')->group(function () {
    Route::post('/users/user', [UserController::class, 'store']);
});
Route::middleware('permission:user.update')->group(function () {
    Route::put('/users/user/{id}', [UserController::class, 'update']);
});

Route::middleware('permission:user.delete')->group(function () {
    Route::delete('/users/user/{id}', [UserController::class, 'destroy']);
});

Route::middleware('permission:permission.view')->group(function () {
    Route::get('/permissions', [PermissionController::class, 'index']);
});
Route::middleware('permission:permission.create')->group(function () {
    Route::post('/permissions', [PermissionController::class, 'store']);
});
Route::middleware('permission:permission.update')->group(function () {
    Route::put('/permissions/{id}', [PermissionController::class, 'update']);
});
Route::patch(
    '/users/{user}/status',
    [UserController::class, 'toggleStatus']
);
Route::patch(
    '/warehouse/categories/{id}/status',
    [CategoryController::class, 'toggleStatus']
);
Route::patch(
    'units/{id}/status',
    [UnitController::class, 'toggleStatus']
);
Route::patch(
    '/warehouse/products/{id}/status',
    [ProductController::class, 'toggleStatus']
);

Route::get(
    '/company/create',
    [CompanyController::class, 'create']
);

//update tt kho
Route::patch('/warehouses/{id}/status', [WarehouseController::class, 'toggleStatus']);
//API lấy tỉnh
Route::get(
    '/provinces',
    [AddressController::class, 'provinces']
);

Route::get(
    '/provinces/{province}/wards',
    [AddressController::class, 'wards']
);

Route::get(
    '/notifications',
    [NotificationController::class, 'index']
);

Route::get(
    '/notifications/unread-count',
    [NotificationController::class, 'unreadCount']
);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {});
