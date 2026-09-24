<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\CashShiftController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ── Public routes (no auth required) ────────────────
Route::post('/login', [AuthController::class, 'login']);
Route::post('/midtrans/webhook', [MidtransController::class, 'webhook']);

// ── Protected routes ────────────────────────────────
Route::middleware(['auth:sanctum'])->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn (Request $r) => $r->user()->load('role', 'tenant'));
    
    // Dashboard stats (semua role yang sudah login)
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Shop Settings (Admin Toko)
    Route::get('/settings', [SettingController::class, 'show'])
        ->middleware('permission:shop.settings.view');
    Route::put('/settings', [SettingController::class, 'update'])
        ->middleware('permission:shop.settings.update');
    
    // Tenants (Super Admin)
    Route::apiResource('tenants', TenantController::class)
        ->middleware('permission:platform.tenant.view');
    
    // Activity Logs (Super Admin)
    Route::get('activity-logs', [ActivityLogController::class, 'index'])
        ->middleware('permission:platform.activity_log.view');
    
    // Users (Super Admin untuk global user, Admin untuk staf toko)
    Route::apiResource('users', UserController::class)
        ->middleware('permission:platform.user.manage,staff.user.manage');
    
    // Categories — read umum, write butuh permission manage
    Route::get('categories',           [CategoryController::class, 'index'])
        ->middleware('permission:shop.category.view');
    Route::get('categories/{id}',      [CategoryController::class, 'show'])
        ->middleware('permission:shop.category.view');
    Route::middleware('permission:shop.category.manage')->group(function () {
        Route::post('categories',           [CategoryController::class, 'store']);
        Route::put('categories/{id}',       [CategoryController::class, 'update']);
        Route::delete('categories/{id}',    [CategoryController::class, 'destroy']);
    });
    
    // Products — read umum, write butuh permission manage
    Route::get('products',          [ProductController::class, 'index'])
        ->middleware('permission:shop.product.view');
    Route::get('products/{id}',     [ProductController::class, 'show'])
        ->middleware('permission:shop.product.view');
    Route::middleware('permission:shop.product.manage')->group(function () {
        Route::post('products',         [ProductController::class, 'store']);
        Route::post('products/{id}',    [ProductController::class, 'update']); // POST karena FormData
        Route::delete('products/{id}',  [ProductController::class, 'destroy']);
    });
    
    // Stock Adjustments (Inventaris)
    Route::get('stock-adjustments/summary',  [StockAdjustmentController::class, 'summary'])
        ->middleware('permission:shop.stock.view');
    Route::get('stock-adjustments',          [StockAdjustmentController::class, 'index'])
        ->middleware('permission:shop.stock.view');
    Route::post('stock-adjustments',         [StockAdjustmentController::class, 'store'])
        ->middleware('permission:shop.stock.adjust');
    
    // Cash Shifts
    Route::get('cash-shifts',              [CashShiftController::class, 'index'])
        ->middleware('permission:shop.shift.view');
    Route::post('cash-shifts',             [CashShiftController::class, 'store'])
        ->middleware('permission:shop.shift.manage');
    Route::get('cash-shifts/{id}',         [CashShiftController::class, 'show'])
        ->middleware('permission:shop.shift.view');
    Route::put('cash-shifts/{id}/close',   [CashShiftController::class, 'close'])
        ->middleware('permission:shop.shift.manage');
    
    // Orders & POS
    Route::get('orders',       [OrderController::class, 'index'])
        ->middleware('permission:shop.order.view');
    Route::post('orders',      [OrderController::class, 'store'])
        ->middleware('permission:pos.transact');
    Route::get('orders/{id}',  [OrderController::class, 'show'])
        ->middleware('permission:shop.order.view');
    
    // Return Order endpoint
    Route::get('returns',               [ReturnController::class, 'index'])
        ->middleware('permission:shop.order.view');
    Route::post('returns/process',      [ReturnController::class, 'processReturn'])
        ->middleware('permission:shop.order.delete');
    
    // Midtrans — buat token pembayaran QRIS/Transfer
    Route::post('midtrans/token', [MidtransController::class, 'createToken'])
        ->middleware('permission:shop.payment.gateway');
    
    // Order Items
    Route::get('order-items',          [OrderItemController::class, 'index'])
        ->middleware('permission:shop.order.view');
    Route::get('order-items/{id}',     [OrderItemController::class, 'show'])
        ->middleware('permission:shop.order.view');
    Route::delete('order-items/{id}',  [OrderItemController::class, 'destroy'])
        ->middleware('permission:shop.order.delete');
    
    // Reports
    Route::get('reports/profit', [ReportController::class, 'profit'])
        ->middleware('permission:shop.report.profit');
    Route::get('reports/cash-shifts', [ReportController::class, 'cashShifts'])
        ->middleware('permission:shop.report.shift');

    // Branches (Cabang Toko)
    Route::get('branches', [BranchController::class, 'index'])
        ->middleware('permission:shop.settings.view,shop.stock.view');
    Route::get('branches/{id}', [BranchController::class, 'show'])
        ->middleware('permission:shop.settings.view,shop.stock.view');
    Route::post('branches', [BranchController::class, 'store'])
        ->middleware('permission:shop.settings.update');
    Route::put('branches/{id}', [BranchController::class, 'update'])
        ->middleware('permission:shop.settings.update');
    Route::delete('branches/{id}', [BranchController::class, 'destroy'])
        ->middleware('permission:shop.settings.update');

    // Warehouses (Gudang) + Stock Transfer
    Route::get('warehouses',               [WarehouseController::class, 'index'])
        ->middleware('permission:shop.stock.view');
    Route::post('warehouses',              [WarehouseController::class, 'store'])
        ->middleware('permission:shop.stock.adjust');
    Route::get('warehouses/{id}',          [WarehouseController::class, 'show'])
        ->middleware('permission:shop.stock.view');
    Route::put('warehouses/{id}',          [WarehouseController::class, 'update'])
        ->middleware('permission:shop.stock.adjust');
    Route::delete('warehouses/{id}',       [WarehouseController::class, 'destroy'])
        ->middleware('permission:shop.stock.adjust');
    Route::get('warehouses/{id}/stocks',   [WarehouseController::class, 'stocks'])
        ->middleware('permission:shop.stock.view');
    Route::post('stock-transfers',         [WarehouseController::class, 'transfer'])
        ->middleware('permission:shop.stock.adjust');
    Route::get('stock-transfers',          [WarehouseController::class, 'transfers'])
        ->middleware('permission:shop.stock.view');
});