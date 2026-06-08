<?php

use App\Http\Controllers\WEB\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/index', function () {
    return Inertia::render('Products/Index');
});

Route::get('/create', function () {

    return Inertia::render('createProduct');
});
Route::get('/product-detail/{id}', function ($id) {
    return Inertia::render('ProductDetail', [
        'id' => $id
    ]);
});
Route::get('/product-update/{id}', function ($id) {
    return Inertia::render('EditProduct', [
        'id' => $id
    ]);
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
Route::get('/users', function () {
    return Inertia::render('User/Index');
})->middleware('permission:user.view');

require __DIR__ . '/auth.php';
