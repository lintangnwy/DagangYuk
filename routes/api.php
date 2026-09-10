<?php

use Illuminate\Http\Request;
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

Route::get('order-items', [OrderItemController::class, 'index']);
Route::get('order-items/{id}', [OrderItemController::class, 'show']);
Route::delete('order-items/{id}', [OrderItemController::class, 'destroy']);
});