<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class BackupCreate extends Command
{
    protected $signature = 'backup:create {--tenant= : Slug or UUID of a specific tenant}';

    protected $description = 'Create a backup (full or single tenant)';

    public function handle(BackupService $backupService): int
    {
        $tenantOption = $this->option('tenant');

        try {
            if ($tenantOption) {
                $tenant = \App\Models\Tenant::where('slug', $tenantOption)
                    ->orWhere('id', $tenantOption)
                    ->first();

                if (! $tenant) {
                    $this->error("Tenant '{$tenantOption}' not found.");
                    return self::FAILURE;
                }

                $this->info("Creating backup for tenant: {$tenant->slug} ({$tenant->id})...");
                $path = $backupService->createTenantBackup($tenant->id);
            } else {
                $this->info('Creating full backup (central + all tenants)...');
                $path = $backupService->createFullBackup();
            }

            $this->info("Backup created: {$path}");
            $this->info('Size: ' . $this->humanSize(filesize($path)));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Backup failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    private function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $size = (float) $bytes;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2) . ' ' . $units[$i];
    }
}
