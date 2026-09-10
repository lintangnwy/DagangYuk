<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CashShiftController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;

// ── Public ──────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

// ── Protected (semua user login) ─────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user',    fn (Request $r) => $r->user()->load('role', 'tenant'));

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Tenants — hanya super_admin
    Route::apiResource('tenants', TenantController::class)
        ->middleware('role:super_admin');

    // Users — hanya super_admin & admin
    Route::apiResource('users', UserController::class)
        ->middleware('role:super_admin,admin');

    // Categories — write hanya admin/super_admin, read semua
    Route::get('categories',      [CategoryController::class, 'index']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::post('categories',         [CategoryController::class, 'store']);
        Route::put('categories/{id}',     [CategoryController::class, 'update']);
        Route::delete('categories/{id}',  [CategoryController::class, 'destroy']);
    });

    // Products — read semua (untuk kasir), write hanya admin/super_admin
    Route::get('products',       [ProductController::class, 'index']);
    Route::get('products/{id}',  [ProductController::class, 'show']);
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::post('products',          [ProductController::class, 'store']);
        Route::post('products/{id}',     [ProductController::class, 'update']); // POST karena multipart/form-data
        Route::delete('products/{id}',   [ProductController::class, 'destroy']);
    });

    // Cash Shifts
    Route::get('cash-shifts',            [CashShiftController::class, 'index']);
    Route::post('cash-shifts',           [CashShiftController::class, 'store']);
    Route::get('cash-shifts/{id}',       [CashShiftController::class, 'show']);
    Route::put('cash-shifts/{id}/close', [CashShiftController::class, 'close']);

    // Orders
    Route::get('orders',      [OrderController::class, 'index']);
    Route::post('orders',     [OrderController::class, 'store']);
    Route::get('orders/{id}', [OrderController::class, 'show']);

    // Order Items
    Route::get('order-items',         [OrderItemController::class, 'index']);
    Route::get('order-items/{id}',    [OrderItemController::class, 'show']);
    Route::delete('order-items/{id}', [OrderItemController::class, 'destroy']);
});
