<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\TenantInfoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AdminTenantController extends Controller
{
    public function __construct(
        private TenantInfoService $tenantInfoService,
    ) {}

    public function index()
    {
        $tenants = Tenant::orderBy('created_at', 'desc')->get();

        $tenantsData = $tenants->map(
            fn (Tenant $tenant) => $this->tenantInfoService->getSummary($tenant)
        )->values()->toArray();

        return Inertia::render('Admin/Tenants/Index', [
            'tenants' => $tenantsData,
        ]);
    }

    public function show(string $id)
    {
        $tenant = Tenant::findOrFail($id);

        return Inertia::render('Admin/Tenants/Show', [
            'tenant' => $this->tenantInfoService->getDetail($tenant),
        ]);
    }

    public function suspend(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->suspended_at = now();
        $tenant->save();

        return back()->with('success', trans('admin.tenant_suspended'));
    }

    public function unsuspend(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->suspended_at = null;
        $tenant->save();

        return back()->with('success', trans('admin.tenant_unsuspended'));
    }

    public function resetPassword(string $id, Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $tenant = Tenant::findOrFail($id);

        $tenant->run(function () use ($request) {
            DB::table('users')
                ->where('role', 'owner')
                ->update(['password' => Hash::make($request->password)]);
        });

        return back()->with('success', trans('admin.password_reset_success'));
    }

    public function destroy(string $id)
    {
        $tenant = Tenant::findOrFail($id);

        // Delete tenant storage files
        $storagePath = base_path("storage/app/private/tenant{$tenant->id}");
        if (File::isDirectory($storagePath)) {
            File::deleteDirectory($storagePath);
        }

        // Delete tenant (stancl/tenancy auto-deletes the DB via DeleteDatabase job)
        $tenant->domains()->delete();
        $tenant->delete();

        return redirect()->route('admin.dashboard')->with('success', trans('admin.tenant_deleted'));
    }
}
