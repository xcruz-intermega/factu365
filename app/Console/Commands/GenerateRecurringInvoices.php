<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\RecurringInvoiceService;
use Illuminate\Console\Command;

class GenerateRecurringInvoices extends Command
{
    protected $signature = 'invoices:generate-recurring {--tenant= : Process only a specific tenant ID}';

    protected $description = 'Generate pending recurring invoices for all (or one) tenant(s)';

    public function handle(): int
    {
        $tenantId = $this->option('tenant');

        $query = Tenant::whereNull('suspended_at');
        if ($tenantId) {
            $query->where('id', $tenantId);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->info('No active tenants found.');

            return self::SUCCESS;
        }

        $totalGenerated = 0;
        $totalErrors = 0;

        foreach ($tenants as $tenant) {
            $this->info("Processing tenant: {$tenant->slug} ({$tenant->id})");

            $tenant->run(function () use (&$totalGenerated, &$totalErrors) {
                $service = app(RecurringInvoiceService::class);
                $results = $service->generatePendingForTenant();

                foreach ($results as $result) {
                    if ($result['status'] === 'success') {
                        $totalGenerated++;
                        $this->line("  Generated document #{$result['document_id']} from template #{$result['template_id']}");
                    } else {
                        $totalErrors++;
                        $this->warn("  Error on template #{$result['template_id']}: {$result['error']}");
                    }
                }

                if (empty($results)) {
                    $this->line('  No pending recurring invoices.');
                }
            });
        }

        $this->newLine();
        $this->info("Done. Generated: {$totalGenerated}, Errors: {$totalErrors}");

        return $totalErrors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
