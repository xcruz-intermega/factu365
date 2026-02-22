<?php

namespace App\Http\Controllers;

use App\Events\InvoiceFinalized;
use App\Http\Requests\DocumentRequest;
use App\Models\Client;
use App\Models\Document;
use App\Models\DocumentDueDate;
use App\Models\DocumentSeries;
use App\Models\PaymentTemplate;
use App\Models\PdfTemplate;
use App\Models\Product;
use App\Services\NumberingService;
use App\Services\PdfGeneratorService;
use App\Services\TaxCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function __construct(
        private TaxCalculatorService $taxCalculator,
        private NumberingService $numberingService,
        private PdfGeneratorService $pdfGenerator,
    ) {}

    public function index(Request $request, string $type)
    {
        $this->validateDocumentType($type);

        $direction = $type === Document::TYPE_PURCHASE_INVOICE ? 'received' : 'issued';

        $documents = Document::query()
            ->ofType($type)
            ->where('direction', $direction)
            ->with(['client:id,legal_name,trade_name,nif', 'series:id,prefix'])
            ->search($request->input('search'))
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->when(
                $request->input('sort'),
                fn ($q) => $q->orderBy($request->input('sort'), $request->input('dir', 'desc')),
                fn ($q) => $q->orderBy('issue_date', 'desc')->orderBy('id', 'desc')
            )
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
            'documentType' => $type,
            'documentTypeLabel' => Document::documentTypeLabel($type),
            'filters' => $request->only(['search', 'status', 'sort', 'dir']),
            'statuses' => $this->getStatusesForType($type),
        ]);
    }

    public function create(Request $request, string $type)
    {
        $this->validateDocumentType($type);

        $direction = $type === Document::TYPE_PURCHASE_INVOICE ? 'received' : 'issued';

        return Inertia::render('Documents/Create', [
            'documentType' => $type,
            'documentTypeLabel' => Document::documentTypeLabel($type),
            'direction' => $direction,
            'clients' => Client::with('discounts')->orderBy('legal_name')->get(['id', 'legal_name', 'trade_name', 'nif', 'type', 'payment_terms_days', 'payment_template_id']),
            'products' => Product::with(['components.component:id,name,reference,unit_price,vat_rate,exemption_code,irpf_applicable,unit,type,product_family_id'])->orderBy('name')->get(['id', 'name', 'reference', 'unit_price', 'vat_rate', 'exemption_code', 'irpf_applicable', 'unit', 'type', 'product_family_id']),
            'series' => DocumentSeries::forType($type)->forYear(now()->year)->get(),
            'paymentTemplates' => PaymentTemplate::with('lines')->orderBy('name')->get(),
            'parentDocument' => $request->input('from')
                ? Document::with('lines')->find($request->input('from'))
                : null,
        ]);
    }

    public function store(DocumentRequest $request, string $type)
    {
        $this->validateDocumentType($type);

        $validated = $request->validated();
        $direction = $type === Document::TYPE_PURCHASE_INVOICE ? 'received' : 'issued';
        $isNonFiscal = in_array($type, [Document::TYPE_QUOTE, Document::TYPE_DELIVERY_NOTE]);

        $document = DB::transaction(function () use ($validated, $type, $direction, $isNonFiscal) {
            // Calculate totals
            $calculated = $this->taxCalculator->calculateDocument(
                $validated['lines'],
                (float) ($validated['global_discount_percent'] ?? 0)
            );

            // Auto-number non-fiscal documents on create
            $numbering = null;
            if ($isNonFiscal) {
                $numbering = $this->numberingService->generateNumber(
                    $type,
                    $validated['series_id'] ?? null,
                );
            }

            // Create document
            $document = Document::create([
                'document_type' => $type,
                'invoice_type' => $validated['invoice_type'] ?? $this->defaultInvoiceType($type),
                'direction' => $direction,
                'status' => $isNonFiscal ? Document::STATUS_CREATED : Document::STATUS_DRAFT,
                'series_id' => $numbering ? $numbering['series_id'] : null,
                'number' => $numbering ? $numbering['number'] : null,
                'title' => $validated['title'] ?? null,
                'client_id' => $validated['client_id'] ?? null,
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'] ?? null,
                'operation_date' => $validated['operation_date'] ?? null,
                'subtotal' => $calculated['subtotal'],
                'total_discount' => $calculated['total_discount'],
                'global_discount_percent' => $validated['global_discount_percent'] ?? 0,
                'global_discount_amount' => $calculated['global_discount_amount'],
                'tax_base' => $calculated['tax_base'],
                'total_vat' => $calculated['total_vat'],
                'total_irpf' => $calculated['total_irpf'],
                'total_surcharge' => $calculated['total_surcharge'],
                'total' => $calculated['total'],
                'regime_key' => $validated['regime_key'] ?? '01',
                'notes' => $validated['notes'] ?? null,
                'footer_text' => $validated['footer_text'] ?? null,
                'corrected_document_id' => $validated['corrected_document_id'] ?? null,
                'rectificative_type' => $validated['rectificative_type'] ?? null,
            ]);

            // Create lines
            foreach ($calculated['lines'] as $index => $line) {
                $document->lines()->create([
                    'sort_order' => $index + 1,
                    'product_id' => $line['product_id'] ?? null,
                    'concept' => $line['concept'],
                    'description' => $line['description'] ?? null,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'unit' => $line['unit'] ?? null,
                    'discount_percent' => $line['discount_percent'] ?? 0,
                    'discount_amount' => $line['discount_amount'],
                    'vat_rate' => $line['vat_rate'],
                    'vat_amount' => $line['vat_amount'],
                    'exemption_code' => $line['exemption_code'] ?? null,
                    'irpf_rate' => $line['irpf_rate'] ?? 0,
                    'irpf_amount' => $line['irpf_amount'],
                    'surcharge_rate' => $line['surcharge_rate'] ?? 0,
                    'surcharge_amount' => $line['surcharge_amount'],
                    'line_subtotal' => $line['line_subtotal'],
                    'line_total' => $line['line_total'],
                ]);
            }

            // Save due dates if provided
            if (! empty($validated['due_dates'])) {
                foreach ($validated['due_dates'] as $index => $dd) {
                    $document->dueDates()->create([
                        'due_date' => $dd['due_date'],
                        'amount' => $dd['amount'],
                        'percentage' => $dd['percentage'],
                        'sort_order' => $index + 1,
                    ]);
                }
                // Sync main due_date with first entry
                $document->update(['due_date' => $validated['due_dates'][0]['due_date']]);
            }

            return $document;
        });

        return redirect()->route('documents.edit', [$type, $document])
            ->with('success', Document::documentTypeLabel($type) . ' creado correctamente.');
    }

    public function edit(string $type, Document $document)
    {
        $this->validateDocumentType($type);
        $this->ensureDocumentMatchesType($document, $type);

        $document->load(['lines' => fn ($q) => $q->orderBy('sort_order'), 'client', 'series', 'dueDates']);

        return Inertia::render('Documents/Edit', [
            'document' => $document,
            'documentType' => $type,
            'documentTypeLabel' => Document::documentTypeLabel($type),
            'clients' => Client::with('discounts')->orderBy('legal_name')->get(['id', 'legal_name', 'trade_name', 'nif', 'type', 'payment_terms_days', 'payment_template_id']),
            'products' => Product::with(['components.component:id,name,reference,unit_price,vat_rate,exemption_code,irpf_applicable,unit,type,product_family_id'])->orderBy('name')->get(['id', 'name', 'reference', 'unit_price', 'vat_rate', 'exemption_code', 'irpf_applicable', 'unit', 'type', 'product_family_id']),
            'series' => DocumentSeries::forType($type)->forYear(now()->year)->get(),
            'canFinalize' => $document->canBeFinalized(),
            'canEdit' => $document->canBeEdited(),
            'canConvert' => $document->canBeConverted(),
            'conversionTargets' => $document->conversionTargets(),
            'nextConversionType' => Document::nextConversionType($type),
            'paymentTemplates' => PaymentTemplate::with('lines')->orderBy('name')->get(),
        ]);
    }

    public function update(DocumentRequest $request, string $type, Document $document)
    {
        $this->validateDocumentType($type);
        $this->ensureDocumentMatchesType($document, $type);

        if (! $document->canBeEdited()) {
            return back()->with('error', 'Este documento no puede ser editado.');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($document, $validated) {
            // Recalculate totals
            $calculated = $this->taxCalculator->calculateDocument(
                $validated['lines'],
                (float) ($validated['global_discount_percent'] ?? 0)
            );

            // Update document
            $document->update([
                'invoice_type' => $validated['invoice_type'] ?? $document->invoice_type,
                'title' => $validated['title'] ?? $document->title,
                'client_id' => $validated['client_id'] ?? null,
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'] ?? null,
                'operation_date' => $validated['operation_date'] ?? null,
                'subtotal' => $calculated['subtotal'],
                'total_discount' => $calculated['total_discount'],
                'global_discount_percent' => $validated['global_discount_percent'] ?? 0,
                'global_discount_amount' => $calculated['global_discount_amount'],
                'tax_base' => $calculated['tax_base'],
                'total_vat' => $calculated['total_vat'],
                'total_irpf' => $calculated['total_irpf'],
                'total_surcharge' => $calculated['total_surcharge'],
                'total' => $calculated['total'],
                'regime_key' => $validated['regime_key'] ?? $document->regime_key,
                'notes' => $validated['notes'] ?? null,
                'footer_text' => $validated['footer_text'] ?? null,
                'corrected_document_id' => $validated['corrected_document_id'] ?? null,
                'rectificative_type' => $validated['rectificative_type'] ?? null,
            ]);

            // Replace lines
            $document->lines()->delete();

            foreach ($calculated['lines'] as $index => $line) {
                $document->lines()->create([
                    'sort_order' => $index + 1,
                    'product_id' => $line['product_id'] ?? null,
                    'concept' => $line['concept'],
                    'description' => $line['description'] ?? null,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'unit' => $line['unit'] ?? null,
                    'discount_percent' => $line['discount_percent'] ?? 0,
                    'discount_amount' => $line['discount_amount'],
                    'vat_rate' => $line['vat_rate'],
                    'vat_amount' => $line['vat_amount'],
                    'exemption_code' => $line['exemption_code'] ?? null,
                    'irpf_rate' => $line['irpf_rate'] ?? 0,
                    'irpf_amount' => $line['irpf_amount'],
                    'surcharge_rate' => $line['surcharge_rate'] ?? 0,
                    'surcharge_amount' => $line['surcharge_amount'],
                    'line_subtotal' => $line['line_subtotal'],
                    'line_total' => $line['line_total'],
                ]);
            }

            // Replace due dates if provided
            if (isset($validated['due_dates'])) {
                $document->dueDates()->delete();
                foreach ($validated['due_dates'] as $index => $dd) {
                    $document->dueDates()->create([
                        'due_date' => $dd['due_date'],
                        'amount' => $dd['amount'],
                        'percentage' => $dd['percentage'],
                        'sort_order' => $index + 1,
                    ]);
                }
                if (! empty($validated['due_dates'])) {
                    $document->update(['due_date' => $validated['due_dates'][0]['due_date']]);
                }
            }
        });

        return redirect()->route('documents.edit', [$type, $document])
            ->with('success', Document::documentTypeLabel($type) . ' actualizado correctamente.');
    }

    public function finalize(string $type, Document $document)
    {
        $this->validateDocumentType($type);
        $this->ensureDocumentMatchesType($document, $type);

        if ($document->isNonFiscalType()) {
            return back()->with('error', 'Los presupuestos y albaranes no requieren finalización.');
        }

        if (! $document->canBeFinalized()) {
            return back()->with('error', 'Este documento no puede ser finalizado. Verifique que tiene cliente y líneas.');
        }

        DB::transaction(function () use ($document, $type) {
            // Generate number
            $numbering = $this->numberingService->generateNumber(
                $type,
                $document->series_id,
            );

            $document->update([
                'series_id' => $numbering['series_id'],
                'number' => $numbering['number'],
                'status' => $type === Document::TYPE_PURCHASE_INVOICE
                    ? Document::STATUS_REGISTERED
                    : Document::STATUS_FINALIZED,
            ]);
        });

        // Dispatch VeriFactu event for invoices and rectificatives
        InvoiceFinalized::dispatch($document->fresh());

        return redirect()->route('documents.edit', [$type, $document])
            ->with('success', Document::documentTypeLabel($type) . ' finalizado con número ' . $document->number . '.');
    }

    public function destroy(string $type, Document $document)
    {
        $this->validateDocumentType($type);
        $this->ensureDocumentMatchesType($document, $type);

        if (! $document->canBeDeleted()) {
            return back()->with('error', 'Este documento no puede ser eliminado.');
        }

        $document->lines()->delete();
        $document->delete();

        return redirect()->route('documents.index', $type)
            ->with('success', Document::documentTypeLabel($type) . ' eliminado correctamente.');
    }

    public function convert(Request $request, string $type, Document $document)
    {
        $this->validateDocumentType($type);
        $this->ensureDocumentMatchesType($document, $type);

        if (! $document->canBeConverted()) {
            return back()->with('error', 'Este documento no puede ser convertido.');
        }

        // Accept target_type from request, default to next in chain
        $newType = $request->input('target_type') ?? Document::nextConversionType($type);
        if (! $newType || ! in_array($newType, $document->conversionTargets())) {
            return back()->with('error', 'Tipo de conversión no válido.');
        }

        $document->load('lines');
        $isTargetNonFiscal = in_array($newType, [Document::TYPE_QUOTE, Document::TYPE_DELIVERY_NOTE]);

        $newDocument = DB::transaction(function () use ($document, $newType, $isTargetNonFiscal) {
            $direction = $newType === Document::TYPE_PURCHASE_INVOICE ? 'received' : 'issued';

            // Auto-number non-fiscal targets
            $numbering = null;
            if ($isTargetNonFiscal) {
                $numbering = $this->numberingService->generateNumber($newType);
            }

            $newDocument = Document::create([
                'document_type' => $newType,
                'invoice_type' => $this->defaultInvoiceType($newType),
                'direction' => $direction,
                'status' => $isTargetNonFiscal ? Document::STATUS_CREATED : Document::STATUS_DRAFT,
                'series_id' => $numbering ? $numbering['series_id'] : null,
                'number' => $numbering ? $numbering['number'] : null,
                'client_id' => $document->client_id,
                'parent_document_id' => $document->id,
                'issue_date' => now()->toDateString(),
                'due_date' => $document->due_date,
                'subtotal' => $document->subtotal,
                'total_discount' => $document->total_discount,
                'global_discount_percent' => $document->global_discount_percent,
                'global_discount_amount' => $document->global_discount_amount,
                'tax_base' => $document->tax_base,
                'total_vat' => $document->total_vat,
                'total_irpf' => $document->total_irpf,
                'total_surcharge' => $document->total_surcharge,
                'total' => $document->total,
                'regime_key' => $document->regime_key,
                'notes' => $document->notes,
                'footer_text' => $document->footer_text,
            ]);

            foreach ($document->lines as $line) {
                $newDocument->lines()->create(
                    $line->only([
                        'product_id', 'sort_order', 'concept', 'description',
                        'quantity', 'unit_price', 'unit', 'discount_percent',
                        'discount_amount', 'vat_rate', 'vat_amount', 'exemption_code',
                        'irpf_rate', 'irpf_amount', 'surcharge_rate', 'surcharge_amount',
                        'line_subtotal', 'line_total',
                    ])
                );
            }

            // Mark origin as converted if non-fiscal
            if ($document->isNonFiscalType()) {
                $document->update(['status' => Document::STATUS_CONVERTED]);
            }

            return $newDocument;
        });

        return redirect()->route('documents.edit', [$newType, $newDocument])
            ->with('success', 'Documento convertido a ' . Document::documentTypeLabel($newType) . '.');
    }

    public function createRectificative(Document $document)
    {
        if (! $document->isInvoice() || ! $document->isFinalized()) {
            return back()->with('error', 'Solo se pueden rectificar facturas finalizadas.');
        }

        $document->load('lines');

        $newDocument = DB::transaction(function () use ($document) {
            $newDocument = Document::create([
                'document_type' => Document::TYPE_RECTIFICATIVE,
                'invoice_type' => Document::INVOICE_TYPE_R1,
                'direction' => 'issued',
                'status' => Document::STATUS_DRAFT,
                'client_id' => $document->client_id,
                'corrected_document_id' => $document->id,
                'rectificative_type' => 'substitution',
                'issue_date' => now()->toDateString(),
                'subtotal' => $document->subtotal,
                'total_discount' => $document->total_discount,
                'global_discount_percent' => $document->global_discount_percent,
                'global_discount_amount' => $document->global_discount_amount,
                'tax_base' => $document->tax_base,
                'total_vat' => $document->total_vat,
                'total_irpf' => $document->total_irpf,
                'total_surcharge' => $document->total_surcharge,
                'total' => $document->total,
                'regime_key' => $document->regime_key,
            ]);

            foreach ($document->lines as $line) {
                $newDocument->lines()->create(
                    $line->only([
                        'product_id', 'sort_order', 'concept', 'description',
                        'quantity', 'unit_price', 'unit', 'discount_percent',
                        'discount_amount', 'vat_rate', 'vat_amount', 'exemption_code',
                        'irpf_rate', 'irpf_amount', 'surcharge_rate', 'surcharge_amount',
                        'line_subtotal', 'line_total',
                    ])
                );
            }

            return $newDocument;
        });

        return redirect()->route('documents.edit', [Document::TYPE_RECTIFICATIVE, $newDocument])
            ->with('success', 'Factura rectificativa creada como borrador.');
    }

    public function updateStatus(Request $request, string $type, Document $document)
    {
        $this->validateDocumentType($type);
        $this->ensureDocumentMatchesType($document, $type);

        if ($document->isDraft()) {
            return back()->with('error', 'No se puede cambiar el estado de un borrador.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $allowedStatuses = $this->getStatusesForType($type);
        $allowedKeys = array_column($allowedStatuses, 'value');

        if (! in_array($validated['status'], $allowedKeys)) {
            return back()->with('error', 'Estado no válido.');
        }

        $document->update(['status' => $validated['status']]);

        return back()->with('success', 'Estado actualizado a ' . Document::statusLabel($validated['status']) . '.');
    }

    public function downloadPdf(Request $request, string $type, Document $document)
    {
        $this->validateDocumentType($type);
        $this->ensureDocumentMatchesType($document, $type);

        $template = $request->input('template_id')
            ? PdfTemplate::find($request->input('template_id'))
            : null;

        return $this->pdfGenerator->download($document, $template);
    }

    public function previewPdf(Request $request, string $type, Document $document)
    {
        $this->validateDocumentType($type);
        $this->ensureDocumentMatchesType($document, $type);

        $template = $request->input('template_id')
            ? PdfTemplate::find($request->input('template_id'))
            : null;

        return $this->pdfGenerator->stream($document, $template);
    }

    public function sendEmail(Request $request, string $type, Document $document)
    {
        $this->validateDocumentType($type);
        $this->ensureDocumentMatchesType($document, $type);

        if ($document->isDraft()) {
            return back()->with('error', 'No se puede enviar un documento en borrador.');
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $template = $request->input('template_id')
            ? PdfTemplate::find($request->input('template_id'))
            : null;

        $pdfContent = $this->pdfGenerator->content($document, $template);
        $typeLabel = Document::documentTypeLabel($document->document_type);
        $filename = str_replace(' ', '_', $typeLabel) . '_' . str_replace(['/', '\\'], '-', $document->number ?? $document->id) . '.pdf';

        $emailSubject = $validated['subject'] ?? "{$typeLabel} {$document->number}";
        $emailBody = $validated['message'] ?? "Adjunto encontrará el documento {$typeLabel} {$document->number}.";

        Mail::raw($emailBody, function ($mail) use ($validated, $emailSubject, $pdfContent, $filename) {
            $mail->to($validated['email'])
                ->subject($emailSubject)
                ->attachData($pdfContent, $filename, ['mime' => 'application/pdf']);
        });

        // Update status to sent if finalized
        if ($document->status === Document::STATUS_FINALIZED) {
            $document->update(['status' => Document::STATUS_SENT]);
        }

        return back()->with('success', 'Documento enviado por email a ' . $validated['email'] . '.');
    }

    public function markDueDatePaid(string $type, Document $document, DocumentDueDate $dueDate)
    {
        $this->validateDocumentType($type);
        $this->ensureDocumentMatchesType($document, $type);

        if ($dueDate->document_id !== $document->id) {
            abort(404);
        }

        $dueDate->update([
            'payment_status' => $dueDate->isPaid() ? 'pending' : 'paid',
            'payment_date' => $dueDate->isPaid() ? null : now()->toDateString(),
        ]);

        // Auto-update document status based on due dates
        $allDueDates = $document->dueDates()->get();
        if ($allDueDates->isNotEmpty()) {
            $allPaid = $allDueDates->every(fn ($dd) => $dd->payment_status === 'paid');
            $anyPaid = $allDueDates->contains(fn ($dd) => $dd->payment_status === 'paid');

            if ($allPaid) {
                $document->update(['status' => Document::STATUS_PAID]);
            } elseif ($anyPaid) {
                $document->update(['status' => Document::STATUS_PARTIAL]);
            } elseif ($document->status === Document::STATUS_PAID || $document->status === Document::STATUS_PARTIAL) {
                $document->update(['status' => Document::STATUS_FINALIZED]);
            }
        }

        return back()->with('success', $dueDate->isPaid() ? 'Vencimiento marcado como pagado.' : 'Vencimiento desmarcado.');
    }

    // -- Private helpers --

    private function validateDocumentType(string $type): void
    {
        $valid = [
            Document::TYPE_INVOICE,
            Document::TYPE_QUOTE,
            Document::TYPE_DELIVERY_NOTE,
            Document::TYPE_PROFORMA,
            Document::TYPE_RECEIPT,
            Document::TYPE_RECTIFICATIVE,
            Document::TYPE_PURCHASE_INVOICE,
        ];

        if (! in_array($type, $valid)) {
            abort(404);
        }
    }

    private function ensureDocumentMatchesType(Document $document, string $type): void
    {
        if ($document->document_type !== $type) {
            abort(404);
        }
    }

    private function defaultInvoiceType(string $documentType): ?string
    {
        return match ($documentType) {
            Document::TYPE_INVOICE => Document::INVOICE_TYPE_F1,
            Document::TYPE_RECTIFICATIVE => Document::INVOICE_TYPE_R1,
            default => null,
        };
    }

    private function getStatusesForType(string $type): array
    {
        if ($type === Document::TYPE_PURCHASE_INVOICE) {
            return [
                ['value' => Document::STATUS_DRAFT, 'label' => 'Borrador'],
                ['value' => Document::STATUS_REGISTERED, 'label' => 'Registrada'],
                ['value' => Document::STATUS_PAID, 'label' => 'Pagada'],
            ];
        }

        if ($type === Document::TYPE_QUOTE) {
            return [
                ['value' => Document::STATUS_CREATED, 'label' => 'Creado'],
                ['value' => Document::STATUS_SENT, 'label' => 'Enviado'],
                ['value' => Document::STATUS_ACCEPTED, 'label' => 'Aceptado'],
                ['value' => Document::STATUS_REJECTED, 'label' => 'Rechazado'],
                ['value' => Document::STATUS_CONVERTED, 'label' => 'Convertido'],
                ['value' => Document::STATUS_CANCELLED, 'label' => 'Anulado'],
            ];
        }

        if ($type === Document::TYPE_DELIVERY_NOTE) {
            return [
                ['value' => Document::STATUS_CREATED, 'label' => 'Creado'],
                ['value' => Document::STATUS_SENT, 'label' => 'Enviado'],
                ['value' => Document::STATUS_CONVERTED, 'label' => 'Convertido'],
                ['value' => Document::STATUS_CANCELLED, 'label' => 'Anulado'],
            ];
        }

        return [
            ['value' => Document::STATUS_DRAFT, 'label' => 'Borrador'],
            ['value' => Document::STATUS_FINALIZED, 'label' => 'Finalizada'],
            ['value' => Document::STATUS_SENT, 'label' => 'Enviada'],
            ['value' => Document::STATUS_PAID, 'label' => 'Pagada'],
            ['value' => Document::STATUS_PARTIAL, 'label' => 'Pago parcial'],
            ['value' => Document::STATUS_OVERDUE, 'label' => 'Vencida'],
            ['value' => Document::STATUS_CANCELLED, 'label' => 'Anulada'],
        ];
    }
}
