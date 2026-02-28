<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CompanyProfile;
use App\Models\Document;
use App\Models\DocumentSeries;
use App\Models\VatRate;
use App\Services\VeriFactu\CertificateManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SettingsController extends Controller
{
    // ─── Company Profile ───

    public function companyProfile()
    {
        return Inertia::render('Settings/CompanyProfile', [
            'company' => CompanyProfile::first(),
        ]);
    }

    public function updateCompanyProfile(Request $request)
    {
        $validated = $request->validate([
            'legal_name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'nif' => ['required', 'string', 'max:20'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'address_city' => ['nullable', 'string', 'max:100'],
            'address_postal_code' => ['nullable', 'string', 'max:10'],
            'address_province' => ['nullable', 'string', 'max:100'],
            'address_country' => ['nullable', 'string', 'max:2'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'tax_regime' => ['nullable', 'string', 'max:50'],
            'irpf_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'catalog_enabled' => ['boolean'],
        ]);

        $company = CompanyProfile::first();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            if ($company?->logo_path) {
                Storage::disk('local')->delete($company->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('logos', 'local');
        }
        unset($validated['logo']);

        if ($company) {
            $company->update($validated);
        } else {
            CompanyProfile::create($validated);
        }

        return back()->with('success', __('settings.flash_company_updated'));
    }

    // ─── Document Series ───

    public function series()
    {
        return Inertia::render('Settings/Series', [
            'series' => DocumentSeries::orderBy('fiscal_year', 'desc')
                ->orderBy('document_type')
                ->get(),
            'documentTypes' => $this->documentTypeOptions(),
        ]);
    }

    public function storeSeries(Request $request)
    {
        $validated = $request->validate([
            'document_type' => ['required', 'string'],
            'prefix' => ['required', 'string', 'max:50'],
            'next_number' => ['required', 'integer', 'min:1'],
            'padding' => ['required', 'integer', 'min:1', 'max:10'],
            'fiscal_year' => ['required', 'integer', 'min:2020', 'max:2099'],
            'is_default' => ['boolean'],
        ]);

        // If setting as default, unset others
        if ($validated['is_default'] ?? false) {
            DocumentSeries::forType($validated['document_type'])
                ->forYear($validated['fiscal_year'])
                ->update(['is_default' => false]);
        }

        DocumentSeries::create($validated);

        return back()->with('success', __('settings.flash_series_created'));
    }

    public function updateSeries(Request $request, DocumentSeries $series)
    {
        $validated = $request->validate([
            'prefix' => ['required', 'string', 'max:50'],
            'next_number' => ['required', 'integer', 'min:1'],
            'padding' => ['required', 'integer', 'min:1', 'max:10'],
            'is_default' => ['boolean'],
        ]);

        if ($validated['is_default'] ?? false) {
            DocumentSeries::forType($series->document_type)
                ->forYear($series->fiscal_year)
                ->where('id', '!=', $series->id)
                ->update(['is_default' => false]);
        }

        $series->update($validated);

        return back()->with('success', __('settings.flash_series_updated'));
    }

    public function destroySeries(DocumentSeries $series)
    {
        if ($series->documents()->exists()) {
            return back()->with('error', __('settings.error_series_has_documents'));
        }

        $series->delete();

        return back()->with('success', __('settings.flash_series_deleted'));
    }

    // ─── Certificates ───

    public function certificates()
    {
        return Inertia::render('Settings/Certificates', [
            'certificates' => Certificate::orderByDesc('created_at')->get()->map(fn ($cert) => [
                'id' => $cert->id,
                'name' => $cert->name,
                'subject_cn' => $cert->subject_cn,
                'serial_number' => $cert->serial_number,
                'valid_from' => $cert->valid_from?->format('d/m/Y'),
                'valid_until' => $cert->valid_until?->format('d/m/Y'),
                'is_active' => $cert->is_active,
                'is_expired' => $cert->isExpired(),
                'is_valid' => $cert->isValid(),
            ]),
        ]);
    }

    public function uploadCertificate(Request $request, CertificateManager $certManager)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'certificate' => ['required', 'file', 'max:10240'],
            'password' => ['required', 'string'],
        ]);

        try {
            $certManager->upload(
                $request->file('certificate'),
                $request->input('password'),
                $request->input('name'),
            );
        } catch (\Throwable $e) {
            return back()->with('error', __('settings.error_cert_processing', ['error' => $e->getMessage()]));
        }

        return back()->with('success', __('settings.flash_cert_uploaded'));
    }

    public function toggleCertificate(Certificate $certificate)
    {
        if (! $certificate->is_active) {
            // Deactivate others first
            Certificate::where('id', '!=', $certificate->id)->update(['is_active' => false]);
        }

        $certificate->update(['is_active' => ! $certificate->is_active]);

        return back()->with('success', $certificate->is_active ? __('settings.flash_cert_activated') : __('settings.flash_cert_deactivated'));
    }

    public function destroyCertificate(Certificate $certificate)
    {
        if ($certificate->pfx_path) {
            Storage::disk('local')->delete($certificate->pfx_path);
        }

        $certificate->delete();

        return back()->with('success', __('settings.flash_cert_deleted'));
    }

    // ─── VeriFactu Settings ───

    public function updateVerifactu(Request $request)
    {
        $validated = $request->validate([
            'verifactu_enabled' => ['required', 'boolean'],
            'verifactu_environment' => ['required', 'string', 'in:sandbox,production'],
        ]);

        $company = CompanyProfile::first();

        // Guard: once in production, cannot revert to sandbox
        if ($company && $company->verifactu_environment === 'production' && $validated['verifactu_environment'] === 'sandbox') {
            return back()->with('error', __('settings.error_verifactu_production_irreversible'));
        }

        if ($company) {
            $company->update($validated);
        } else {
            CompanyProfile::create($validated);
        }

        return back()->with('success', __('settings.flash_verifactu_updated'));
    }

    // ─── Demo Data ───

    public function seedDemoData(): \Illuminate\Http\RedirectResponse
    {
        $seeder = new \Database\Seeders\DemoDataSeeder();
        $seeder->run();

        return back()->with('success', __('settings.flash_demo_generated'));
    }

    // ─── VAT Rates ───

    public function vatRates()
    {
        return Inertia::render('Settings/VatRates', [
            'vatRatesList' => VatRate::ordered()->get(),
        ]);
    }

    public function storeVatRate(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100', Rule::unique('vat_rates', 'rate')],
            'surcharge_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_default' => ['boolean'],
            'is_exempt' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        if ($validated['is_default'] ?? false) {
            VatRate::where('is_default', true)->update(['is_default' => false]);
        }

        VatRate::create($validated);

        return back()->with('success', __('settings.flash_vat_rate_created'));
    }

    public function updateVatRate(Request $request, VatRate $vatRate)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100', Rule::unique('vat_rates', 'rate')->ignore($vatRate->id)],
            'surcharge_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_default' => ['boolean'],
            'is_exempt' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        if ($validated['is_default'] ?? false) {
            VatRate::where('id', '!=', $vatRate->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $vatRate->update($validated);

        return back()->with('success', __('settings.flash_vat_rate_updated'));
    }

    public function destroyVatRate(VatRate $vatRate)
    {
        if ($vatRate->is_default) {
            return back()->with('error', __('settings.error_cannot_delete_default_vat_rate'));
        }

        if ($vatRate->isInUse()) {
            return back()->with('error', __('settings.error_vat_rate_in_use'));
        }

        $vatRate->delete();

        return back()->with('success', __('settings.flash_vat_rate_deleted'));
    }

    // ─── Helpers ───

    private function documentTypeOptions(): array
    {
        return [
            ['value' => Document::TYPE_INVOICE, 'label' => __('documents.type_invoice')],
            ['value' => Document::TYPE_QUOTE, 'label' => __('documents.type_quote')],
            ['value' => Document::TYPE_DELIVERY_NOTE, 'label' => __('documents.type_delivery_note')],
            ['value' => Document::TYPE_RECTIFICATIVE, 'label' => __('documents.type_rectificative')],
            ['value' => Document::TYPE_PROFORMA, 'label' => __('documents.type_proforma')],
            ['value' => Document::TYPE_RECEIPT, 'label' => __('documents.type_receipt')],
            ['value' => Document::TYPE_PURCHASE_INVOICE, 'label' => __('documents.type_purchase_invoice')],
        ];
    }
}
