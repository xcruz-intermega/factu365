<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use PharData;
use PDO;
use Symfony\Component\Process\Process;

class BackupService
{
    private string $backupBasePath;

    public function __construct()
    {
        // Use base_path() to avoid tenancy overriding storage_path()
        $this->backupBasePath = base_path('storage/app/backups');

        if (! File::isDirectory($this->backupBasePath)) {
            File::makeDirectory($this->backupBasePath, 0755, true);
        }
    }

    /**
     * Create a full backup (central DB + all tenants + files).
     */
    public function createFullBackup(): string
    {
        $timestamp = now()->format('Y-m-d_His');
        $backupName = "backup_full_{$timestamp}";
        $tempDir = $this->backupBasePath . "/tmp_{$backupName}";

        try {
            File::makeDirectory($tempDir, 0755, true);
            File::makeDirectory("{$tempDir}/central", 0755, true);
            File::makeDirectory("{$tempDir}/tenants", 0755, true);

            // Dump central database
            $centralDb = config('database.connections.mysql.database');
            $this->dumpDatabase($centralDb, "{$tempDir}/central/{$centralDb}.sql.gz");

            // Dump each tenant
            $tenants = \App\Models\Tenant::all();
            $tenantList = [];

            foreach ($tenants as $tenant) {
                $tenantDir = "{$tempDir}/tenants/{$tenant->id}";
                File::makeDirectory($tenantDir, 0755, true);

                $tenantDb = "tenant_{$tenant->id}";
                $this->dumpDatabase($tenantDb, "{$tenantDir}/{$tenantDb}.sql.gz");

                // Copy tenant files
                $this->copyTenantFiles($tenant->id, $tenantDir);

                $tenantList[] = [
                    'id' => $tenant->id,
                    'slug' => $tenant->slug ?? null,
                    'database' => $tenantDb,
                ];
            }

            // Write manifest
            $manifest = [
                'created_at' => now()->toIso8601String(),
                'type' => 'full',
                'app_version' => config('app.version', '1.0.0'),
                'central_database' => $centralDb,
                'tenants' => $tenantList,
            ];
            File::put("{$tempDir}/manifest.json", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Create .tar.gz archive
            $archivePath = $this->createArchive($tempDir, $backupName);

            return $archivePath;
        } finally {
            // Clean up temp directory
            if (File::isDirectory($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }
    }

    /**
     * Create a backup for a single tenant.
     */
    public function createTenantBackup(string $tenantId): string
    {
        $tenant = \App\Models\Tenant::findOrFail($tenantId);
        $timestamp = now()->format('Y-m-d_His');
        $slug = $tenant->slug ?? $tenant->id;
        $backupName = "backup_tenant_{$slug}_{$timestamp}";
        $tempDir = $this->backupBasePath . "/tmp_{$backupName}";

        try {
            File::makeDirectory($tempDir, 0755, true);
            $tenantDir = "{$tempDir}/tenants/{$tenant->id}";
            File::makeDirectory($tenantDir, 0755, true);

            // Dump tenant database
            $tenantDb = "tenant_{$tenant->id}";
            $this->dumpDatabase($tenantDb, "{$tenantDir}/{$tenantDb}.sql.gz");

            // Copy tenant files
            $this->copyTenantFiles($tenant->id, $tenantDir);

            // Write manifest
            $manifest = [
                'created_at' => now()->toIso8601String(),
                'type' => 'tenant',
                'app_version' => config('app.version', '1.0.0'),
                'tenants' => [
                    [
                        'id' => $tenant->id,
                        'slug' => $tenant->slug ?? null,
                        'database' => $tenantDb,
                    ],
                ],
            ];
            File::put("{$tempDir}/manifest.json", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Create .tar.gz archive
            $archivePath = $this->createArchive($tempDir, $backupName);

            return $archivePath;
        } finally {
            if (File::isDirectory($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }
    }

    /**
     * Restore a full backup.
     */
    public function restoreFullBackup(string $archivePath, bool $runMigrations = true): array
    {
        $tempDir = $this->extractArchive($archivePath);
        $results = ['databases' => [], 'files' => [], 'migrations' => null];

        try {
            $manifest = $this->readManifest($tempDir);

            // Restore central database
            if (isset($manifest['central_database'])) {
                $centralDb = $manifest['central_database'];
                $dumpPath = "{$tempDir}/central/{$centralDb}.sql.gz";

                if (File::exists($dumpPath)) {
                    $this->restoreDatabase($centralDb, $dumpPath);
                    $results['databases'][] = $centralDb;
                }
            }

            // Restore tenant databases + files
            foreach ($manifest['tenants'] ?? [] as $tenantInfo) {
                $tenantId = $tenantInfo['id'];
                $tenantDb = $tenantInfo['database'];
                $tenantDir = "{$tempDir}/tenants/{$tenantId}";
                $dumpPath = "{$tenantDir}/{$tenantDb}.sql.gz";

                if (File::exists($dumpPath)) {
                    $this->restoreDatabase($tenantDb, $dumpPath);
                    $results['databases'][] = $tenantDb;
                }

                $this->restoreTenantFiles($tenantId, $tenantDir);
                $results['files'][] = $tenantId;
            }

            // Run migrations to level up schema
            if ($runMigrations) {
                $results['migrations'] = $this->runMigrations();
            }

            return $results;
        } finally {
            if (File::isDirectory($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }
    }

    /**
     * Restore a single tenant from a backup.
     */
    public function restoreTenantBackup(string $archivePath, string $tenantId, bool $runMigrations = true): array
    {
        $tempDir = $this->extractArchive($archivePath);
        $results = ['databases' => [], 'files' => [], 'migrations' => null];

        try {
            $manifest = $this->readManifest($tempDir);

            // Find the tenant in the manifest
            $tenantInfo = collect($manifest['tenants'] ?? [])->firstWhere('id', $tenantId);

            if (! $tenantInfo) {
                throw new \RuntimeException("Tenant '{$tenantId}' not found in backup manifest.");
            }

            $tenantDb = $tenantInfo['database'];
            $tenantDir = "{$tempDir}/tenants/{$tenantId}";
            $dumpPath = "{$tenantDir}/{$tenantDb}.sql.gz";

            if (File::exists($dumpPath)) {
                $this->restoreDatabase($tenantDb, $dumpPath);
                $results['databases'][] = $tenantDb;
            }

            $this->restoreTenantFiles($tenantId, $tenantDir);
            $results['files'][] = $tenantId;

            // Run tenant migrations only
            if ($runMigrations) {
                $results['migrations'] = $this->runTenantMigrations();
            }

            return $results;
        } finally {
            if (File::isDirectory($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }
    }

    /**
     * List all backups with their manifest data.
     */
    public function listBackups(): array
    {
        $backups = [];
        $files = File::glob("{$this->backupBasePath}/backup_*.tar.gz");

        foreach ($files as $file) {
            $filename = basename($file);
            $manifest = $this->readManifestFromArchive($file);

            $backups[] = [
                'filename' => $filename,
                'path' => $file,
                'size' => File::size($file),
                'size_human' => $this->humanFileSize(File::size($file)),
                'created_at' => $manifest['created_at'] ?? File::lastModified($file),
                'type' => $manifest['type'] ?? 'unknown',
                'tenants' => $manifest['tenants'] ?? [],
                'app_version' => $manifest['app_version'] ?? 'unknown',
            ];
        }

        // Sort by date descending
        usort($backups, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $backups;
    }

    /**
     * Delete a backup file.
     */
    public function deleteBackup(string $filename): bool
    {
        $path = $this->getBackupPath($filename);

        if (! $path) {
            return false;
        }

        return File::delete($path);
    }

    /**
     * Get the full path for a backup file (with anti-traversal validation).
     */
    public function getBackupPath(string $filename): ?string
    {
        $safeName = basename($filename);
        $path = "{$this->backupBasePath}/{$safeName}";

        // Anti-traversal: ensure resolved path is within backups directory
        $realBase = realpath($this->backupBasePath);
        $realPath = realpath($path);

        if ($realPath === false || ! str_starts_with($realPath, $realBase)) {
            return null;
        }

        if (! File::exists($path)) {
            return null;
        }

        return $path;
    }

    // ──────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────

    private function dumpDatabase(string $dbName, string $outputPath): void
    {
        $creds = $this->getMysqlCredentials();
        $pdo = $this->createPdo($dbName, $creds);

        $sqlPath = str_replace('.sql.gz', '.sql', $outputPath);
        $handle = fopen($sqlPath, 'w');

        if (! $handle) {
            throw new \RuntimeException("Cannot open {$sqlPath} for writing.");
        }

        try {
            // Header
            fwrite($handle, "-- Backup of `{$dbName}` generated " . now()->toIso8601String() . "\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");

            // Get all tables
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                // Table structure
                $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
                fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
                fwrite($handle, $createStmt['Create Table'] . ";\n\n");

                // Table data
                $rows = $pdo->query("SELECT * FROM `{$table}`");
                $rows->setFetchMode(PDO::FETCH_ASSOC);

                foreach ($rows as $row) {
                    $values = array_map(function ($value) use ($pdo) {
                        if ($value === null) {
                            return 'NULL';
                        }
                        return $pdo->quote((string) $value);
                    }, $row);

                    $columns = implode('`,`', array_keys($row));
                    $valueStr = implode(',', $values);
                    fwrite($handle, "INSERT INTO `{$table}` (`{$columns}`) VALUES ({$valueStr});\n");
                }

                fwrite($handle, "\n");
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        } finally {
            fclose($handle);
        }

        // Verify the dump is not empty
        if (File::size($sqlPath) < 50) {
            File::delete($sqlPath);
            throw new \RuntimeException("Dump produced empty output for {$dbName}.");
        }

        // Gzip the .sql file
        $gzHandle = gzopen($outputPath, 'wb9');
        $sqlHandle = fopen($sqlPath, 'rb');

        while (! feof($sqlHandle)) {
            gzwrite($gzHandle, fread($sqlHandle, 524288)); // 512KB chunks
        }

        fclose($sqlHandle);
        gzclose($gzHandle);
        File::delete($sqlPath);
    }

    private function restoreDatabase(string $dbName, string $dumpPath): void
    {
        $creds = $this->getMysqlCredentials();

        // Create database if not exists (connect without selecting a DB)
        $pdoNoDB = new PDO(
            "mysql:host={$creds['host']};port={$creds['port']}",
            $creds['user'],
            $creds['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $pdoNoDB->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}`");

        // Connect to the target database
        $pdo = $this->createPdo($dbName, $creds);

        // Decompress and read SQL
        $gzHandle = gzopen($dumpPath, 'rb');

        if (! $gzHandle) {
            throw new \RuntimeException("Cannot open dump file: {$dumpPath}");
        }

        $sql = '';
        while (! gzeof($gzHandle)) {
            $sql .= gzread($gzHandle, 524288);
        }
        gzclose($gzHandle);

        // Execute the SQL statements
        $pdo->exec($sql);
    }

    private function createPdo(string $dbName, array $creds): PDO
    {
        return new PDO(
            "mysql:host={$creds['host']};port={$creds['port']};dbname={$dbName};charset=utf8mb4",
            $creds['user'],
            $creds['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
            ]
        );
    }

    private function copyTenantFiles(string $tenantId, string $targetDir): void
    {
        $filesDir = "{$targetDir}/files";
        $sourcePath = base_path("storage/app/private/tenant{$tenantId}");

        if (File::isDirectory($sourcePath)) {
            File::makeDirectory($filesDir, 0755, true);
            File::copyDirectory($sourcePath, $filesDir);
        }
    }

    private function restoreTenantFiles(string $tenantId, string $tenantDir): void
    {
        $filesDir = "{$tenantDir}/files";
        $targetPath = base_path("storage/app/private/tenant{$tenantId}");

        if (File::isDirectory($filesDir)) {
            if (! File::isDirectory($targetPath)) {
                File::makeDirectory($targetPath, 0755, true);
            }
            File::copyDirectory($filesDir, $targetPath);
        }
    }

    private function createArchive(string $sourceDir, string $archiveName): string
    {
        $tarPath = "{$this->backupBasePath}/{$archiveName}.tar";
        $gzPath = "{$tarPath}.gz";

        // Remove previous if exists
        if (File::exists($tarPath)) {
            File::delete($tarPath);
        }
        if (File::exists($gzPath)) {
            File::delete($gzPath);
        }

        $phar = new PharData($tarPath);
        $phar->buildFromDirectory($sourceDir);
        $phar->compress(\Phar::GZ);

        // PharData::compress creates the .tar.gz and leaves the .tar
        if (File::exists($tarPath)) {
            File::delete($tarPath);
        }

        return $gzPath;
    }

    private function extractArchive(string $archivePath): string
    {
        $tempDir = $this->backupBasePath . '/tmp_restore_' . uniqid();
        File::makeDirectory($tempDir, 0755, true);

        $phar = new PharData($archivePath);
        $phar->decompress(); // creates .tar from .tar.gz

        $tarPath = str_replace('.tar.gz', '.tar', $archivePath);
        $pharTar = new PharData($tarPath);
        $pharTar->extractTo($tempDir);

        // Clean up the intermediate .tar
        if (File::exists($tarPath)) {
            File::delete($tarPath);
        }

        return $tempDir;
    }

    private function readManifest(string $dir): array
    {
        $manifestPath = "{$dir}/manifest.json";

        if (! File::exists($manifestPath)) {
            throw new \RuntimeException('Backup manifest not found.');
        }

        return json_decode(File::get($manifestPath), true);
    }

    private function readManifestFromArchive(string $archivePath): array
    {
        try {
            $phar = new PharData($archivePath);
            if (isset($phar['manifest.json'])) {
                return json_decode($phar['manifest.json']->getContent(), true) ?? [];
            }
        } catch (\Throwable $e) {
            Log::warning("Could not read manifest from {$archivePath}: " . $e->getMessage());
        }

        return [];
    }

    private function runMigrations(): array
    {
        $results = [];

        // Central migrations
        $process = new Process(['php', base_path('artisan'), 'migrate', '--force']);
        $process->setTimeout(120);
        $process->run();
        $results['central'] = $process->isSuccessful();

        // Tenant migrations
        $results['tenants'] = $this->runTenantMigrations()['tenants'] ?? false;

        return $results;
    }

    private function runTenantMigrations(): array
    {
        $process = new Process(['php', base_path('artisan'), 'tenants:migrate', '--force']);
        $process->setTimeout(300);
        $process->run();

        return ['tenants' => $process->isSuccessful()];
    }

    private function getMysqlCredentials(): array
    {
        return [
            'host' => config('database.connections.mysql.host', '127.0.0.1'),
            'port' => config('database.connections.mysql.port', '3306'),
            'user' => config('database.connections.mysql.username', 'root'),
            'password' => config('database.connections.mysql.password', ''),
        ];
    }

    private function humanFileSize(int $bytes): string
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
