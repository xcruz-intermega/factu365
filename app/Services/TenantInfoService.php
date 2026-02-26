<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TenantInfoService
{
    public function getSummary(Tenant $tenant): array
    {
        $data = [
            'id' => $tenant->id,
            'slug' => $tenant->slug,
            'name' => $tenant->name,
            'email' => $tenant->email,
            'plan_id' => $tenant->plan_id,
            'trial_ends_at' => $tenant->trial_ends_at?->toISOString(),
            'created_at' => $tenant->created_at?->toISOString(),
            'suspended_at' => $tenant->suspended_at?->toISOString(),
            'company' => null,
            'owner' => null,
            'users_count' => 0,
            'invoices_count' => 0,
            'disk_usage' => 0,
            'disk_usage_human' => '0 B',
            'last_backup_date' => null,
            'backups_count' => 0,
            'backups_total_size' => 0,
            'backups_total_size_human' => '0 B',
        ];

        try {
            $tenant->run(function () use (&$data) {
                // Company profile
                $company = DB::table('company_profiles')->first();
                if ($company) {
                    $data['company'] = [
                        'legal_name' => $company->legal_name,
                        'trade_name' => $company->trade_name,
                        'nif' => $company->nif,
                        'city' => $company->address_city,
                        'verifactu_enabled' => (bool) $company->verifactu_enabled,
                        'verifactu_environment' => $company->verifactu_environment,
                    ];
                }

                // Owner
                $owner = DB::table('users')->where('role', 'owner')->first();
                if ($owner) {
                    $data['owner'] = [
                        'name' => $owner->name,
                        'email' => $owner->email,
                    ];
                }

                // Counts
                $data['users_count'] = DB::table('users')->count();
                $data['invoices_count'] = DB::table('documents')
                    ->where('document_type', 'invoice')
                    ->where('direction', 'issued')
                    ->count();
            });
        } catch (\Exception $e) {
            // DB might not exist or be corrupted
        }

        // Disk usage — tenancy uses storage/tenant{uuid}/ (suffix_storage_path)
        $storagePath = base_path("storage/tenant{$tenant->id}");
        if (File::isDirectory($storagePath)) {
            $size = $this->getDirectorySize($storagePath);
            $data['disk_usage'] = $size;
            $data['disk_usage_human'] = $this->humanFileSize($size);
        }

        // Backups
        $backupInfo = $this->getBackupInfo($tenant->id);
        $data['last_backup_date'] = $backupInfo['last_date'];
        $data['backups_count'] = $backupInfo['count'];
        $data['backups_total_size'] = $backupInfo['total_size'];
        $data['backups_total_size_human'] = $this->humanFileSize($backupInfo['total_size']);

        return $data;
    }

    public function getDetail(Tenant $tenant): array
    {
        $data = $this->getSummary($tenant);

        $data['clients_count'] = 0;
        $data['products_count'] = 0;
        $data['expenses_count'] = 0;
        $data['documents_breakdown'] = [];
        $data['revenue_this_year'] = 0;
        $data['users'] = [];
        $data['last_document_date'] = null;
        $data['company_full'] = null;
        $data['backups'] = $this->getBackupInfo($tenant->id)['list'];

        try {
            $tenant->run(function () use (&$data) {
                // Full company profile
                $company = DB::table('company_profiles')->first();
                if ($company) {
                    $data['company_full'] = [
                        'legal_name' => $company->legal_name,
                        'trade_name' => $company->trade_name,
                        'nif' => $company->nif,
                        'address_street' => $company->address_street,
                        'address_city' => $company->address_city,
                        'address_postal_code' => $company->address_postal_code,
                        'address_province' => $company->address_province,
                        'address_country' => $company->address_country,
                        'phone' => $company->phone,
                        'email' => $company->email,
                        'website' => $company->website,
                        'tax_regime' => $company->tax_regime,
                        'irpf_rate' => $company->irpf_rate,
                        'verifactu_enabled' => (bool) $company->verifactu_enabled,
                        'verifactu_environment' => $company->verifactu_environment,
                    ];
                }

                // Counts
                $data['clients_count'] = DB::table('clients')->whereNull('deleted_at')->count();
                $data['products_count'] = DB::table('products')->whereNull('deleted_at')->count();
                $data['expenses_count'] = DB::table('expenses')->count();

                // Documents breakdown
                $breakdown = DB::table('documents')
                    ->select('document_type', DB::raw('COUNT(*) as count'))
                    ->where('direction', 'issued')
                    ->groupBy('document_type')
                    ->pluck('count', 'document_type')
                    ->toArray();

                $data['documents_breakdown'] = [
                    'invoices' => $breakdown['invoice'] ?? 0,
                    'quotes' => $breakdown['quote'] ?? 0,
                    'delivery_notes' => $breakdown['delivery_note'] ?? 0,
                    'rectificatives' => $breakdown['rectificative'] ?? 0,
                    'purchase_invoices' => DB::table('documents')
                        ->where('document_type', 'purchase_invoice')
                        ->count(),
                ];

                // Revenue this year
                $data['revenue_this_year'] = (float) DB::table('documents')
                    ->where('document_type', 'invoice')
                    ->where('direction', 'issued')
                    ->where('status', '!=', 'draft')
                    ->whereYear('issue_date', now()->year)
                    ->sum('total');

                // All users
                $users = DB::table('users')->get();
                $data['users'] = $users->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->role,
                    'locale' => $u->locale ?? 'es',
                    'created_at' => $u->created_at,
                ])->toArray();

                // Last document date
                $lastDoc = DB::table('documents')
                    ->orderByDesc('issue_date')
                    ->first();
                $data['last_document_date'] = $lastDoc?->issue_date;
            });
        } catch (\Exception $e) {
            // DB might not exist or be corrupted
        }

        return $data;
    }

    private function getDirectorySize(string $path): int
    {
        $size = 0;
        foreach (File::allFiles($path) as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    private function humanFileSize(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);

        return round($bytes / pow(1024, $i), 1).' '.$units[$i];
    }

    private function getBackupInfo(string $tenantId): array
    {
        $result = [
            'count' => 0,
            'total_size' => 0,
            'last_date' => null,
            'list' => [],
        ];

        $backupDir = base_path('storage/app/backups');

        if (! File::isDirectory($backupDir)) {
            return $result;
        }

        foreach (File::files($backupDir) as $file) {
            if ($file->getExtension() !== 'gz') {
                continue;
            }

            try {
                $phar = new \PharData($file->getPathname());
                if (! isset($phar['manifest.json'])) {
                    continue;
                }

                $manifest = json_decode($phar['manifest.json']->getContent(), true);
                $tenants = $manifest['tenants'] ?? [];

                foreach ($tenants as $t) {
                    if (($t['id'] ?? '') === $tenantId) {
                        $date = $manifest['created_at'] ?? null;
                        $fileSize = $file->getSize();

                        $result['count']++;
                        $result['total_size'] += $fileSize;

                        if ($date && (! $result['last_date'] || $date > $result['last_date'])) {
                            $result['last_date'] = $date;
                        }

                        $result['list'][] = [
                            'filename' => $file->getFilename(),
                            'date' => $date,
                            'size' => $fileSize,
                            'size_human' => $this->humanFileSize($fileSize),
                            'type' => $manifest['type'] ?? 'unknown',
                        ];

                        break;
                    }
                }
            } catch (\Exception) {
                continue;
            }
        }

        // Sort list by date descending
        usort($result['list'], fn ($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));

        return $result;
    }
}
