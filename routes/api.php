<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TheLoaiController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

Route::middleware('permission:product.view')->group(function () {
    Route::get('/index',  [ProductController::class, 'index']);
});
Route::middleware('permission:product.create')->group(function () {
    Route::post('/index', [ProductController::class, 'store']);
});
Route::middleware('permission:product.update')->group(function () {
    Route::put('/index/{id}', [ProductController::class, 'update']);
});
Route::middleware('permission:product.delete')->group(function () {
    Route::delete('/index/{id}', [ProductController::class, 'destroy']);
});
Route::middleware('permission:product.view')->group(function () {
    Route::get('/index/{id}', [ProductController::class, 'show']);
});


Route::get('/theloai', [TheLoaiController::class, 'index']);



// Route::get(
//     '/permissions',
//     [PermissionController::class, 'index']
// );

// Route::post(
//     '/permissions',
//     [PermissionController::class, 'store']
// );

// Route::put(
//     '/permissions/{id}',
//     [PermissionController::class, 'update']
// );

// Route::delete(
//     '/permissions/{id}',
//     [PermissionController::class, 'destroy']
// );



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
    [UserController::class, 'changeStatus']
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
