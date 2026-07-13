<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AddressController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionCategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseSlipController;
use App\Http\Controllers\WarehouseInventoryController;
use App\Http\Controllers\Accountant\AccountLedgerController;
use App\Http\Controllers\DashboardController;


Route::get('/warehouses/{warehouse}/detail', [WarehouseController::class, 'detail']);
Route::get('/warehouses/all', [WarehouseController::class, 'all']);
Route::apiResource('warehouses', WarehouseController::class);
Route::prefix('warehouse')->group(function () {
    Route::apiResource('products', ProductController::class)->names('warehouse.products.index');
    Route::get('categories/select', [CategoryController::class, 'select']);
    Route::apiResource('categories', CategoryController::class)->names('warehouse.categories');
    Route::get('/units/select', [UnitController::class, 'select']);
    Route::apiResource('units', UnitController::class)->names('warehouse.units');
    Route::apiResource('slips', WarehouseSlipController::class)->names('warehouse.slips');
    Route::get('/inventory', [WarehouseInventoryController::class, 'index'])
        ->name('warehouse.inventory');
    Route::post('/slips/{id}/approve', [WarehouseSlipController::class, 'approve'])
        ->name('warehouse.slips.approve');
    Route::get('/stocks', [WarehouseController::class, 'getStocks'])
        ->name('warehouse.stocks');
});
Route::prefix('purchase')->group(function () {
    Route::get('/suppliers/all', [SupplierController::class, 'all']);
    Route::get('/suppliers/{id}/detail', [SupplierController::class, 'detail'])->name('purchase.suppliers.detail');
    Route::apiResource('suppliers', SupplierController::class)->names('purchase.suppliers');
    Route::get('categories/select', [CategoryController::class, 'select']);
    Route::apiResource('categories', CategoryController::class)->names('purchase.categories');
    Route::get('/units/select', [UnitController::class, 'select']);
    Route::apiResource('units', UnitController::class)->names('purchase.units');
    Route::apiResource('orders', PurchaseOrderController::class)->names('purchase.orders');
    Route::apiResource('products', ProductController::class)->names('purchase.products');
    Route::post(
        'orders/{id}/approve',
        [PurchaseOrderController::class, 'approve']
    )->name('purchase.orders.approve');
    Route::get(
        'orders/{id}/stock-in-data',
        [PurchaseOrderController::class, 'stockInData']
    );
});
Route::get('/sale/customers/next-code', [CustomerController::class, 'nextCode']);
Route::prefix('sale')->group(function () {
    Route::get('/customers/all',  [CustomerController::class, 'all']);
    Route::apiResource('customers', CustomerController::class)->names('sale.customers');
    Route::get('/customers/{id}/detail', [CustomerController::class, 'detail'])->name('sale.customers.detail');
    Route::post('/customers/{id}/quick-order', [CustomerController::class, 'createQuickOrder'])
        ->name('sale.customers.quick-order');
    Route::apiResource('orders', SalesOrderController::class)->names('sale.orders');
});
Route::prefix('accountant')->group(function () {

    // currency
    Route::apiResource('currencies', CurrencyController::class);
    Route::get('/currencies/all', [CurrencyController::class, 'all']);
    Route::get('currencies/{currency}/rates', [CurrencyController::class, 'rates']);
    Route::post('currencies/{currency}/rates', [CurrencyController::class, 'storeRate']);
    Route::patch('currencies/{currency}/toggle-status', [CurrencyController::class, 'toggleStatus']);

    // bank
    Route::apiResource('banks', BankController::class);
    Route::patch('banks/{bank}/toggle-status', [BankController::class, 'toggleStatus']);

    // account
    Route::get('accounts/all', [AccountController::class, 'all']);
    Route::apiResource('accounts', AccountController::class);
    Route::patch('accounts/{account}/toggle-status', [AccountController::class, 'toggleStatus']);
    Route::get('/accounts/{account}/ledger', [AccountLedgerController::class, 'accountLedger']);

    // debt monitoring
    Route::get('/customers-debt', [CustomerController::class, 'index'])
        ->name('accountant.customers-debt.index');
    Route::get('/customers-debt/{id}/detail', [CustomerController::class, 'detail'])
        ->name('accountant.customers-debt.detail');
    Route::get('/suppliers-debt', [SupplierController::class, 'index'])
        ->name('accountant.suppliers-debt.index');
    Route::get('/suppliers-debt/{id}/detail', [SupplierController::class, 'detail'])
        ->name('accountant.suppliers-debt.detail');

    // transaction types
    Route::get('/transaction-categories', [TransactionCategoryController::class, 'index']);

    Route::get('/transaction-categories/active', [TransactionCategoryController::class, 'active']);

    Route::post('/transaction-categories', [TransactionCategoryController::class, 'store']);

    Route::get('/transaction-categories/{transactionCategory}', [TransactionCategoryController::class, 'show']);

    Route::put('/transaction-categories/{transactionCategory}', [TransactionCategoryController::class, 'update']);

    Route::delete('/transaction-categories/{transactionCategory}', [TransactionCategoryController::class, 'destroy']);

    // transactions
    Route::get('/transactions', [TransactionController::class, 'index']);

    Route::post('/transactions', [TransactionController::class, 'store']);

    Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);

    Route::put('/transactions/{transaction}', [TransactionController::class, 'update']);

    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);

    Route::post('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel']);

    Route::post('/accounts/{id}/rebuild-balance', [AccountController::class, 'rebuildBalance']);
    Route::get('/account-ledgers', [AccountLedgerController::class, 'index']);
});
Route::get(
    '/warehouse/orders/{id}/stock-in',
    [PurchaseOrderController::class, 'stockInData']
);
Route::get(
    '/warehouse/orders/{id}/stock-out',
    [SalesOrderController::class, 'stockOutData']
);
Route::get(
    '/available-for-export',
    [SalesOrderController::class, 'availableForExport']
);
Route::get(
    '/saleorders/warehouse',
    [SalesOrderController::class, 'warehouseIndex']
);
Route::get(
    '/warehouse/orders',
    [PurchaseOrderController::class, 'warehouseIndex']
);

Route::post('/sale/orders/{id}/approve', [SalesOrderController::class, 'approve']);
Route::post('/warehouse/slips/{id}/reject', [WarehouseSlipController::class, 'reject']);

Route::get('/products/for-select', [ProductController::class, 'forSelect']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/units', [UnitController::class, 'index']);

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
Route::patch(
    'currencies/{currency}/toggle-status',
    [CurrencyController::class, 'toggleStatus']
);

Route::get(
    '/company/create',
    [CompanyController::class, 'create']
);

//update tt kho
Route::patch('/warehouse/{id}/status', [WarehouseController::class, 'toggleStatus']);
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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard/overview', [DashboardController::class, 'overview']);
});
