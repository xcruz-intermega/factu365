<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BackupController extends Controller
{
    public function __construct(
        private BackupService $backupService,
    ) {}

    public function index()
    {
        return Inertia::render('Settings/Backups', [
            'backups' => $this->backupService->listBackups(),
        ]);
    }

    public function create(Request $request)
    {
        try {
            $tenant = tenant();
            $this->backupService->createTenantBackup($tenant->id);

            return back()->with('success', __('settings.flash_backup_created'));
        } catch (\Throwable $e) {
            return back()->with('error', __('settings.error_backup_failed', ['error' => $e->getMessage()]));
        }
    }

    public function download(string $filename)
    {
        $path = $this->backupService->getBackupPath($filename);

        if (! $path) {
            return back()->with('error', __('settings.error_backup_not_found'));
        }

        return response()->download($path, $filename);
    }

    public function restore(Request $request)
    {
        // Only owner can restore
        if ($request->user()->role !== 'owner') {
            return back()->with('error', __('settings.error_restore_owner_only'));
        }

        $request->validate([
            'filename' => ['required', 'string'],
        ]);

        $filename = $request->input('filename');
        $path = $this->backupService->getBackupPath($filename);

        if (! $path) {
            return back()->with('error', __('settings.error_backup_not_found'));
        }

        try {
            $tenant = tenant();
            $this->backupService->restoreTenantBackup($path, $tenant->id);

            return back()->with('success', __('settings.flash_restore_completed'));
        } catch (\Throwable $e) {
            return back()->with('error', __('settings.error_restore_failed', ['error' => $e->getMessage()]));
        }
    }

    public function destroy(string $filename)
    {
        $deleted = $this->backupService->deleteBackup($filename);

        if (! $deleted) {
            return back()->with('error', __('settings.error_backup_not_found'));
        }

        return back()->with('success', __('settings.flash_backup_deleted'));
    }
}
