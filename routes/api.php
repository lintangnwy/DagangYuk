<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CashShiftController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;

Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('user', function () {
    return auth()->user();
});

Route::apiResource('tenants', TenantController::class);

Route::apiResource('users', UserController::class);

Route::apiResource('categories', CategoryController::class);

Route::apiResource('products', ProductController::class);

Route::get('cash-shifts', [CashShiftController::class, 'index']);
Route::post('cash-shifts', [CashShiftController::class, 'store']);
Route::get('cash-shifts/{id}', [CashShiftController::class, 'show']);
Route::put('cash-shifts/{id}/close', [CashShiftController::class, 'close']);

Route::get('orders', [OrderController::class, 'index']);
Route::post('orders', [OrderController::class, 'store']);
Route::get('orders/{id}', [OrderController::class, 'show']);

Route::get('order-items', [OrderItemController::class, 'index']);
Route::get('order-items/{id}', [OrderItemController::class, 'show']);
Route::delete('order-items/{id}', [OrderItemController::class, 'destroy']);