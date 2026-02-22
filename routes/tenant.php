<?php

declare(strict_types=1);

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PaymentTemplateController;
use App\Http\Controllers\ProductFamilyController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SetLocale;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

Route::prefix('/{tenant}')->middleware([
    'web',
    InitializeTenancyByPath::class,
    SetLocale::class,
])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware('auth')->group(function () {
        // Locale
        Route::patch('/locale', [ProfileController::class, 'updateLocale'])->name('locale.update');

        // Profile (all roles)
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Global search (all roles)
        Route::get('/search', GlobalSearchController::class)->name('global-search');

        // Clients
        Route::resource('clients', ClientController::class)->except(['show']);
        Route::post('/clients/{client}/discounts', [ClientController::class, 'storeDiscount'])->name('clients.discounts.store');
        Route::put('/clients/{client}/discounts/{discount}', [ClientController::class, 'updateDiscount'])->name('clients.discounts.update');
        Route::delete('/clients/{client}/discounts/{discount}', [ClientController::class, 'destroyDiscount'])->name('clients.discounts.destroy');

        // Products
        Route::resource('products', ProductController::class)->except(['show']);
        Route::post('/products/{product}/components', [ProductController::class, 'storeComponent'])->name('products.components.store');
        Route::delete('/products/{product}/components/{component}', [ProductController::class, 'destroyComponent'])->name('products.components.destroy');

        // Documents (type-prefixed routes)
        Route::prefix('documents/{type}')->name('documents.')->group(function () {
            Route::get('/', [DocumentController::class, 'index'])->name('index');
            Route::get('/create', [DocumentController::class, 'create'])->name('create');
            Route::post('/', [DocumentController::class, 'store'])->name('store');
            Route::get('/{document}/edit', [DocumentController::class, 'edit'])->name('edit');
            Route::put('/{document}', [DocumentController::class, 'update'])->name('update');
            Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');
            Route::post('/{document}/finalize', [DocumentController::class, 'finalize'])->name('finalize');
            Route::post('/{document}/convert', [DocumentController::class, 'convert'])->name('convert');
            Route::patch('/{document}/status', [DocumentController::class, 'updateStatus'])->name('update-status');
            Route::get('/{document}/pdf', [DocumentController::class, 'downloadPdf'])->name('download-pdf');
            Route::get('/{document}/pdf/preview', [DocumentController::class, 'previewPdf'])->name('preview-pdf');
            Route::post('/{document}/send-email', [DocumentController::class, 'sendEmail'])->name('send-email');
            Route::post('/{document}/due-dates/{dueDate}/toggle-paid', [DocumentController::class, 'markDueDatePaid'])->name('due-date.toggle-paid');
        });
        Route::post('/documents/{document}/rectificative', [DocumentController::class, 'createRectificative'])->name('documents.rectificative');

        // Expenses
        Route::resource('expenses', ExpenseController::class)->except(['show']);
        Route::post('/expenses/{expense}/mark-paid', [ExpenseController::class, 'markPaid'])->name('expenses.mark-paid');

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/sales/by-client', [ReportController::class, 'salesByClient'])->name('sales.by-client');
            Route::get('/sales/by-product', [ReportController::class, 'salesByProduct'])->name('sales.by-product');
            Route::get('/sales/by-period', [ReportController::class, 'salesByPeriod'])->name('sales.by-period');
            Route::get('/sales/export-csv', [ReportController::class, 'exportSalesCsv'])->name('sales.export-csv');
            Route::get('/fiscal/modelo-303', [ReportController::class, 'modelo303'])->name('fiscal.modelo-303');
            Route::get('/fiscal/modelo-130', [ReportController::class, 'modelo130'])->name('fiscal.modelo-130');
            Route::get('/fiscal/modelo-390', [ReportController::class, 'modelo390'])->name('fiscal.modelo-390');
        });

        // Settings + User management (admin+ only)
        Route::middleware('role:admin')->group(function () {
            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('/company', [SettingsController::class, 'companyProfile'])->name('company');
                Route::post('/company', [SettingsController::class, 'updateCompanyProfile'])->name('company.update');

                Route::get('/series', [SettingsController::class, 'series'])->name('series');
                Route::post('/series', [SettingsController::class, 'storeSeries'])->name('series.store');
                Route::put('/series/{series}', [SettingsController::class, 'updateSeries'])->name('series.update');
                Route::delete('/series/{series}', [SettingsController::class, 'destroySeries'])->name('series.destroy');

                Route::get('/certificates', [SettingsController::class, 'certificates'])->name('certificates');
                Route::post('/certificates', [SettingsController::class, 'uploadCertificate'])->name('certificates.upload');
                Route::post('/certificates/{certificate}/toggle', [SettingsController::class, 'toggleCertificate'])->name('certificates.toggle');
                Route::delete('/certificates/{certificate}', [SettingsController::class, 'destroyCertificate'])->name('certificates.destroy');

                Route::get('/pdf-templates', [SettingsController::class, 'pdfTemplates'])->name('pdf-templates');
                Route::post('/pdf-templates/{template}/default', [SettingsController::class, 'setDefaultTemplate'])->name('pdf-templates.default');

                Route::post('/demo-data', [SettingsController::class, 'seedDemoData'])->name('demo-data');

                Route::get('/product-families', [ProductFamilyController::class, 'index'])->name('product-families');
                Route::post('/product-families', [ProductFamilyController::class, 'store'])->name('product-families.store');
                Route::put('/product-families/{family}', [ProductFamilyController::class, 'update'])->name('product-families.update');
                Route::delete('/product-families/{family}', [ProductFamilyController::class, 'destroy'])->name('product-families.destroy');

                Route::get('/payment-templates', [PaymentTemplateController::class, 'index'])->name('payment-templates');
                Route::post('/payment-templates', [PaymentTemplateController::class, 'store'])->name('payment-templates.store');
                Route::put('/payment-templates/{template}', [PaymentTemplateController::class, 'update'])->name('payment-templates.update');
                Route::delete('/payment-templates/{template}', [PaymentTemplateController::class, 'destroy'])->name('payment-templates.destroy');

                Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users');
                Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
                Route::put('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
                Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
            });
        });
    });

    require __DIR__.'/tenant-auth.php';
});
