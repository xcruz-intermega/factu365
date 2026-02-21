<?php

namespace Tests\Traits;

use App\Models\Tenant;
use Illuminate\Support\Facades\URL;

/**
 * Trait for feature tests that need tenant context with path-based routing.
 *
 * Disables DB/cache/filesystem bootstrappers so tests keep using the
 * single SQLite :memory: database while the InitializeTenancyByPath
 * middleware resolves the tenant from the URL.
 */
trait WithTenancy
{
    protected Tenant $tenant;

    protected string $tenantSlug = 'test';

    protected function setUpWithTenancy(): void
    {
        // Disable bootstrappers so tenancy init doesn't switch databases
        config(['tenancy.bootstrappers' => []]);

        // Create or find tenant record without triggering DB creation events
        $this->tenant = Tenant::withoutEvents(function () {
            return Tenant::firstOrCreate(
                ['slug' => $this->tenantSlug],
                [
                    'id' => 'test-uuid',
                    'name' => 'Test Company',
                    'email' => 'test@example.com',
                ]
            );
        });

        // Set URL defaults so route() includes the tenant slug
        URL::defaults(['tenant' => $this->tenantSlug]);
    }

    /**
     * Prefix a path with the tenant slug.
     */
    protected function tenantUrl(string $path = '/'): string
    {
        return '/' . $this->tenantSlug . $path;
    }
}
