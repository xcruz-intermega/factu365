<?php

use App\Http\Controllers\Auth\RegisteredTenantController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Central domain routes (factu01.local)
Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredTenantController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredTenantController::class, 'store']);
});
