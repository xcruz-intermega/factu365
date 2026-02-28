<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecurringInvoiceRequest;
use App\Models\Client;
use App\Models\DocumentSeries;
use App\Models\PaymentTemplate;
use App\Models\Product;
use App\Models\RecurringInvoice;
use App\Services\RecurringInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RecurringInvoiceController extends Controller
{
    public function __construct(
        private RecurringInvoiceService $recurringService,
    ) {}

    public function index(Request $request)
    {
        $recurring = RecurringInvoice::query()
            ->with(['client:id,legal_name,trade_name,nif'])
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('client', fn ($q) => $q->where('legal_name', 'like', "%{$search}%"));
                });
            })
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('RecurringInvoices/Index', [
            'recurringInvoices' => $recurring,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('RecurringInvoices/CreateEdit', [
            'recurringInvoice' => null,
            'clients' => Client::orderBy('legal_name')->get(['id', 'legal_name', 'trade_name', 'nif', 'type', 'email', 'payment_template_id']),
            'products' => Product::orderBy('name')->get(['id', 'name', 'reference', 'unit_price', 'vat_rate', 'exemption_code', 'irpf_applicable', 'unit', 'type', 'product_family_id']),
            'series' => DocumentSeries::forType('invoice')->forYear(now()->year)->get(),
            'paymentTemplates' => PaymentTemplate::with('lines')->orderBy('name')->get(),
        ]);
    }

    public function store(RecurringInvoiceRequest $request)
    {
        $validated = $request->validated();

        $recurring = DB::transaction(function () use ($validated) {
            $recurring = RecurringInvoice::create([
                'name' => $validated['name'],
                'client_id' => $validated['client_id'],
                'series_id' => $validated['series_id'] ?? null,
                'payment_template_id' => $validated['payment_template_id'] ?? null,
                'invoice_type' => $validated['invoice_type'] ?? 'F1',
                'regime_key' => $validated['regime_key'] ?? '01',
                'global_discount_percent' => $validated['global_discount_percent'] ?? 0,
                'notes' => $validated['notes'] ?? null,
                'footer_text' => $validated['footer_text'] ?? null,
                'interval_value' => $validated['interval_value'],
                'interval_unit' => $validated['interval_unit'],
                'start_date' => $validated['start_date'],
                'next_issue_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'max_occurrences' => $validated['max_occurrences'] ?? null,
                'auto_finalize' => $validated['auto_finalize'] ?? false,
                'auto_send_email' => $validated['auto_send_email'] ?? false,
                'email_recipients' => $validated['email_recipients'] ?? null,
                'created_by' => auth()->id(),
                'status' => RecurringInvoice::STATUS_ACTIVE,
            ]);

            foreach ($validated['lines'] as $index => $line) {
                $recurring->lines()->create([
                    'sort_order' => $index + 1,
                    'product_id' => $line['product_id'] ?? null,
                    'concept' => $line['concept'],
                    'description' => $line['description'] ?? null,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'unit' => $line['unit'] ?? 'unidad',
                    'discount_percent' => $line['discount_percent'] ?? 0,
                    'vat_rate' => $line['vat_rate'],
                    'exemption_code' => $line['exemption_code'] ?? null,
                    'irpf_rate' => $line['irpf_rate'] ?? 0,
                    'surcharge_rate' => $line['surcharge_rate'] ?? 0,
                ]);
            }

            return $recurring;
        });

        return redirect()->route('recurring-invoices.show', $recurring)
            ->with('success', __('recurring.flash_created'));
    }

    public function show(RecurringInvoice $recurringInvoice)
    {
        $recurringInvoice->load([
            'client:id,legal_name,trade_name,nif,email',
            'lines' => fn ($q) => $q->orderBy('sort_order'),
            'lines.product:id,name,reference',
            'series:id,prefix',
            'paymentTemplate:id,name',
            'createdBy:id,name',
            'generatedDocuments' => fn ($q) => $q->orderBy('created_at', 'desc')->limit(50),
            'generatedDocuments.series:id,prefix',
        ]);

        return Inertia::render('RecurringInvoices/Show', [
            'recurringInvoice' => $recurringInvoice,
        ]);
    }

    public function edit(RecurringInvoice $recurringInvoice)
    {
        $recurringInvoice->load([
            'lines' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        return Inertia::render('RecurringInvoices/CreateEdit', [
            'recurringInvoice' => $recurringInvoice,
            'clients' => Client::orderBy('legal_name')->get(['id', 'legal_name', 'trade_name', 'nif', 'type', 'email', 'payment_template_id']),
            'products' => Product::orderBy('name')->get(['id', 'name', 'reference', 'unit_price', 'vat_rate', 'exemption_code', 'irpf_applicable', 'unit', 'type', 'product_family_id']),
            'series' => DocumentSeries::forType('invoice')->forYear(now()->year)->get(),
            'paymentTemplates' => PaymentTemplate::with('lines')->orderBy('name')->get(),
        ]);
    }

    public function update(RecurringInvoiceRequest $request, RecurringInvoice $recurringInvoice)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($recurringInvoice, $validated) {
            $recurringInvoice->update([
                'name' => $validated['name'],
                'client_id' => $validated['client_id'],
                'series_id' => $validated['series_id'] ?? null,
                'payment_template_id' => $validated['payment_template_id'] ?? null,
                'invoice_type' => $validated['invoice_type'] ?? 'F1',
                'regime_key' => $validated['regime_key'] ?? '01',
                'global_discount_percent' => $validated['global_discount_percent'] ?? 0,
                'notes' => $validated['notes'] ?? null,
                'footer_text' => $validated['footer_text'] ?? null,
                'interval_value' => $validated['interval_value'],
                'interval_unit' => $validated['interval_unit'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'max_occurrences' => $validated['max_occurrences'] ?? null,
                'auto_finalize' => $validated['auto_finalize'] ?? false,
                'auto_send_email' => $validated['auto_send_email'] ?? false,
                'email_recipients' => $validated['email_recipients'] ?? null,
            ]);

            // Replace lines
            $recurringInvoice->lines()->delete();

            foreach ($validated['lines'] as $index => $line) {
                $recurringInvoice->lines()->create([
                    'sort_order' => $index + 1,
                    'product_id' => $line['product_id'] ?? null,
                    'concept' => $line['concept'],
                    'description' => $line['description'] ?? null,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'unit' => $line['unit'] ?? 'unidad',
                    'discount_percent' => $line['discount_percent'] ?? 0,
                    'vat_rate' => $line['vat_rate'],
                    'exemption_code' => $line['exemption_code'] ?? null,
                    'irpf_rate' => $line['irpf_rate'] ?? 0,
                    'surcharge_rate' => $line['surcharge_rate'] ?? 0,
                ]);
            }
        });

        return redirect()->route('recurring-invoices.show', $recurringInvoice)
            ->with('success', __('recurring.flash_updated'));
    }

    public function destroy(RecurringInvoice $recurringInvoice)
    {
        if ($recurringInvoice->isActive() && $recurringInvoice->generatedDocuments()->exists()) {
            return back()->with('error', __('recurring.error_cannot_delete_active'));
        }

        $recurringInvoice->lines()->delete();
        $recurringInvoice->delete();

        return redirect()->route('recurring-invoices.index')
            ->with('success', __('recurring.flash_deleted'));
    }

    public function toggleStatus(RecurringInvoice $recurringInvoice)
    {
        if ($recurringInvoice->isFinished()) {
            return back()->with('error', __('recurring.error_finished'));
        }

        if ($recurringInvoice->isActive()) {
            $recurringInvoice->update(['status' => RecurringInvoice::STATUS_PAUSED]);

            return back()->with('success', __('recurring.flash_paused'));
        }

        // Resuming: recalculate next_issue_date if it's in the past
        $nextDate = $recurringInvoice->next_issue_date;
        if ($nextDate->lt(now()->startOfDay())) {
            $nextDate = now()->startOfDay();
        }

        $recurringInvoice->update([
            'status' => RecurringInvoice::STATUS_ACTIVE,
            'next_issue_date' => $nextDate,
        ]);

        return back()->with('success', __('recurring.flash_resumed'));
    }

    public function generateNow(RecurringInvoice $recurringInvoice)
    {
        if (!$recurringInvoice->isActive()) {
            return back()->with('error', __('recurring.error_not_active'));
        }

        try {
            $document = $this->recurringService->generateInvoice($recurringInvoice);

            return redirect()->route('recurring-invoices.show', $recurringInvoice)
                ->with('success', __('recurring.flash_generated', ['number' => $document->number ?? '#' . $document->id]));
        } catch (\Throwable $e) {
            return back()->with('error', __('recurring.error_generation_failed', ['error' => $e->getMessage()]));
        }
    }
}
