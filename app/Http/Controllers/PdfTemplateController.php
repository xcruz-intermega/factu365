<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\Document;
use App\Models\PdfTemplate;
use App\Services\PdfGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class PdfTemplateController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/PdfTemplates', [
            'templates' => PdfTemplate::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Settings/PdfTemplateEditor', [
            'template' => null,
            'defaultLayout' => PdfTemplate::getDefaultLayout(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'layout_json' => ['required', 'array'],
            'layout_json.blocks' => ['required', 'array'],
            'layout_json.global' => ['required', 'array'],
        ]);

        $global = $validated['layout_json']['global'] ?? [];

        PdfTemplate::create([
            'name' => $validated['name'],
            'blade_view' => 'pdf.documents.dynamic',
            'layout_json' => $validated['layout_json'],
            'settings' => [
                'primary_color' => $global['primary_color'] ?? '#1f2937',
                'accent_color' => $global['accent_color'] ?? '#4f46e5',
                'font_family' => $global['font_family'] ?? 'DejaVu Sans',
                'show_logo' => true,
                'show_qr' => true,
            ],
            'is_default' => false,
        ]);

        return redirect()->route('settings.pdf-templates')
            ->with('success', __('settings.flash_pdf_created'));
    }

    public function edit(PdfTemplate $template)
    {
        return Inertia::render('Settings/PdfTemplateEditor', [
            'template' => $template,
            'defaultLayout' => PdfTemplate::getDefaultLayout(),
        ]);
    }

    public function update(Request $request, PdfTemplate $template)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'layout_json' => ['required', 'array'],
            'layout_json.blocks' => ['required', 'array'],
            'layout_json.global' => ['required', 'array'],
        ]);

        $global = $validated['layout_json']['global'] ?? [];

        $template->update([
            'name' => $validated['name'],
            'blade_view' => 'pdf.documents.dynamic',
            'layout_json' => $validated['layout_json'],
            'settings' => [
                'primary_color' => $global['primary_color'] ?? '#1f2937',
                'accent_color' => $global['accent_color'] ?? '#4f46e5',
                'font_family' => $global['font_family'] ?? 'DejaVu Sans',
                'show_logo' => true,
                'show_qr' => true,
            ],
        ]);

        return redirect()->route('settings.pdf-templates')
            ->with('success', __('settings.flash_pdf_updated'));
    }

    public function destroy(PdfTemplate $template)
    {
        if ($template->is_default) {
            return back()->with('error', __('settings.pdf_cannot_delete_default'));
        }

        $template->delete();

        return back()->with('success', __('settings.flash_pdf_deleted'));
    }

    public function setDefault(PdfTemplate $template)
    {
        PdfTemplate::where('id', '!=', $template->id)->update(['is_default' => false]);
        $template->update(['is_default' => true]);

        return back()->with('success', __('settings.flash_pdf_default', ['name' => $template->name]));
    }

    public function preview(Request $request, PdfGeneratorService $pdfService)
    {
        $layoutJson = $request->input('layout_json');

        // Handle JSON string from form POST
        if (is_string($layoutJson)) {
            $layoutJson = json_decode($layoutJson, true);
        }

        if (! is_array($layoutJson) || empty($layoutJson['blocks']) || empty($layoutJson['global'])) {
            abort(422, 'Invalid layout JSON');
        }

        $company = CompanyProfile::first();

        $sampleData = $this->buildSampleData($company, $layoutJson);

        $pdf = $pdfService->generateFromLayout($layoutJson, $sampleData);

        return $pdf->stream('preview.pdf');
    }

    public function export(PdfTemplate $template)
    {
        $exportData = [
            'name' => $template->name,
            'layout_json' => $template->layout_json ?? $template->getLayout(),
            'settings' => $template->settings,
        ];

        $filename = 'template_' . str_replace(' ', '_', strtolower($template->name)) . '.json';

        return response()->json($exportData)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:json,txt', 'max:1024'],
        ]);

        $content = json_decode($request->file('file')->get(), true);

        if (! $content || ! isset($content['layout_json'])) {
            return back()->with('error', __('settings.pdf_import_invalid'));
        }

        PdfTemplate::create([
            'name' => ($content['name'] ?? 'Importada') . ' (importada)',
            'blade_view' => 'pdf.documents.dynamic',
            'layout_json' => $content['layout_json'],
            'settings' => $content['settings'] ?? [
                'primary_color' => $content['layout_json']['global']['primary_color'] ?? '#1f2937',
                'accent_color' => $content['layout_json']['global']['accent_color'] ?? '#4f46e5',
                'font_family' => $content['layout_json']['global']['font_family'] ?? 'DejaVu Sans',
                'show_logo' => true,
                'show_qr' => true,
            ],
            'is_default' => false,
        ]);

        return back()->with('success', __('settings.pdf_import_success'));
    }

    private function buildSampleData(?CompanyProfile $company, array $layoutJson): array
    {
        $sampleDocument = new \stdClass();
        $sampleDocument->number = 'FACT-2026-00001';
        $sampleDocument->document_type = 'invoice';
        $sampleDocument->status = 'finalized';
        $sampleDocument->issue_date = now();
        $sampleDocument->due_date = now()->addDays(30);
        $sampleDocument->operation_date = now();
        $sampleDocument->invoice_type = null;
        $sampleDocument->notes = 'Ejemplo de observaciones del documento.';
        $sampleDocument->footer_text = 'Forma de pago: Transferencia bancaria 30 días.';
        $sampleDocument->subtotal = 1500.00;
        $sampleDocument->total_discount = 50.00;
        $sampleDocument->tax_base = 1450.00;
        $sampleDocument->total_vat = 304.50;
        $sampleDocument->total_surcharge = 0;
        $sampleDocument->total_irpf = 0;
        $sampleDocument->total = 1754.50;
        $sampleDocument->correctedDocument = null;

        $sampleClient = new \stdClass();
        $sampleClient->legal_name = 'Empresa Cliente S.L.';
        $sampleClient->trade_name = '';
        $sampleClient->nif = 'B12345678';
        $sampleClient->address_street = 'Calle Ejemplo, 42';
        $sampleClient->address_postal_code = '28001';
        $sampleClient->address_city = 'Madrid';
        $sampleClient->address_province = 'Madrid';
        $sampleDocument->client = $sampleClient;

        $sampleLines = collect([
            $this->makeSampleLine('Servicio de diseño web', 'Diseño responsive completo', 1, 600.00, 0, 21),
            $this->makeSampleLine('Hosting anual', 'Plan profesional 12 meses', 1, 400.00, 0, 21),
            $this->makeSampleLine('Mantenimiento mensual', null, 5, 100.00, 10, 21),
        ]);

        $vatBreakdown = collect([
            ['vat_rate' => 21, 'base' => 1450.00, 'vat' => 304.50, 'surcharge' => 0],
        ]);

        return [
            'document' => $sampleDocument,
            'company' => $company,
            'lines' => $sampleLines,
            'vatBreakdown' => $vatBreakdown,
            'qrDataUri' => null,
            'template' => null,
            'settings' => [
                'show_qr' => true,
                'show_logo' => true,
            ],
            'typeLabel' => 'Factura',
            'statusLabel' => 'Emitida',
        ];
    }

    private function makeSampleLine(string $concept, ?string $description, float $qty, float $price, float $discount, float $vat): \stdClass
    {
        $line = new \stdClass();
        $line->concept = $concept;
        $line->description = $description;
        $line->quantity = $qty;
        $line->unit_price = $price;
        $line->unit = 'unidad';
        $line->discount_percent = $discount;
        $line->vat_rate = $vat;

        $subtotal = $qty * $price;
        $discountAmount = $subtotal * ($discount / 100);
        $base = $subtotal - $discountAmount;
        $vatAmount = $base * ($vat / 100);

        $line->line_subtotal = $base;
        $line->line_total = $base + $vatAmount;
        $line->vat_amount = $vatAmount;
        $line->surcharge_amount = 0;

        return $line;
    }
}
