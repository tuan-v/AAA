<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TheLoaiController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\RoleController;

Route::get('/index',
[ProductController::class, 'index']);

Route::post('/index',
[ProductController::class, 'store']);

Route::get('/index/{id}',
[ProductController::class, 'show']);

Route::put('/index/{id}',
[ProductController::class, 'update']);

Route::delete('/index/{id}',
[ProductController::class, 'destroy']);

Route::get('/theloai', [TheLoaiController::class, 'index']);
Route::prefix('users')->group(function () {

    Route::get('/user', [UserController::class,'index']);

    Route::post('/user', [UserController::class,'store']);

    Route::get('/user/{id}', [UserController::class,'show']);

    Route::put('/user/{id}', [UserController::class,'update']);

    Route::delete('/{id}', [UserController::class,'destroy']);
});
// Route::apiResource(
//     'user',
//     EmployeeController::class
// );

Route::get(
    'roles',
    [RoleController::class,'index']
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

});
