<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WEB\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Authenticated business flows
|--------------------------------------------------------------------------
|
| Cac route ben duoi la man hinh nghiep vu cua ERP. Moi phan he tu kiem tra
| permission rieng; API cung cap du lieu cho cac man hinh nam tai api.php.
|
*/
Route::middleware('auth')->group(function () {
    /*
    |----------------------------------------------------------------------
    | Warehouse flow: kho -> hang hoa -> dieu chuyen -> phieu kho
    |----------------------------------------------------------------------
    */
    Route::prefix('warehouse')->group(function () {
        Route::get('/', fn () => Inertia::render('Warehouse/Dashboard'))
            ->middleware('permission:kho.xem');
        Route::get('/list', fn () => Inertia::render('Warehouse/Index'))
            ->middleware('permission:kho.xem');
        Route::get('/{id}/detail', fn ($id) => Inertia::render('Warehouse/WarehouseDetail', [
            'id' => (int) $id,
        ]))->middleware('permission:kho.xem');

        // Danh muc va san pham thuoc kho.
        Route::get('/products', fn () => Inertia::render('Warehouse/Product/Index'))
            ->middleware('permission:san_pham_kho.xem');
        Route::get('/categories', fn () => Inertia::render('Warehouse/Category/Index'))
            ->middleware('permission:danh_muc_kho.xem');
        Route::get('/units', fn () => Inertia::render('Warehouse/Unit/Index'))
            ->middleware('permission:don_vi_kho.xem');

        // Van hanh ton kho va luan chuyen hang hoa.
        Route::get('/transfers', fn () => Inertia::render('Warehouse/Transfer/Index'))
            ->middleware('permission:chuyen_kho.xem');
        Route::get('/inventory-movements', fn () => Inertia::render('Warehouse/InventoryMovement/Index'))
            ->middleware('permission:kho.xem');
        Route::get('/orders', fn () => Inertia::render('Warehouse/Order/Index'))
            ->middleware('permission:phieu_kho.xem');
        Route::get('/slips', fn () => Inertia::render('Warehouse/Slip/Index'))
            ->middleware('permission:phieu_kho.xem');

        // Tao phieu kho tu don mua hoac don ban.
        Route::get('/slips/purchasecreate', fn () => Inertia::render('Warehouse/Slip/Purchasecreate'))
            ->middleware('permission:phieu_kho.them');
        Route::get('/slips/salecreate', fn () => Inertia::render('Warehouse/Slip/Salecreate'))
            ->middleware('permission:phieu_kho.them');
    });

    /*
    |----------------------------------------------------------------------
    | Purchase flow: nha cung cap -> san pham mua -> don mua -> nhap kho
    |----------------------------------------------------------------------
    */
    Route::prefix('purchase')->group(function () {
        Route::get('/', fn () => Inertia::render('Purchase/Dashboard'))
            ->middleware('permission:don_mua.xem');
        Route::get('/suppliers', fn () => Inertia::render('Purchase/Supplier/Index'))
            ->middleware('permission:nha_cung_cap.xem');
        Route::get('/categories', fn () => Inertia::render('Purchase/Category/Index'))
            ->middleware('permission:danh_muc_mua_hang.xem');
        Route::get('/units', fn () => Inertia::render('Purchase/Unit/Index'))
            ->middleware('permission:don_vi_mua_hang.xem');
        Route::get('/products', fn () => Inertia::render('Purchase/Product/Index'))
            ->middleware('permission:san_pham_mua_hang.xem');
        Route::get('/orders', fn () => Inertia::render('Purchase/Order/Index'))
            ->middleware('permission:don_mua.xem');
        Route::get('/orders/{id}', fn ($id) => Inertia::render('Purchase/Order/PurchaseOrderDetail', [
            'id' => (int) $id,
        ]))->middleware('permission:don_mua.xem_chi_tiet');
    });

    /*
    |----------------------------------------------------------------------
    | Sale flow: khach hang -> don ban -> xuat kho -> thu tien
    |----------------------------------------------------------------------
    */
    Route::prefix('sale')->group(function () {
        Route::get('/', fn () => Inertia::render('Sale/Dashboard'))
            ->middleware('permission:don_ban.xem');
        Route::get('/customers', fn () => Inertia::render('Sale/Customer/Index'))
            ->middleware('permission:khach_hang.xem');
        Route::get('/orders', fn () => Inertia::render('Sale/Order/Index'))
            ->middleware('permission:don_ban.xem');
        Route::get('/pos', fn () => Inertia::render('Sale/Pos/Index'))
            ->middleware('permission:don_ban.them');
    });

    /*
    |----------------------------------------------------------------------
    | Accounting flow: danh muc tai chinh -> giao dich -> cong no -> bao cao
    |----------------------------------------------------------------------
    */
    Route::prefix('accountant')->group(function () {
        Route::get('/', fn () => Inertia::render('Accountant/Dashboard'))
            ->middleware('permission:giao_dich.xem');

        // Danh muc ke toan.
        Route::get('/accounts', fn () => Inertia::render('Accountant/Account/Index'))
            ->middleware('permission:tai_khoan.xem');
        Route::get('/currencies', fn () => Inertia::render('Accountant/Currency/Index'))
            ->middleware('permission:tien_te.xem');
        Route::get('/banks', fn () => Inertia::render('Accountant/Bank/Index'))
            ->middleware('permission:ngan_hang.xem');
        Route::get('/transaction-categories', fn () => Inertia::render('Accountant/TransactionCategory/Index'))
            ->middleware('permission:loai_giao_dich.xem');

        // Giao dich, so tai khoan va bao cao.
        Route::get('/transactions', fn () => Inertia::render('Accountant/Transaction/Index'))
            ->middleware('permission:giao_dich.xem');
        Route::get('/warehouse-slips', fn () => Inertia::render('Warehouse/Slip/Index'))
            ->middleware('permission:phieu_kho.xem');
        Route::get('/account-ledgers', fn () => Inertia::render('Accountant/AccountLedger/Index'))
            ->middleware('permission:giao_dich.xem');
        Route::get('/profit-loss-report', fn () => Inertia::render('Accountant/Report/ProfitLoss'))
            ->middleware('permission:giao_dich.xem');

        // Cong no; giu ca URL so it va so nhieu de tuong thich lien ket cu.
        Route::get('/customers-debt', fn () => Inertia::render('Accountant/Customer/Index'))
            ->middleware('permission:cong_no_khach_hang.xem');
        Route::get('/customer-debts', fn () => Inertia::render('Accountant/Customer/Index'))
            ->middleware('permission:cong_no_khach_hang.xem');
        Route::get('/suppliers-debt', fn () => Inertia::render('Accountant/Supplier/Index'))
            ->middleware('permission:cong_no_nha_cung_cap.xem');
        Route::get('/supplier-debts', fn () => Inertia::render('Accountant/Supplier/Index'))
            ->middleware('permission:cong_no_nha_cung_cap.xem');
    });

    /*
    |----------------------------------------------------------------------
    | Administration flow: nhan su -> vai tro/quyen -> nhat ky
    |----------------------------------------------------------------------
    */
    Route::get('/user', fn () => Inertia::render('Manage/User'))
        ->middleware('permission:nhan_su.xem');
    Route::get('/user/{id}', fn ($id) => Inertia::render('Manage/UserDetail', [
        'id' => $id,
    ]))->middleware('permission:nhan_su.xem_chi_tiet');
    Route::get('/departments', fn () => Inertia::render('Manage/Department/Index'))
        ->middleware('permission:nhan_su.xem');
    Route::get('/positions', fn () => Inertia::render('Manage/Position/Index'))
        ->middleware('permission:nhan_su.xem');
    Route::get('/role', fn () => Inertia::render('Manage/Role'))
        ->middleware('permission:vai_tro.xem');
    Route::get('/permission', fn () => Inertia::render('Manage/Permission'))
        ->middleware('permission:quyen.xem');
    Route::get('/audit-logs', fn () => Inertia::render('AuditLog/Index'))
        ->middleware('permission:nhat_ky.xem');
});

/*
|--------------------------------------------------------------------------
| Local/staging maintenance flow
|--------------------------------------------------------------------------
|
| Khong dang ky cac endpoint van hanh nay tren production. Nguoi goi van
| phai dang nhap truoc khi chay migration hoac tao storage link.
|
*/
if (app()->isLocal() || app()->environment('staging')) {
    Route::post('/setup', function () {
        Artisan::call('migrate', ['--force' => true]);

        return 'Database migrated!';
    })->middleware('auth');

    Route::post('/setup-s', function () {
        Artisan::call('storage:link');

        return 'Storage linked!';
    })->middleware('auth');
}

/*
|--------------------------------------------------------------------------
| Account, company onboarding and application entry flow
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Tai khoan ca nhan.
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tao cong ty truoc khi vao cac man hinh chinh.
    Route::get('/company/create', [CompanyController::class, 'create'])->name('company.create');
    Route::post('/company', [CompanyController::class, 'store']);

    Route::middleware('company.created')->group(function () {
        Route::get('/', [DashboardController::class, 'landing']);
        Route::get('/dashboard', [DashboardController::class, 'landing'])->name('dashboard');
        Route::get('/guide', fn () => Inertia::render('Guide/Index'))->name('guide.index');

        // Route cu con duoc UI/linh ket hien tai su dung.
        Route::get('/products', fn () => Inertia::render('Products/Index'))
            ->name('products.index');
        Route::get('/manage/user', fn () => Inertia::render('Manage/User'))
            ->middleware('permission:nhan_su.xem')
            ->name('manage.user');
        Route::get('/users', [UserController::class, 'index']);
    });
});

/*
|--------------------------------------------------------------------------
| Legacy user page
|--------------------------------------------------------------------------
|
| Route nay trung GET /users o nhom company.created phia tren va dang duoc
| dang ky sau, nen day la handler co hieu luc hien tai. Giu nguyen thu tu de
| khong thay doi hanh vi; nen hop nhat hai route trong mot dot refactor rieng.
|
*/
Route::get('/users', fn () => Inertia::render('User/Index'))
    ->middleware('permission:nhan_su.xem');

/*
|--------------------------------------------------------------------------
| Guest authentication flow
|--------------------------------------------------------------------------
|
| Dang nhap, dang ky, quen/doi mat khau, xac minh email va OAuth.
|
*/
require __DIR__.'/auth.php';
