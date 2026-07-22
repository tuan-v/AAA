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
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\WarehouseTransferController;
use App\Http\Controllers\Accountant\AccountLedgerController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PositionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'throttle:api', 'audit'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | USER, ROLE, PERMISSION & COMPANY MODULES
    |--------------------------------------------------------------------------
    */
    Route::controller(UserController::class)->prefix('users')->group(function () {
        Route::get('/user', 'index')->middleware('permission:nhan_su.xem');
        Route::get('/user/{id}', 'show')->middleware('permission:nhan_su.xem');
        Route::post('/user', 'store')->middleware('permission:nhan_su.them');
        Route::put('/user/{id}', 'update')->middleware('permission:nhan_su.sua');
        Route::delete('/user/{id}', 'destroy')->middleware('permission:nhan_su.xoa');
        Route::patch('/{user}/status', 'toggleStatus')->middleware('permission:nhan_su.khoa');
    });

    Route::controller(DepartmentController::class)->prefix('departments')->group(function () {
        Route::get('/', 'index')->middleware('permission:nhan_su.xem');
        Route::get('/all', 'all')->middleware('permission:nhan_su.xem');
        Route::get('/managers', 'managers')->middleware('permission:nhan_su.xem');
        Route::post('/', 'store')->middleware('permission:nhan_su.them');
        Route::put('/{department}', 'update')->middleware('permission:nhan_su.sua');
        Route::delete('/{department}', 'destroy')->middleware('permission:nhan_su.xoa');
    });

    Route::controller(PositionController::class)->prefix('positions')->group(function () {
        Route::get('/', 'index')->middleware('permission:nhan_su.xem');
        Route::get('/all', 'all')->middleware('permission:nhan_su.xem');
        Route::post('/', 'store')->middleware('permission:nhan_su.them');
        Route::put('/{position}', 'update')->middleware('permission:nhan_su.sua');
        Route::delete('/{position}', 'destroy')->middleware('permission:nhan_su.xoa');
    });

    Route::controller(RoleController::class)->prefix('roles')->group(function () {
        Route::get('/', 'index')->middleware('permission:vai_tro.xem');
        Route::post('/', 'store')->middleware('permission:vai_tro.them');
        Route::put('/{id}', 'update')->middleware('permission:vai_tro.sua');
        Route::delete('/{id}', 'destroy')->middleware('permission:vai_tro.xoa');
    });

    Route::get('/permissions/all', [RoleController::class, 'permissions'])->middleware('permission:quyen.xem');

    Route::controller(PermissionController::class)->prefix('permissions')->group(function () {
        Route::get('/', 'index')->middleware('permission:quyen.xem');
        Route::post('/', 'store')->middleware('permission:quyen.them');
        Route::put('/{id}', 'update')->middleware('permission:quyen.sua');
        Route::delete('/{id}', 'destroy')->middleware('permission:quyen.xoa');
    });

    Route::get('/company/create', [CompanyController::class, 'create']);
    Route::controller(AuditLogController::class)->prefix('audit-logs')->group(function () {
        Route::get('/', 'index')->middleware('permission:nhat_ky.xem');
        Route::get('/trace', 'trace')->middleware('permission:nhat_ky.xem');
        Route::get('/{auditLog}', 'show')->middleware('permission:nhat_ky.xem');
    });
    /*
    |--------------------------------------------------------------------------
    | WAREHOUSES & SLIPS (WAREHOUSE MODULE)
    |--------------------------------------------------------------------------
    */
    Route::controller(WarehouseController::class)->prefix('warehouses')->group(function () {
        Route::get('/', 'index')->middleware('permission:kho.xem');
        Route::get('/all', 'all')->middleware('permission:kho.xem|san_pham_mua_hang.xem');
        Route::get('/{warehouse}/detail', 'detail')->middleware('permission:kho.xem');
        Route::get('/{warehouse}', 'show')->middleware('permission:kho.xem');
        Route::post('/', 'store')->middleware('permission:kho.them');
        Route::put('/{warehouse}', 'update')->middleware('permission:kho.sua');
        Route::delete('/{warehouse}', 'destroy')->middleware('permission:kho.xoa');
        Route::patch('/{warehouse}/status', 'toggleStatus')->middleware('permission:kho.khoa');
    });

    // Singletone-style status toggle back-compatibility
    Route::patch('/warehouse/{id}/status', [WarehouseController::class, 'toggleStatus'])->middleware('permission:kho.khoa');

    Route::prefix('warehouse')->group(function () {
        Route::controller(WarehouseTransferController::class)->prefix('transfers')->group(function () {
            Route::get('/', 'index')->middleware('permission:chuyen_kho.xem');
            Route::post('/', 'store')->middleware('permission:chuyen_kho.them');
            Route::post('/{id}/approve', 'approve')->middleware('permission:chuyen_kho.duyet');
            Route::post('/{id}/cancel', 'cancel')->middleware('permission:chuyen_kho.huy');
        });
        Route::controller(ProductController::class)->prefix('products')->group(function () {
            Route::get('/', 'index')->middleware('permission:san_pham_kho.xem');
            Route::post('/', 'store')->middleware('permission:san_pham_kho.them');
            Route::get('/{product}', 'show')->middleware('permission:san_pham_kho.xem');
            Route::put('/{product}', 'update')->middleware('permission:san_pham_kho.sua');
            Route::delete('/{product}', 'destroy')->middleware('permission:san_pham_kho.xoa');
            Route::patch('/{id}/status', 'toggleStatus')->middleware('permission:san_pham_kho.khoa');
        });

        Route::controller(CategoryController::class)->prefix('categories')->group(function () {
            Route::get('/', 'index')->middleware('permission:danh_muc_kho.xem');
            Route::get('/select', 'select')->middleware('permission:danh_muc_kho.xem');
            Route::post('/', 'store')->middleware('permission:danh_muc_kho.them');
            Route::get('/{category}', 'show')->middleware('permission:danh_muc_kho.xem');
            Route::put('/{category}', 'update')->middleware('permission:danh_muc_kho.sua');
            Route::delete('/{category}', 'destroy')->middleware('permission:danh_muc_kho.xoa');
            Route::patch('/{id}/status', 'toggleStatus')->middleware('permission:danh_muc_kho.khoa');
        });

        Route::controller(UnitController::class)->prefix('units')->group(function () {
            Route::get('/', 'index')->middleware('permission:don_vi_kho.xem');
            Route::get('/select', 'select')->middleware('permission:don_vi_kho.xem');
            Route::post('/', 'store')->middleware('permission:don_vi_kho.them');
            Route::get('/{unit}', 'show')->middleware('permission:don_vi_kho.xem');
            Route::put('/{unit}', 'update')->middleware('permission:don_vi_kho.sua');
            Route::delete('/{unit}', 'destroy')->middleware('permission:don_vi_kho.xoa');
            Route::patch('/{id}/status', 'toggleStatus')->middleware('permission:don_vi_kho.khoa');
        });

        Route::controller(WarehouseSlipController::class)->prefix('slips')->group(function () {
            Route::get('/', 'index')->middleware('permission:phieu_kho.xem');
            Route::post('/', 'store')->middleware('permission:phieu_kho.them');
            Route::get('/{slip}', 'show')->middleware('permission:phieu_kho.xem_chi_tiet');
            Route::put('/{slip}', 'update')->middleware('permission:phieu_kho.sua');
            Route::post('/{id}/approve', 'approve')->middleware('permission:phieu_kho.duyet');
            Route::post('/{id}/reject', 'reject')->middleware('permission:phieu_kho.tu_choi');
        });

        Route::get('/inventory', [WarehouseInventoryController::class, 'index'])->middleware('permission:kho.xem');
        Route::get('/inventory-movements', [InventoryMovementController::class, 'index'])->middleware('permission:kho.xem');
        Route::get('/stocks', [WarehouseController::class, 'getStocks'])->middleware('permission:kho.xem');
    });

    /*
    |--------------------------------------------------------------------------
    | PURCHASE MODULE
    |--------------------------------------------------------------------------
    */
    Route::prefix('purchase')->group(function () {
        Route::controller(SupplierController::class)->prefix('suppliers')->group(function () {
            Route::get('/', 'index')->middleware('permission:nha_cung_cap.xem');
            Route::get('/all', 'all')->middleware('permission:nha_cung_cap.xem|giao_dich.them|giao_dich.sua');
            Route::get('/{id}/detail', 'detail')->middleware('permission:nha_cung_cap.xem_chi_tiet|cong_no_nha_cung_cap.xem_chi_tiet');
            Route::post('/', 'store')->middleware('permission:nha_cung_cap.them');
            Route::get('/{supplier}', 'show')->middleware('permission:nha_cung_cap.xem');
            Route::put('/{supplier}', 'update')->middleware('permission:nha_cung_cap.sua');
            Route::delete('/{supplier}', 'destroy')->middleware('permission:nha_cung_cap.xoa');
            Route::patch('/{id}/status', 'toggleStatus')->middleware('permission:nha_cung_cap.khoa');
        });

        Route::controller(CategoryController::class)->prefix('categories')->group(function () {
            Route::get('/', 'index')->middleware('permission:danh_muc_mua_hang.xem');
            Route::get('/select', 'select')->middleware('permission:danh_muc_mua_hang.xem');
            Route::post('/', 'store')->middleware('permission:danh_muc_mua_hang.them');
            Route::get('/{category}', 'show')->middleware('permission:danh_muc_mua_hang.xem');
            Route::put('/{category}', 'update')->middleware('permission:danh_muc_mua_hang.sua');
            Route::delete('/{category}', 'destroy')->middleware('permission:danh_muc_mua_hang.xoa');
            Route::patch('/{id}/status', 'toggleStatus')->middleware('permission:danh_muc_mua_hang.khoa');
        });

        Route::controller(UnitController::class)->prefix('units')->group(function () {
            Route::get('/', 'index')->middleware('permission:don_vi_mua_hang.xem');
            Route::get('/select', 'select')->middleware('permission:don_vi_mua_hang.xem');
            Route::post('/', 'store')->middleware('permission:don_vi_mua_hang.them');
            Route::get('/{unit}', 'show')->middleware('permission:don_vi_mua_hang.xem');
            Route::put('/{unit}', 'update')->middleware('permission:don_vi_mua_hang.sua');
            Route::delete('/{unit}', 'destroy')->middleware('permission:don_vi_mua_hang.xoa');
            Route::patch('/{id}/status', 'toggleStatus')->middleware('permission:don_vi_mua_hang.khoa');
        });

        Route::controller(ProductController::class)->prefix('products')->group(function () {
            Route::get('/', 'index')->middleware('permission:san_pham_mua_hang.xem');
            Route::post('/', 'store')->middleware('permission:san_pham_mua_hang.them');
            Route::get('/{product}', 'show')->middleware('permission:san_pham_mua_hang.xem');
            Route::put('/{product}', 'update')->middleware('permission:san_pham_mua_hang.sua');
            Route::delete('/{product}', 'destroy')->middleware('permission:san_pham_mua_hang.xoa');
            Route::patch('/{id}/status', 'toggleStatus')->middleware('permission:san_pham_mua_hang.khoa');
        });

        Route::controller(PurchaseOrderController::class)->prefix('orders')->group(function () {
            Route::get('/', 'index')->middleware('permission:don_mua.xem|giao_dich.them|giao_dich.sua');
            Route::post('/', 'store')->middleware('permission:don_mua.them');
            Route::get('/{order}', 'show')->middleware('permission:don_mua.xem_chi_tiet|cong_no_nha_cung_cap.xem_chi_tiet');
            Route::put('/{order}', 'update')->middleware('permission:don_mua.sua');
            Route::delete('/{order}', 'destroy')->middleware('permission:don_mua.xoa');
            Route::post('/{id}/approve', 'approve')->middleware('permission:don_mua.duyet');
            Route::post('/{id}/cancel', 'cancel')->middleware('permission:don_mua.huy');
            Route::get('/{id}/stock-in-data', 'stockInData')->middleware('permission:don_mua.xem_chi_tiet');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | SALE MODULE
    |--------------------------------------------------------------------------
    */
    Route::prefix('sale')->group(function () {
        Route::controller(CustomerController::class)->prefix('customers')->group(function () {
            Route::get('/', 'index')->middleware('permission:khach_hang.xem');
            Route::get('/all', 'all')->middleware('permission:khach_hang.xem|giao_dich.them|giao_dich.sua');
            Route::get('/next-code', 'nextCode')->middleware('permission:khach_hang.xem');
            Route::post('/', 'store')->middleware('permission:khach_hang.them');
            Route::get('/{id}/detail', 'detail')
                ->middleware('permission:khach_hang.xem|cong_no_khach_hang.xem_chi_tiet');
            Route::get('/{customer}', 'show')->middleware('permission:khach_hang.xem');
            Route::put('/{customer}', 'update')->middleware('permission:khach_hang.sua');
            Route::delete('/{customer}', 'destroy')->middleware('permission:khach_hang.xoa');
            Route::patch('/{customer}/status', 'toggleStatus')->middleware('permission:khach_hang.khoa');
            Route::post('/{id}/quick-order', 'createQuickOrder')->middleware('permission:don_ban.them');
        });

        Route::controller(SalesOrderController::class)->prefix('orders')->group(function () {
            Route::get('/', 'index')->middleware('permission:don_ban.xem|giao_dich.them|giao_dich.sua');
            Route::post('/', 'store')->middleware('permission:don_ban.them');
            Route::get('/{order}', 'show')->middleware('permission:don_ban.xem_chi_tiet|cong_no_khach_hang.xem_chi_tiet');
            Route::put('/{order}', 'update')->middleware('permission:don_ban.sua');
            Route::delete('/{order}', 'destroy')->middleware('permission:don_ban.xoa');
            Route::post('/{id}/approve', 'approve')->middleware('permission:don_ban.duyet');
            Route::post('/{id}/cancel', 'cancel')->middleware('permission:don_ban.huy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | ACCOUNTANT MODULE
    |--------------------------------------------------------------------------
    */
    Route::prefix('accountant')->group(function () {
        Route::controller(CurrencyController::class)->prefix('currencies')->group(function () {
            Route::get('/', 'index')->middleware('permission:tien_te.xem');
            Route::get('/all', 'all')->middleware('permission:tien_te.xem');
            Route::post('/', 'store')->middleware('permission:tien_te.them');

            Route::get('/{currency}', 'show')->middleware('permission:tien_te.xem');
            Route::put('/{currency}', 'update')->middleware('permission:tien_te.sua');
            Route::delete('/{currency}', 'destroy')->middleware('permission:tien_te.xoa');
            Route::patch('/{currency}/toggle-status', 'toggleStatus')->middleware('permission:tien_te.khoa');
            Route::get('/{currency}/rates', 'rates')->middleware('permission:tien_te.xem_lich_su');
            Route::post('/{currency}/rates', 'storeRate')->middleware('permission:tien_te.sua');
        });

        Route::controller(BankController::class)->prefix('banks')->group(function () {
            Route::get('/', 'index')->middleware('permission:ngan_hang.xem');
            Route::post('/', 'store')->middleware('permission:ngan_hang.them');
            Route::get('/{bank}', 'show')->middleware('permission:ngan_hang.xem');
            Route::put('/{bank}', 'update')->middleware('permission:ngan_hang.sua');
            Route::delete('/{bank}', 'destroy')->middleware('permission:ngan_hang.xoa');
            Route::patch('/{bank}/toggle-status', 'toggleStatus')->middleware('permission:ngan_hang.khoa');
        });

        Route::controller(AccountController::class)->prefix('accounts')->group(function () {
            Route::get('/', 'index')->middleware('permission:tai_khoan.xem');
            Route::get('/all', 'all')->middleware('permission:tai_khoan.xem');
            Route::post('/', 'store')->middleware('permission:tai_khoan.them');
            Route::get('/{account}', 'show')->middleware('permission:tai_khoan.xem');
            Route::put('/{account}', 'update')->middleware('permission:tai_khoan.sua');
            Route::delete('/{account}', 'destroy')->middleware('permission:tai_khoan.xoa');
            Route::patch('/{account}/toggle-status', 'toggleStatus')->middleware('permission:tai_khoan.khoa');
            Route::post('/{id}/rebuild-balance', 'rebuildBalance')->middleware('permission:tai_khoan.sua');
        });

        Route::controller(AccountLedgerController::class)->group(function () {
            Route::get('/account-ledgers', 'index')->middleware('permission:giao_dich.xem');
            Route::get('/accounts/{account}/ledger', 'accountLedger')->middleware('permission:giao_dich.xem');
        });

        Route::controller(CustomerController::class)->prefix('customers-debt')->group(function () {
            Route::get('/', 'index')->middleware('permission:cong_no_khach_hang.xem')->name('accountant.customers-debt.index');
            Route::get('/{id}/detail', 'detail')->middleware('permission:cong_no_khach_hang.xem_chi_tiet')->name('accountant.customers-debt.detail');
        });

        Route::controller(SupplierController::class)->prefix('suppliers-debt')->group(function () {
            Route::get('/', 'index')->middleware('permission:cong_no_nha_cung_cap.xem')->name('accountant.suppliers-debt.index');
            Route::get('/{id}/detail', 'detail')->middleware('permission:cong_no_nha_cung_cap.xem_chi_tiet')->name('accountant.suppliers-debt.detail');
        });

        Route::controller(TransactionCategoryController::class)->prefix('transaction-categories')->group(function () {
            Route::get('/', 'index')->middleware('permission:loai_giao_dich.xem');
            Route::get('/active', 'active')->middleware('permission:loai_giao_dich.xem');
            Route::post('/', 'store')->middleware('permission:loai_giao_dich.them');
            Route::get('/{transactionCategory}', 'show')->middleware('permission:loai_giao_dich.xem');
            Route::put('/{transactionCategory}', 'update')->middleware('permission:loai_giao_dich.sua');
            Route::delete('/{transactionCategory}', 'destroy')->middleware('permission:loai_giao_dich.xoa');
        });

        Route::controller(TransactionController::class)->prefix('transactions')->group(function () {
            Route::get('/', 'index')->middleware('permission:giao_dich.xem');
            Route::post('/', 'store')->middleware('permission:giao_dich.them');
            Route::get('/order-outstanding', 'orderOutstanding')->middleware('permission:giao_dich.xem');
            Route::get('/{transaction}', 'show')->middleware('permission:giao_dich.xem');
            Route::put('/{transaction}', 'update')->middleware('permission:giao_dich.sua');
            Route::delete('/{transaction}', 'destroy')->middleware('permission:giao_dich.xoa');
            Route::post('/{transaction}/approve', 'approve')->middleware('permission:giao_dich.duyet'); // MỚI
            Route::post('/{transaction}/reject', 'reject')->middleware('permission:giao_dich.tu_choi');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | WAREHOUSE ORDER APIS (SHARED)
    |--------------------------------------------------------------------------
    */
    Route::get('/warehouse/orders', [PurchaseOrderController::class, 'warehouseIndex'])->middleware('permission:phieu_kho.xem');
    Route::get('/warehouse/orders/{id}/stock-in', [PurchaseOrderController::class, 'stockInData'])->middleware('permission:phieu_kho.them');
    Route::get('/warehouse/orders/{id}/stock-out', [SalesOrderController::class, 'stockOutData'])->middleware('permission:phieu_kho.them');
    Route::get('/saleorders/warehouse', [SalesOrderController::class, 'warehouseIndex'])->middleware('permission:phieu_kho.xem');
    Route::get('/available-for-export', [SalesOrderController::class, 'availableForExport'])->middleware('permission:phieu_kho.xem');

    /*
    |--------------------------------------------------------------------------
    | SELECTORS & SHARED ENDPOINTS AT ROOT
    |--------------------------------------------------------------------------
    */
    Route::get('/products/for-select', [ProductController::class, 'forSelect'])
        ->middleware('permission:san_pham_mua_hang.xem|don_ban.xem|khach_hang.xem');
    Route::get('/currencies/for-select', [CurrencyController::class, 'forSelect'])
        ->middleware('permission:nha_cung_cap.xem|don_mua.xem|don_ban.xem|khach_hang.xem|giao_dich.xem');
    Route::get('/categories', [CategoryController::class, 'index'])->middleware('permission:danh_muc_mua_hang.xem');
    Route::get('/units', [UnitController::class, 'index'])->middleware('permission:don_vi_mua_hang.xem');

    Route::patch('units/{id}/status', [UnitController::class, 'toggleStatus'])->middleware('permission:don_vi_kho.khoa');
    Route::patch('currencies/{currency}/toggle-status', [CurrencyController::class, 'toggleStatus'])->middleware('permission:tien_te.khoa');

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
        Route::post('/mark-all-read', 'markAllAsRead');
        Route::post('/{notification}/mark-as-read', 'markAsRead');
        Route::delete('/{notification}', 'destroy');
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
    Route::get('/dashboard/{module}', [DashboardController::class, 'module'])
        ->whereIn('module', ['purchase', 'sale', 'warehouse', 'accountant']);
});
