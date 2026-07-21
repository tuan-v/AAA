<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WEB\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth'])->group(function () {
Route::prefix('/warehouse')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Warehouse/Dashboard');
    })->middleware('permission:kho.xem');
    Route::get('/list', fn() => Inertia::render('Warehouse/Index'))->middleware('permission:kho.xem');
    Route::get('/{id}/detail', function ($id) {
        return Inertia::render('Warehouse/WarehouseDetail', [
            'id' => (int) $id,
        ]);
    })->middleware('permission:kho.xem');
    Route::get('/products', function () {
        return Inertia::render('Warehouse/Product/Index');
    })->middleware('permission:san_pham_kho.xem');
    Route::get('/categories', fn() => Inertia::render('Warehouse/Category/Index'))->middleware('permission:danh_muc_kho.xem');
    Route::get('/units', fn() => Inertia::render('Warehouse/Unit/Index'))->middleware('permission:don_vi_kho.xem');

    Route::get('/transfers', fn() => Inertia::render('Warehouse/Transfer/Index'))->middleware('permission:chuyen_kho.xem');
    Route::get('/inventory-movements', fn() => Inertia::render('Warehouse/InventoryMovement/Index'))->middleware('permission:kho.xem');

    Route::get('/orders', fn() => Inertia::render('Warehouse/Order/Index'))->middleware('permission:phieu_kho.xem');

    Route::get('/slips', fn() => Inertia::render('Warehouse/Slip/Index'))->middleware('permission:phieu_kho.xem');
});
Route::get(
    '/warehouse/slips/purchasecreate',
    function () {
        return Inertia::render(
            'Warehouse/Slip/Purchasecreate'
        );
    }
)->middleware('permission:phieu_kho.them');
Route::get(
    '/warehouse/slips/salecreate',
    function () {
        return Inertia::render(
            'Warehouse/Slip/Salecreate'
        );
    }
)->middleware('permission:phieu_kho.them');
Route::prefix('purchase')->group(function () {

    Route::get('/', function () {
        return Inertia::render('Purchase/Dashboard');
    })->middleware('permission:don_mua.xem');
    Route::get('/suppliers', function () {
        return Inertia::render('Purchase/Supplier/Index');
    })->middleware('permission:nha_cung_cap.xem');
    Route::get('/categories', fn() => Inertia::render('Purchase/Category/Index'))->middleware('permission:danh_muc_mua_hang.xem');
    Route::get('/units', fn() => Inertia::render('Purchase/Unit/Index'))->middleware('permission:don_vi_mua_hang.xem');
    Route::get('/orders', function () {
        return Inertia::render('Purchase/Order/Index');
    })->middleware('permission:don_mua.xem');
    Route::get('/orders/{id}', function ($id) {
        return Inertia::render('Purchase/Order/PurchaseOrderDetail', [
            'id' => (int) $id,
        ]);
    })->middleware('permission:don_mua.xem_chi_tiet');
    Route::get('/products', function () {
        return Inertia::render('Purchase/Product/Index');
    })->middleware('permission:san_pham_mua_hang.xem');
});
Route::prefix('/sale')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Sale/Dashboard');
    })->middleware('permission:don_ban.xem');
    Route::get('/customers', function () {
        return Inertia::render('Sale/Customer/Index');
    })->middleware('permission:khach_hang.xem');
    Route::get('/orders', function () {
        return Inertia::render('Sale/Order/Index');
    })->middleware('permission:don_ban.xem');
});
Route::prefix('/accountant')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Accountant/Dashboard');
    })->middleware('permission:giao_dich.xem');
    Route::get('/accounts', function () {
        return Inertia::render('Accountant/Account/Index');
    })->middleware('permission:tai_khoan.xem');
    Route::get('/currencies', function () {
        return Inertia::render('Accountant/Currency/Index');
    })->middleware('permission:tien_te.xem');
    Route::get('/banks', function () {
        return Inertia::render('Accountant/Bank/Index');
    })->middleware('permission:ngan_hang.xem');
    Route::get('/transactions', function () {
        return Inertia::render('Accountant/Transaction/Index');
    })->middleware('permission:giao_dich.xem');
    Route::get('/customers-debt', function () {
        return Inertia::render('Accountant/Customer/Index');
    })->middleware('permission:cong_no_khach_hang.xem');
    Route::get('/customer-debts', function () {
        return Inertia::render('Accountant/Customer/Index');
    })->middleware('permission:cong_no_khach_hang.xem');
    Route::get('/suppliers-debt', function () {
        return Inertia::render('Accountant/Supplier/Index');
    })->middleware('permission:cong_no_nha_cung_cap.xem');
    Route::get('/supplier-debts', function () {
        return Inertia::render('Accountant/Supplier/Index');
    })->middleware('permission:cong_no_nha_cung_cap.xem');
    Route::get('/transaction-categories', function () {
        return Inertia::render('Accountant/TransactionCategory/Index');
    })->middleware('permission:loai_giao_dich.xem');
    Route::get('/account-ledgers', function () {
        return Inertia::render('Accountant/AccountLedger/Index');
    })->middleware('permission:giao_dich.xem');
});
Route::get('/permission', function () {
    return Inertia::render(
        'Manage/Permission'
    );
})->middleware('permission:quyen.xem');

Route::get('/role', function () {
    return Inertia::render('Manage/Role');
})->middleware('permission:vai_tro.xem');
Route::get('/audit-logs', function () {
    return Inertia::render('AuditLog/Index');
})->middleware('permission:nhat_ky.xem');

Route::get('/user', function () {
    return Inertia::render('Manage/User');
})->middleware('permission:nhan_su.xem');
Route::get('/departments', function () {
    return Inertia::render('Manage/Department/Index');
})->middleware('permission:nhan_su.xem');
Route::get('/user/{id}', function ($id) {
    return Inertia::render('Manage/UserDetail', [
        'id' => $id
    ]);
})->middleware('permission:nhan_su.xem_chi_tiet');
});



// ─── Setup routes (chỉ cho phép ở môi trường local/staging) ──────────────────
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
// ─────────────────────────────────────────────────────────────────────────────


Route::middleware(['auth'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get(
        '/company/create',
        [CompanyController::class, 'create']
    )->name('company.create');

    Route::post(
        '/company',
        [CompanyController::class, 'store']
    );

    Route::middleware([
        'auth',
        'company.created'
    ])->group(function () {
        Route::get('/', [DashboardController::class, 'landing']);
        Route::get('/dashboard', [DashboardController::class, 'landing'])->name('dashboard');
        Route::get('/products', function () {
            return Inertia::render('Products/Index');
        })->name('products.index');
        Route::get('/manage/user', function () {
            return Inertia::render('Manage/User');
        })->middleware('permission:nhan_su.xem')->name('manage.user');

        Route::get('/users', [UserController::class, 'index']);
    });
});

Route::get('/users', function () {
    return Inertia::render('User/Index');
})->middleware('permission:nhan_su.xem');

require __DIR__ . '/auth.php';
