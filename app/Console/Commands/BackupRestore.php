<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class BackupRestore extends Command
{
    protected $signature = 'backup:restore
        {file : Backup filename or full path}
        {--tenant= : Restore only this tenant (UUID)}
        {--skip-migrations : Do not run migrations after restoring}
        {--force : Skip confirmation prompt}';

    protected $description = 'Restore a backup from file';

    public function handle(BackupService $backupService): int
    {
        $file = $this->argument('file');
        $tenantId = $this->option('tenant');
        $skipMigrations = (bool) $this->option('skip-migrations');
        $force = (bool) $this->option('force');

        // Resolve file path
        $path = $backupService->getBackupPath($file);

        if (! $path && file_exists($file)) {
            $path = $file;
        }

        if (! $path) {
            $this->error("Backup file not found: {$file}");
            return self::FAILURE;
        }

        // Confirmation
        if (! $force) {
            $scope = $tenantId ? "tenant '{$tenantId}'" : 'ALL databases (central + tenants)';
            $this->warn("This will overwrite {$scope} with data from the backup.");

            if (! $this->confirm('Are you sure you want to continue?')) {
                $this->info('Restore cancelled.');
                return self::SUCCESS;
            }
        }

        try {
            $this->info("Restoring from: {$path}...");

            if ($tenantId) {
                $results = $backupService->restoreTenantBackup($path, $tenantId, ! $skipMigrations);
            } else {
                $results = $backupService->restoreFullBackup($path, ! $skipMigrations);
            }

            $this->info('Databases restored: ' . implode(', ', $results['databases']));

            if (! empty($results['files'])) {
                $this->info('Tenant files restored: ' . implode(', ', $results['files']));
            }

            if ($results['migrations']) {
                $this->info('Migrations executed.');
            }

            $this->info('Restore completed successfully.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Restore failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
