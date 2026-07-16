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
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'audit'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | USER, ROLE, PERMISSION & COMPANY MODULES
    |--------------------------------------------------------------------------
    */
    Route::controller(UserController::class)->prefix('users')->group(function () {
        Route::get('/user', 'index')->middleware('permission:user.view');
        Route::get('/user/{id}', 'show')->middleware('permission:user.view');
        Route::post('/user', 'store')->middleware('permission:user.create');
        Route::put('/user/{id}', 'update')->middleware('permission:user.update');
        Route::delete('/user/{id}', 'destroy')->middleware('permission:user.delete');
        Route::patch('/{user}/status', 'toggleStatus')->middleware('permission:user.lock');
    });

    Route::controller(RoleController::class)->prefix('roles')->group(function () {
        Route::get('/', 'index')->middleware('permission:role.view');
        Route::post('/', 'store')->middleware('permission:role.create');
        Route::put('/{id}', 'update')->middleware('permission:role.update');
        Route::delete('/{id}', 'destroy')->middleware('permission:role.delete');
    });

    Route::get('/permissions/all', [RoleController::class, 'permissions'])->middleware('permission:permission.view');

    Route::controller(PermissionController::class)->prefix('permissions')->group(function () {
        Route::get('/', 'index')->middleware('permission:permission.view');
        Route::post('/', 'store')->middleware('permission:permission.create');
        Route::put('/{id}', 'update')->middleware('permission:permission.update');
    });

    Route::get('/company/create', [CompanyController::class, 'create']);
    Route::controller(AuditLogController::class)->prefix('audit-logs')->group(function () {
        Route::get('/', 'index')->middleware('permission:auditlog.view');
        Route::get('/trace', 'trace')->middleware('permission:auditlog.view');
        Route::get('/{auditLog}', 'show')->middleware('permission:auditlog.view');
    });
    /*
    |--------------------------------------------------------------------------
    | WAREHOUSES & SLIPS (WAREHOUSE MODULE)
    |--------------------------------------------------------------------------
    */
    Route::controller(WarehouseController::class)->prefix('warehouses')->group(function () {
        Route::get('/', 'index')->middleware('permission:warehouse.view');
        Route::get('/all', 'all')->middleware('permission:warehouse.view');
        Route::get('/{warehouse}/detail', 'detail')->middleware('permission:warehouse.view');
        Route::get('/{warehouse}', 'show')->middleware('permission:warehouse.view');
        Route::post('/', 'store')->middleware('permission:warehouse.create');
        Route::put('/{warehouse}', 'update')->middleware('permission:warehouse.update');
        Route::delete('/{warehouse}', 'destroy')->middleware('permission:warehouse.delete');
        Route::patch('/{warehouse}/status', 'toggleStatus')->middleware('permission:warehouse.lock');
    });

    // Singletone-style status toggle back-compatibility
    Route::patch('/warehouse/{id}/status', [WarehouseController::class, 'toggleStatus'])->middleware('permission:warehouse.lock');

    Route::prefix('warehouse')->group(function () {
        Route::controller(ProductController::class)->prefix('products')->group(function () {
            Route::get('/', 'index')->middleware('permission:warehouse_product.view');
            Route::post('/', 'store')->middleware('permission:warehouse_product.create');
            Route::get('/{product}', 'show')->middleware('permission:warehouse_product.view');
            Route::put('/{product}', 'update')->middleware('permission:warehouse_product.update');
            Route::delete('/{product}', 'destroy')->middleware('permission:warehouse_product.delete');
            Route::patch('/{id}/status', 'toggleStatus')->middleware('permission:warehouse_product.lock');
        });

        Route::controller(CategoryController::class)->prefix('categories')->group(function () {
            Route::get('/', 'index')->middleware('permission:warehouse_category.view');
            Route::get('/select', 'select')->middleware('permission:warehouse_category.view');
            Route::post('/', 'store')->middleware('permission:warehouse_category.create');
            Route::get('/{category}', 'show')->middleware('permission:warehouse_category.view');
            Route::put('/{category}', 'update')->middleware('permission:warehouse_category.update');
            Route::delete('/{category}', 'destroy')->middleware('permission:warehouse_category.delete');
            Route::patch('/{id}/status', 'toggleStatus')->middleware('permission:warehouse_category.lock');
        });

        Route::controller(UnitController::class)->prefix('units')->group(function () {
            Route::get('/', 'index')->middleware('permission:warehouse_unit.view');
            Route::get('/select', 'select')->middleware('permission:warehouse_unit.view');
            Route::post('/', 'store')->middleware('permission:warehouse_unit.create');
            Route::get('/{unit}', 'show')->middleware('permission:warehouse_unit.view');
            Route::put('/{unit}', 'update')->middleware('permission:warehouse_unit.update');
            Route::delete('/{unit}', 'destroy')->middleware('permission:warehouse_unit.delete');
            Route::patch('/{id}/status', 'toggleStatus')->middleware('permission:warehouse_unit.lock');
        });

        Route::controller(WarehouseSlipController::class)->prefix('slips')->group(function () {
            Route::get('/', 'index')->middleware('permission:warehouse_slip.view');
            Route::post('/', 'store')->middleware('permission:warehouse_slip.create');
            Route::get('/{slip}', 'show')->middleware('permission:warehouse_slip.detail');
            Route::put('/{slip}', 'update')->middleware('permission:warehouse_slip.update');
            Route::post('/{id}/approve', 'approve')->middleware('permission:warehouse_slip.approve');
            Route::post('/{id}/reject', 'reject')->middleware('permission:warehouse_slip.reject');
        });

        Route::get('/inventory', [WarehouseInventoryController::class, 'index'])->middleware('permission:warehouse.view');
        Route::get('/stocks', [WarehouseController::class, 'getStocks'])->middleware('permission:warehouse.view');
    });

    /*
    |--------------------------------------------------------------------------
    | PURCHASE MODULE
    |--------------------------------------------------------------------------
    */
    Route::prefix('purchase')->group(function () {
        Route::controller(SupplierController::class)->prefix('suppliers')->group(function () {
            Route::get('/', 'index')->middleware('permission:supplier.view');
            Route::get('/all', 'all')->middleware('permission:supplier.view');
            Route::get('/{id}/detail', 'detail')->middleware('permission:supplier.detail');
            Route::post('/', 'store')->middleware('permission:supplier.create');
            Route::get('/{supplier}', 'show')->middleware('permission:supplier.view');
            Route::put('/{supplier}', 'update')->middleware('permission:supplier.update');
            Route::delete('/{supplier}', 'destroy')->middleware('permission:supplier.delete');
        });

        Route::controller(CategoryController::class)->prefix('categories')->group(function () {
            Route::get('/', 'index')->middleware('permission:purchase_category.view');
            Route::get('/select', 'select')->middleware('permission:purchase_category.view');
            Route::post('/', 'store')->middleware('permission:purchase_category.create');
            Route::get('/{category}', 'show')->middleware('permission:purchase_category.view');
            Route::put('/{category}', 'update')->middleware('permission:purchase_category.update');
            Route::delete('/{category}', 'destroy')->middleware('permission:purchase_category.delete');
            Route::patch('/{id}/status', 'toggleStatus')->middleware('permission:purchase_category.lock');
        });

        Route::controller(UnitController::class)->prefix('units')->group(function () {
            Route::get('/', 'index')->middleware('permission:purchase_unit.view');
            Route::get('/select', 'select')->middleware('permission:purchase_unit.view');
            Route::post('/', 'store')->middleware('permission:purchase_unit.create');
            Route::get('/{unit}', 'show')->middleware('permission:purchase_unit.view');
            Route::put('/{unit}', 'update')->middleware('permission:purchase_unit.update');
            Route::delete('/{unit}', 'destroy')->middleware('permission:purchase_unit.delete');
            Route::patch('/{id}/status', 'toggleStatus')->middleware('permission:purchase_unit.lock');
        });

        Route::controller(ProductController::class)->prefix('products')->group(function () {
            Route::get('/', 'index')->middleware('permission:product.view');
            Route::post('/', 'store')->middleware('permission:product.create');
            Route::get('/{product}', 'show')->middleware('permission:product.view');
            Route::put('/{product}', 'update')->middleware('permission:product.update');
            Route::delete('/{product}', 'destroy')->middleware('permission:product.delete');
            Route::patch('/{id}/status', 'toggleStatus')->middleware('permission:product.lock');
        });

        Route::controller(PurchaseOrderController::class)->prefix('orders')->group(function () {
            Route::get('/', 'index')->middleware('permission:purchase_order.view');
            Route::post('/', 'store')->middleware('permission:purchase_order.create');
            Route::get('/{order}', 'show')->middleware('permission:purchase_order.detail');
            Route::put('/{order}', 'update')->middleware('permission:purchase_order.update');
            Route::delete('/{order}', 'destroy')->middleware('permission:purchase_order.delete');
            Route::post('/{id}/approve', 'approve')->middleware('permission:purchase_order.approve');
            Route::get('/{id}/stock-in-data', 'stockInData')->middleware('permission:purchase_order.detail');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | SALE MODULE
    |--------------------------------------------------------------------------
    */
    Route::prefix('sale')->group(function () {
        Route::controller(CustomerController::class)->prefix('customers')->group(function () {
            Route::get('/', 'index')->middleware('permission:sale_customer.view');
            Route::get('/all', 'all')->middleware('permission:sale_customer.view');
            Route::get('/next-code', 'nextCode')->middleware('permission:sale_customer.view');
            Route::post('/', 'store')->middleware('permission:sale_customer.create');
            Route::get('/{id}/detail', 'detail')->middleware('permission:customer_debt.detail');
            Route::get('/{customer}', 'show')->middleware('permission:sale_customer.view');
            Route::put('/{customer}', 'update')->middleware('permission:sale_customer.update');
            Route::delete('/{customer}', 'destroy')->middleware('permission:sale_customer.delete');
            Route::patch('/{customer}/status', 'toggleStatus')->middleware('permission:sale_customer.lock');
            Route::patch('/{customer}/status', 'toggleStatus')->middleware('permission:sale_customer.unlock');
            Route::post('/{id}/quick-order', 'createQuickOrder')->middleware('permission:sale_order.create');
        });

        Route::controller(SalesOrderController::class)->prefix('orders')->group(function () {
            Route::get('/', 'index')->middleware('permission:sale_order.view');
            Route::post('/', 'store')->middleware('permission:sale_order.create');
            Route::get('/{order}', 'show')->middleware('permission:sale_order.detail');
            Route::put('/{order}', 'update')->middleware('permission:sale_order.update');
            Route::delete('/{order}', 'destroy')->middleware('permission:sale_order.delete');
            Route::post('/{id}/approve', 'approve')->middleware('permission:sale_order.approve');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | ACCOUNTANT MODULE
    |--------------------------------------------------------------------------
    */
    Route::prefix('accountant')->group(function () {
        Route::controller(CurrencyController::class)->prefix('currencies')->group(function () {
            Route::get('/', 'index')->middleware('permission:currency.view');
            Route::get('/all', 'all')->middleware('permission:currency.view');
            Route::post('/', 'store')->middleware('permission:currency.create');

            Route::get('/{currency}', 'show')->middleware('permission:currency.view');
            Route::put('/{currency}', 'update')->middleware('permission:currency.update');
            Route::delete('/{currency}', 'destroy')->middleware('permission:currency.delete');
            Route::patch('/{currency}/toggle-status', 'toggleStatus')->middleware('permission:currency.lock');
            Route::get('/{currency}/rates', 'rates')->middleware('permission:currency.history');
            Route::post('/{currency}/rates', 'storeRate')->middleware('permission:currency.update');
        });

        Route::controller(BankController::class)->prefix('banks')->group(function () {
            Route::get('/', 'index')->middleware('permission:bank.view');
            Route::post('/', 'store')->middleware('permission:bank.create');
            Route::get('/{bank}', 'show')->middleware('permission:bank.view');
            Route::put('/{bank}', 'update')->middleware('permission:bank.update');
            Route::delete('/{bank}', 'destroy')->middleware('permission:bank.delete');
            Route::patch('/{bank}/toggle-status', 'toggleStatus')->middleware('permission:bank.lock');
        });

        Route::controller(AccountController::class)->prefix('accounts')->group(function () {
            Route::get('/', 'index')->middleware('permission:account.view');
            Route::get('/all', 'all')->middleware('permission:account.view');
            Route::post('/', 'store')->middleware('permission:account.create');
            Route::get('/{account}', 'show')->middleware('permission:account.view');
            Route::put('/{account}', 'update')->middleware('permission:account.update');
            Route::delete('/{account}', 'destroy')->middleware('permission:account.delete');
            Route::patch('/{account}/toggle-status', 'toggleStatus')->middleware('permission:account.lock');
            Route::post('/{id}/rebuild-balance', 'rebuildBalance')->middleware('permission:account.update');
        });

        Route::controller(AccountLedgerController::class)->group(function () {
            Route::get('/account-ledgers', 'index')->middleware('permission:transaction.view');
            Route::get('/accounts/{account}/ledger', 'accountLedger')->middleware('permission:transaction.view');
        });

        Route::controller(CustomerController::class)->prefix('customers-debt')->group(function () {
            Route::get('/', 'index')->middleware('permission:customer_debt.view');
            Route::get('/{id}/detail', 'detail')->middleware('permission:customer_debt.detail');
        });

        Route::controller(SupplierController::class)->prefix('suppliers-debt')->group(function () {
            Route::get('/', 'index')->middleware('permission:supplier_debt.view');
            Route::get('/{id}/detail', 'detail')->middleware('permission:supplier_debt.detail');
        });

        Route::controller(TransactionCategoryController::class)->prefix('transaction-categories')->group(function () {
            Route::get('/', 'index')->middleware('permission:transaction_category.view');
            Route::get('/active', 'active')->middleware('permission:transaction_category.view');
            Route::post('/', 'store')->middleware('permission:transaction_category.create');
            Route::get('/{transactionCategory}', 'show')->middleware('permission:transaction_category.view');
            Route::put('/{transactionCategory}', 'update')->middleware('permission:transaction_category.update');
            Route::delete('/{transactionCategory}', 'destroy')->middleware('permission:transaction_category.delete');
        
        });

        Route::controller(TransactionController::class)->prefix('transactions')->group(function () {
            Route::get('/', 'index')->middleware('permission:transaction.view');
            Route::post('/', 'store')->middleware('permission:transaction.create');
            Route::get('/{transaction}', 'show')->middleware('permission:transaction.view');
            Route::put('/{transaction}', 'update')->middleware('permission:transaction.update');
            Route::delete('/{transaction}', 'destroy')->middleware('permission:transaction.delete');
              Route::post('/{transaction}/approve', 'approve')->middleware('permission:transaction.approve'); // MỚI
            Route::post('/{transaction}/reject', 'reject')->middleware('permission:transaction.reject');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | WAREHOUSE ORDER APIS (SHARED)
    |--------------------------------------------------------------------------
    */
    Route::get('/warehouse/orders', [PurchaseOrderController::class, 'warehouseIndex'])->middleware('permission:warehouse_slip.view');
    Route::get('/warehouse/orders/{id}/stock-in', [PurchaseOrderController::class, 'stockInData'])->middleware('permission:warehouse_slip.create');
    Route::get('/warehouse/orders/{id}/stock-out', [SalesOrderController::class, 'stockOutData'])->middleware('permission:warehouse_slip.create');
    Route::get('/saleorders/warehouse', [SalesOrderController::class, 'warehouseIndex'])->middleware('permission:warehouse_slip.view');
    Route::get('/available-for-export', [SalesOrderController::class, 'availableForExport'])->middleware('permission:warehouse_slip.view');

    /*
    |--------------------------------------------------------------------------
    | SELECTORS & SHARED ENDPOINTS AT ROOT
    |--------------------------------------------------------------------------
    */
    Route::get('/products/for-select', [ProductController::class, 'forSelect'])->middleware('permission:purchase_product.view');
    Route::get('/categories', [CategoryController::class, 'index'])->middleware('permission:purchase_category.view');
    Route::get('/units', [UnitController::class, 'index'])->middleware('permission:purchase_unit.view');

    Route::patch('units/{id}/status', [UnitController::class, 'toggleStatus'])->middleware('permission:warehouse_unit.lock');
    Route::patch('currencies/{currency}/toggle-status', [CurrencyController::class, 'toggleStatus'])->middleware('permission:currency.lock');

    /*
    |--------------------------------------------------------------------------
    | ADDRESS & NOTIFICATION APIS
    |--------------------------------------------------------------------------
    */
    Route::controller(AddressController::class)->prefix('provinces')->group(function () {
        Route::get('/', 'provinces');
        Route::get('/{province}/wards', 'wards');
    });

    Route::controller(NotificationController::class)->prefix('notifications')->group(function () {
        Route::get('/', 'index');
        Route::get('/unread-count', 'unreadCount');
    });

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD & CURRENT USER
    |--------------------------------------------------------------------------
    */
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/dashboard/overview', [DashboardController::class, 'overview']);
});
