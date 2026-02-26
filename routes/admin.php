<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminTenantController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    // Auth (no middleware)
    Route::get('/login', [AdminAuthController::class, 'create'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'store'])->name('admin.login.store');
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('admin.logout');

    // Protected routes
    Route::middleware('super_admin')->group(function () {
        Route::get('/dashboard', [AdminTenantController::class, 'index'])->name('admin.dashboard');
        Route::get('/tenants/{tenant}', [AdminTenantController::class, 'show'])->name('admin.tenants.show');
        Route::post('/tenants/{tenant}/suspend', [AdminTenantController::class, 'suspend'])->name('admin.tenants.suspend');
        Route::post('/tenants/{tenant}/unsuspend', [AdminTenantController::class, 'unsuspend'])->name('admin.tenants.unsuspend');
        Route::post('/tenants/{tenant}/reset-password', [AdminTenantController::class, 'resetPassword'])->name('admin.tenants.reset-password');
        Route::delete('/tenants/{tenant}', [AdminTenantController::class, 'destroy'])->name('admin.tenants.destroy');
    });
});
