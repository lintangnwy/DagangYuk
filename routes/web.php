<?php

use Illuminate\Support\Facades\Route;

// Laravel entry point — frontend di-serve oleh Vue (Vite).
Route::get('/', function () {
    return view('welcome');
});
