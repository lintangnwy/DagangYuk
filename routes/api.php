<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CashShiftController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\SettingController;

use App\Http\Controllers\ReportController;

// ── Public ──────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

// Webhook Midtrans — public (tidak perlu auth)
Route::post('/midtrans/webhook', [MidtransController::class, 'webhook']);

// ── Protected ────────────────────────────────────────
Route::middleware(['auth:sanctum', 'check_tenant'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn (Request $r) => $r->user()->load('role', 'tenant'));

    // Dashboard stats
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Shop Settings (Tenant Admin)
    Route::get('/settings', [SettingController::class, 'show'])
        ->middleware('role:admin');
    Route::put('/settings', [SettingController::class, 'update'])
        ->middleware('role:admin');

    // Tenants
    Route::apiResource('tenants', TenantController::class)
        ->middleware('role:super_admin');

    // Users
    Route::apiResource('users', UserController::class)
        ->middleware('role:super_admin,admin');

    // Categories — read semua, write hanya admin
    Route::get('categories',           [CategoryController::class, 'index']);
    Route::get('categories/{id}',      [CategoryController::class, 'show']);
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::post('categories',           [CategoryController::class, 'store']);
        Route::put('categories/{id}',       [CategoryController::class, 'update']);
        Route::delete('categories/{id}',    [CategoryController::class, 'destroy']);
    });

    // Products — read semua, write hanya admin
    Route::get('products',          [ProductController::class, 'index']);
    Route::get('products/{id}',     [ProductController::class, 'show']);
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::post('products',         [ProductController::class, 'store']);
        Route::post('products/{id}',    [ProductController::class, 'update']); // POST karena FormData
        Route::delete('products/{id}',  [ProductController::class, 'destroy']);
    });

    // Stock Adjustments (Inventaris)
    Route::get('stock-adjustments/summary',  [StockAdjustmentController::class, 'summary']);
    Route::get('stock-adjustments',          [StockAdjustmentController::class, 'index']);
    Route::post('stock-adjustments',         [StockAdjustmentController::class, 'store'])
        ->middleware('role:super_admin,admin');

    // Cash Shifts
    Route::get('cash-shifts',              [CashShiftController::class, 'index']);
    Route::post('cash-shifts',             [CashShiftController::class, 'store']);
    Route::get('cash-shifts/{id}',         [CashShiftController::class, 'show']);
    Route::put('cash-shifts/{id}/close',   [CashShiftController::class, 'close']);

    // Orders
    Route::get('orders',       [OrderController::class, 'index']);
    Route::post('orders',      [OrderController::class, 'store']);
    Route::get('orders/{id}',  [OrderController::class, 'show']);

    // Midtrans — buat token pembayaran QRIS/Transfer
    Route::post('midtrans/token', [MidtransController::class, 'createToken']);

    // Order Items
    Route::get('order-items',          [OrderItemController::class, 'index']);
    Route::get('order-items/{id}',     [OrderItemController::class, 'show']);
    Route::delete('order-items/{id}',  [OrderItemController::class, 'destroy']);

    // Reports
    Route::get('reports/profit', [ReportController::class, 'profit']);
    Route::get('reports/cash-shifts', [ReportController::class, 'cashShifts']);
});
