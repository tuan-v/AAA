<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WEB\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('/warehouse')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Warehouse/Index');
    });
    Route::get('/products', function () {
        return Inertia::render('Warehouse/Product/Index');
    });
    Route::get('/categories', fn() => Inertia::render('Warehouse/Category/Index'));
    Route::get('/units', fn() => Inertia::render('Warehouse/Unit/Index'));

    Route::get('/orders', fn() => Inertia::render('Warehouse/Order/Index'));

    Route::get('/slips', fn() => Inertia::render('Warehouse/Slip/Index'));
});
Route::get(
    '/warehouse/slips/purchasecreate',
    function () {
        return Inertia::render(
            'Warehouse/Slip/Purchasecreate'
        );
    }
);
Route::get(
    '/warehouse/slips/salecreate',
    function () {
        return Inertia::render(
            'Warehouse/Slip/Salecreate'
        );
    }
);
Route::prefix('purchase')->group(function () {

    Route::get('/', function () {
        return Inertia::render('Purchase/Order/Index');
    });
    Route::get('/suppliers', function () {
        return Inertia::render('Purchase/Supplier/Index');
    });
    Route::get('/categories', fn() => Inertia::render('Purchase/Category/Index'));
    Route::get('/units', fn() => Inertia::render('Purchase/Unit/Index'));
    Route::get('/orders', function () {
        return Inertia::render('Purchase/Order/Index');
    });
    Route::get('/products', function () {
        return Inertia::render('Purchase/Product/Index');
    });
});
Route::prefix('/sale')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Sale/Customer/Index');
    });
    Route::get('/customers', function () {
        return Inertia::render('Sale/Customer/Index');
    });
    Route::get('/orders', function () {
        return Inertia::render('Sale/Order/Index');
    });
});
Route::prefix('/accountant')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Accountant/Account/Index');
    });
    Route::get('/accounts', function () {
        return Inertia::render('Accountant/Account/Index');
    });
    Route::get('/currencies', function () {
        return Inertia::render('Accountant/Currency/Index');
    });
    Route::get('/banks', function () {
        return Inertia::render('Accountant/Bank/Index');
    });
    Route::get('/transactions', function () {
        return Inertia::render('Accountant/Transaction/Index');
    });
    Route::get('/customers-debt', function () {
        return Inertia::render('Accountant/Customer/Index');
    });
    Route::get('/customer-debts', function () {
        return Inertia::render('Accountant/Customer/Index');
    });
    Route::get('/suppliers-debt', function () {
        return Inertia::render('Accountant/Supplier/Index');
    });
    Route::get('/supplier-debts', function () {
        return Inertia::render('Accountant/Supplier/Index');
    });
    Route::get('/transaction-categories', function () {
        return Inertia::render('Accountant/TransactionCategory/Index');
    });
    Route::get('/account-ledgers', function () {
        return Inertia::render('Accountant/AccountLedger/Index');
    });
});
Route::get('/permission', function () {
    return Inertia::render(
        'Manage/Permission'
    );
});

Route::get('/role', function () {
    return Inertia::render('Manage/Role');
});


Route::get('/user', function () {
    return Inertia::render('Manage/User');
});
Route::get('/user/{id}', function ($id) {
    return Inertia::render('Manage/UserDetail', [
        'id' => $id
    ]);
});



// ─── Setup routes (chỉ cho phép ở môi trường local/staging) ──────────────────
if (app()->isLocal() || app()->environment('staging')) {
    Route::get('/setup', function () {
        $request = request();
        if ($request->get('fresh')) {
            Artisan::call('migrate:fresh');
            Artisan::call('db:seed');
        } else {
            Artisan::call('migrate');
        }
        return 'Database migrated and seeded!';
    });

    Route::get('/setup-s', function () {
        Artisan::call('storage:link');
        return 'Storage linked!';
    });
}
// ─────────────────────────────────────────────────────────────────────────────


Route::middleware(['auth'])->group(function () {

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
        Route::get('/', function () {
            return Inertia::render('DashBoard');
        });
        Route::get('/dashboard', function () {
            return Inertia::render('DashBoard');
        })->name('dashboard');
        Route::get('/products', function () {
            return Inertia::render('Products/Index');
        })->name('products.index');
        Route::get('/manage/user', function () {
            return Inertia::render('Manage/User');
        })->name('manage.user');

        Route::get('/users', [UserController::class, 'index']);
    });
});

Route::get('/users', function () {
    return Inertia::render('User/Index');
})->middleware('permission:user.view');

require __DIR__ . '/auth.php';
