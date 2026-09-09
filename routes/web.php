<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

Route::apiResource('categories', CategoryController::class);


Route::resource('categories', CategoryController::class);
use App\Http\Controllers\ProductController;

Route::resource('products', ProductController::class);

use App\Http\Controllers\TenantController;

Route::resource('tenants', TenantController::class);

Route::resource('users', UserController::class);