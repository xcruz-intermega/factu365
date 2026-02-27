<?php

namespace App\Providers;

use App\Events\InvoiceFinalized;
use App\Listeners\ProcessInvoiceFinalized;
use App\Services\MailConfigService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Event::listen(InvoiceFinalized::class, ProcessInvoiceFinalized::class);

        try {
            app(MailConfigService::class)->applyConfig();
        } catch (\Throwable) {
            // Silently fail during migrations, artisan commands, etc.
        }
    }
}
