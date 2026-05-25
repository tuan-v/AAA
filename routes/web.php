<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
});

require __DIR__ . '/auth.php';
