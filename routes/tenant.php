<?php

declare(strict_types=1);

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EInvoiceController;
use App\Http\Controllers\FacturaEExportController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FiscalDeclarationController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterBookController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PaymentTemplateController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\TreasuryController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ProductFamilyController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\PdfTemplateController;
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
        Route::get('/products/{product}/stock-movements', [ProductController::class, 'stockMovements'])->name('products.stock-movements');
        Route::post('/products/{product}/stock-adjustment', [ProductController::class, 'stockAdjustment'])->name('products.stock-adjustment');

        // Product Families
        Route::get('/product-families', [ProductFamilyController::class, 'index'])->name('product-families.index');
        Route::post('/product-families', [ProductFamilyController::class, 'store'])->name('product-families.store');
        Route::put('/product-families/{family}', [ProductFamilyController::class, 'update'])->name('product-families.update');
        Route::delete('/product-families/{family}', [ProductFamilyController::class, 'destroy'])->name('product-families.destroy');

        // FacturaE bulk export (must be before documents/{type} to avoid capture)
        Route::get('/documents/export-facturae', [FacturaEExportController::class, 'index'])->name('documents.export-facturae');
        Route::get('/documents/export-facturae/download', [FacturaEExportController::class, 'download'])->name('documents.export-facturae.download');

        // E-Invoice import (must be before documents/{type}/{document} to avoid capture)
        Route::prefix('documents/{type}')->name('documents.')->group(function () {
            Route::get('/import', [EInvoiceController::class, 'create'])->name('import');
            Route::post('/import/preview', [EInvoiceController::class, 'preview'])->name('import.preview');
            Route::post('/import/store', [EInvoiceController::class, 'store'])->name('import.store');
        });

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
            Route::get('/{document}/facturae', [DocumentController::class, 'downloadFacturae'])->name('download-facturae');
            Route::post('/{document}/send-email', [DocumentController::class, 'sendEmail'])->name('send-email');
            Route::post('/{document}/due-dates/{dueDate}/toggle-paid', [DocumentController::class, 'markDueDatePaid'])->name('due-date.toggle-paid');
            Route::post('/{document}/toggle-accounted', [DocumentController::class, 'toggleAccounted'])->name('toggle-accounted');
        });
        Route::post('/documents/{document}/rectificative', [DocumentController::class, 'createRectificative'])->name('documents.rectificative');

        // Expenses
        Route::resource('expenses', ExpenseController::class)->except(['show']);
        Route::post('/expenses/{expense}/mark-paid', [ExpenseController::class, 'markPaid'])->name('expenses.mark-paid');
        Route::get('/expenses/{expense}/attachment', [ExpenseController::class, 'attachment'])->name('expenses.attachment');
        Route::get('/expenses/{expense}/attachment/preview', [ExpenseController::class, 'previewAttachment'])->name('expenses.attachment.preview');

        // Expense Categories
        Route::get('/expense-categories', [ExpenseCategoryController::class, 'index'])->name('expense-categories.index');
        Route::post('/expense-categories', [ExpenseCategoryController::class, 'store'])->name('expense-categories.store');
        Route::put('/expense-categories/{category}', [ExpenseCategoryController::class, 'update'])->name('expense-categories.update');
        Route::delete('/expense-categories/{category}', [ExpenseCategoryController::class, 'destroy'])->name('expense-categories.destroy');

        // Treasury
        Route::prefix('treasury')->name('treasury.')->group(function () {
            Route::get('/', [TreasuryController::class, 'overview'])->name('overview');
            Route::get('/collections', [TreasuryController::class, 'collections'])->name('collections');
            Route::get('/collections/pdf', [TreasuryController::class, 'collectionsPdf'])->name('collections.pdf');
            Route::get('/collections/csv', [TreasuryController::class, 'collectionsCsv'])->name('collections.csv');
            Route::get('/payments', [TreasuryController::class, 'payments'])->name('payments');
            Route::get('/payments/pdf', [TreasuryController::class, 'paymentsPdf'])->name('payments.pdf');
            Route::get('/payments/csv', [TreasuryController::class, 'paymentsCsv'])->name('payments.csv');
            Route::post('/entries', [TreasuryController::class, 'storeEntry'])->name('entries.store');
            Route::put('/entries/{entry}', [TreasuryController::class, 'updateEntry'])->name('entries.update');
            Route::delete('/entries/{entry}', [TreasuryController::class, 'destroyEntry'])->name('entries.destroy');
        });

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/sales/by-client', [ReportController::class, 'salesByClient'])->name('sales.by-client');
            Route::get('/sales/by-product', [ReportController::class, 'salesByProduct'])->name('sales.by-product');
            Route::get('/sales/by-period', [ReportController::class, 'salesByPeriod'])->name('sales.by-period');
            Route::get('/sales/export-csv', [ReportController::class, 'exportSalesCsv'])->name('sales.export-csv');
            Route::get('/sales/by-client/pdf', [ReportController::class, 'exportSalesByClientPdf'])->name('sales.by-client.pdf');
            Route::get('/sales/by-product/pdf', [ReportController::class, 'exportSalesByProductPdf'])->name('sales.by-product.pdf');
            Route::get('/sales/by-period/pdf', [ReportController::class, 'exportSalesByPeriodPdf'])->name('sales.by-period.pdf');
            // Register Books
            Route::get('/books/ventas', [RegisterBookController::class, 'libroVentas'])->name('books.ventas');
            Route::get('/books/ventas/pdf', [RegisterBookController::class, 'exportLibroVentasPdf'])->name('books.ventas.pdf');
            Route::get('/books/ventas/csv', [RegisterBookController::class, 'exportLibroVentasCsv'])->name('books.ventas.csv');
            Route::get('/books/compras', [RegisterBookController::class, 'libroCompras'])->name('books.compras');
            Route::get('/books/compras/pdf', [RegisterBookController::class, 'exportLibroComprasPdf'])->name('books.compras.pdf');
            Route::get('/books/compras/csv', [RegisterBookController::class, 'exportLibroComprasCsv'])->name('books.compras.csv');
            Route::get('/books/expedidas', [RegisterBookController::class, 'libroExpedidas'])->name('books.expedidas');
            Route::get('/books/expedidas/pdf', [RegisterBookController::class, 'exportLibroExpedidasPdf'])->name('books.expedidas.pdf');
            Route::get('/books/expedidas/csv', [RegisterBookController::class, 'exportLibroExpedidasCsv'])->name('books.expedidas.csv');
            Route::get('/books/expedidas/aeat-csv', [RegisterBookController::class, 'libroExpedidasAeatCsv'])->name('books.expedidas.aeat-csv');
            Route::get('/books/recibidas', [RegisterBookController::class, 'libroRecibidas'])->name('books.recibidas');
            Route::get('/books/recibidas/pdf', [RegisterBookController::class, 'exportLibroRecibidasPdf'])->name('books.recibidas.pdf');
            Route::get('/books/recibidas/csv', [RegisterBookController::class, 'exportLibroRecibidasCsv'])->name('books.recibidas.csv');
            Route::get('/books/recibidas/aeat-csv', [RegisterBookController::class, 'libroRecibidasAeatCsv'])->name('books.recibidas.aeat-csv');

            Route::get('/fiscal/modelo-303', [ReportController::class, 'modelo303'])->name('fiscal.modelo-303');
            Route::get('/fiscal/modelo-111', [FiscalDeclarationController::class, 'modelo111'])->name('fiscal.modelo-111');
            Route::get('/fiscal/modelo-115', [FiscalDeclarationController::class, 'modelo115'])->name('fiscal.modelo-115');
            Route::get('/fiscal/modelo-130', [ReportController::class, 'modelo130'])->name('fiscal.modelo-130');
            Route::get('/fiscal/modelo-347', [FiscalDeclarationController::class, 'modelo347'])->name('fiscal.modelo-347');
            Route::get('/fiscal/modelo-390', [ReportController::class, 'modelo390'])->name('fiscal.modelo-390');
            Route::get('/fiscal/modelo-303/pdf', [ReportController::class, 'exportModelo303Pdf'])->name('fiscal.modelo-303.pdf');
            Route::get('/fiscal/modelo-111/pdf', [FiscalDeclarationController::class, 'exportModelo111Pdf'])->name('fiscal.modelo-111.pdf');
            Route::get('/fiscal/modelo-115/pdf', [FiscalDeclarationController::class, 'exportModelo115Pdf'])->name('fiscal.modelo-115.pdf');
            Route::get('/fiscal/modelo-130/pdf', [ReportController::class, 'exportModelo130Pdf'])->name('fiscal.modelo-130.pdf');
            Route::get('/fiscal/modelo-347/pdf', [FiscalDeclarationController::class, 'exportModelo347Pdf'])->name('fiscal.modelo-347.pdf');
            Route::get('/fiscal/modelo-390/pdf', [ReportController::class, 'exportModelo390Pdf'])->name('fiscal.modelo-390.pdf');
            Route::get('/fiscal/modelo-303/csv', [ReportController::class, 'exportModelo303Csv'])->name('fiscal.modelo-303.csv');
            Route::get('/fiscal/modelo-111/csv', [FiscalDeclarationController::class, 'exportModelo111Csv'])->name('fiscal.modelo-111.csv');
            Route::get('/fiscal/modelo-115/csv', [FiscalDeclarationController::class, 'exportModelo115Csv'])->name('fiscal.modelo-115.csv');
            Route::get('/fiscal/modelo-130/csv', [ReportController::class, 'exportModelo130Csv'])->name('fiscal.modelo-130.csv');
            Route::get('/fiscal/modelo-347/csv', [FiscalDeclarationController::class, 'exportModelo347Csv'])->name('fiscal.modelo-347.csv');
            Route::get('/fiscal/modelo-390/csv', [ReportController::class, 'exportModelo390Csv'])->name('fiscal.modelo-390.csv');
        });

        // Settings + User management (admin+ only)
        Route::middleware('role:admin')->group(function () {
            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('/company', [SettingsController::class, 'companyProfile'])->name('company');
                Route::post('/company', [SettingsController::class, 'updateCompanyProfile'])->name('company.update');
                Route::patch('/company/verifactu', [SettingsController::class, 'updateVerifactu'])->name('company.verifactu.update');

                Route::get('/series', [SettingsController::class, 'series'])->name('series');
                Route::post('/series', [SettingsController::class, 'storeSeries'])->name('series.store');
                Route::put('/series/{series}', [SettingsController::class, 'updateSeries'])->name('series.update');
                Route::delete('/series/{series}', [SettingsController::class, 'destroySeries'])->name('series.destroy');

                Route::get('/certificates', [SettingsController::class, 'certificates'])->name('certificates');
                Route::post('/certificates', [SettingsController::class, 'uploadCertificate'])->name('certificates.upload');
                Route::post('/certificates/{certificate}/toggle', [SettingsController::class, 'toggleCertificate'])->name('certificates.toggle');
                Route::delete('/certificates/{certificate}', [SettingsController::class, 'destroyCertificate'])->name('certificates.destroy');

                Route::get('/pdf-templates', [PdfTemplateController::class, 'index'])->name('pdf-templates');
                Route::get('/pdf-templates/create', [PdfTemplateController::class, 'create'])->name('pdf-templates.create');
                Route::post('/pdf-templates', [PdfTemplateController::class, 'store'])->name('pdf-templates.store');
                Route::get('/pdf-templates/{template}/edit', [PdfTemplateController::class, 'edit'])->name('pdf-templates.edit');
                Route::put('/pdf-templates/{template}', [PdfTemplateController::class, 'update'])->name('pdf-templates.update');
                Route::delete('/pdf-templates/{template}', [PdfTemplateController::class, 'destroy'])->name('pdf-templates.destroy');
                Route::post('/pdf-templates/{template}/default', [PdfTemplateController::class, 'setDefault'])->name('pdf-templates.default');
                Route::post('/pdf-templates/preview', [PdfTemplateController::class, 'preview'])->name('pdf-templates.preview');
                Route::get('/pdf-templates/{template}/export', [PdfTemplateController::class, 'export'])->name('pdf-templates.export');
                Route::post('/pdf-templates/import', [PdfTemplateController::class, 'import'])->name('pdf-templates.import');

                Route::post('/demo-data', [SettingsController::class, 'seedDemoData'])->name('demo-data');


                Route::get('/vat-rates', [SettingsController::class, 'vatRates'])->name('vat-rates');
                Route::post('/vat-rates', [SettingsController::class, 'storeVatRate'])->name('vat-rates.store');
                Route::put('/vat-rates/{vatRate}', [SettingsController::class, 'updateVatRate'])->name('vat-rates.update');
                Route::delete('/vat-rates/{vatRate}', [SettingsController::class, 'destroyVatRate'])->name('vat-rates.destroy');

                Route::get('/bank-accounts', [BankAccountController::class, 'index'])->name('bank-accounts');
                Route::post('/bank-accounts', [BankAccountController::class, 'store'])->name('bank-accounts.store');
                Route::put('/bank-accounts/{account}', [BankAccountController::class, 'update'])->name('bank-accounts.update');
                Route::delete('/bank-accounts/{account}', [BankAccountController::class, 'destroy'])->name('bank-accounts.destroy');

                Route::get('/payment-templates', [PaymentTemplateController::class, 'index'])->name('payment-templates');
                Route::post('/payment-templates', [PaymentTemplateController::class, 'store'])->name('payment-templates.store');
                Route::put('/payment-templates/{template}', [PaymentTemplateController::class, 'update'])->name('payment-templates.update');
                Route::delete('/payment-templates/{template}', [PaymentTemplateController::class, 'destroy'])->name('payment-templates.destroy');

                Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users');
                Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
                Route::put('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
                Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');

                // Backups
                Route::get('/backups', [BackupController::class, 'index'])->name('backups');
                Route::post('/backups/create', [BackupController::class, 'create'])->name('backups.create');
                Route::post('/backups/upload', [BackupController::class, 'upload'])->name('backups.upload');
                Route::get('/backups/{filename}/download', [BackupController::class, 'download'])->name('backups.download')->where('filename', '.*');
                Route::post('/backups/restore', [BackupController::class, 'restore'])->name('backups.restore');
                Route::delete('/backups/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy')->where('filename', '.*');

                // Audit Logs
                Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs');
                Route::get('/audit-logs/export-csv', [AuditLogController::class, 'exportCsv'])->name('audit-logs.export-csv');
            });
        });
    });

    require __DIR__.'/tenant-auth.php';
});
