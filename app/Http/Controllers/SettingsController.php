<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CompanyProfile;
use App\Models\Document;
use App\Models\DocumentSeries;
use App\Models\PdfTemplate;
use App\Services\VeriFactu\CertificateManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        ]);

        $company = CompanyProfile::first();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            if ($company?->logo_path) {
                Storage::disk('private')->delete($company->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('logos', 'private');
        }
        unset($validated['logo']);

        if ($company) {
            $company->update($validated);
        } else {
            CompanyProfile::create($validated);
        }

        return back()->with('success', 'Datos de empresa actualizados.');
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

        return back()->with('success', 'Serie creada correctamente.');
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

        return back()->with('success', 'Serie actualizada.');
    }

    public function destroySeries(DocumentSeries $series)
    {
        if ($series->documents()->exists()) {
            return back()->with('error', 'No se puede eliminar una serie con documentos asociados.');
        }

        $series->delete();

        return back()->with('success', 'Serie eliminada.');
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
            return back()->with('error', 'Error al procesar el certificado: ' . $e->getMessage());
        }

        return back()->with('success', 'Certificado cargado correctamente.');
    }

    public function toggleCertificate(Certificate $certificate)
    {
        if (! $certificate->is_active) {
            // Deactivate others first
            Certificate::where('id', '!=', $certificate->id)->update(['is_active' => false]);
        }

        $certificate->update(['is_active' => ! $certificate->is_active]);

        return back()->with('success', $certificate->is_active ? 'Certificado activado.' : 'Certificado desactivado.');
    }

    public function destroyCertificate(Certificate $certificate)
    {
        if ($certificate->pfx_path) {
            Storage::disk('private')->delete($certificate->pfx_path);
        }

        $certificate->delete();

        return back()->with('success', 'Certificado eliminado.');
    }

    // ─── PDF Templates ───

    public function pdfTemplates()
    {
        return Inertia::render('Settings/PdfTemplates', [
            'templates' => PdfTemplate::all(),
        ]);
    }

    public function setDefaultTemplate(PdfTemplate $template)
    {
        PdfTemplate::where('id', '!=', $template->id)->update(['is_default' => false]);
        $template->update(['is_default' => true]);

        return back()->with('success', 'Plantilla "' . $template->name . '" establecida como predeterminada.');
    }

    // ─── Demo Data ───

    public function seedDemoData(): \Illuminate\Http\RedirectResponse
    {
        $seeder = new \Database\Seeders\DemoDataSeeder();
        $seeder->run();

        return back()->with('success', 'Datos de prueba generados correctamente.');
    }

    // ─── Helpers ───

    private function documentTypeOptions(): array
    {
        return [
            ['value' => Document::TYPE_INVOICE, 'label' => 'Factura'],
            ['value' => Document::TYPE_QUOTE, 'label' => 'Presupuesto'],
            ['value' => Document::TYPE_DELIVERY_NOTE, 'label' => 'Albarán'],
            ['value' => Document::TYPE_RECTIFICATIVE, 'label' => 'Rectificativa'],
            ['value' => Document::TYPE_PROFORMA, 'label' => 'Proforma'],
            ['value' => Document::TYPE_RECEIPT, 'label' => 'Recibo'],
            ['value' => Document::TYPE_PURCHASE_INVOICE, 'label' => 'Factura recibida'],
        ];
    }
}
