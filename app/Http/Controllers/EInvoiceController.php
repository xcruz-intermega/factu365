<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\CompanyProfile;
use App\Models\Document;
use App\Models\DocumentLine;
use App\Services\EInvoice\EInvoiceParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class EInvoiceController extends Controller
{
    public function __construct(
        private EInvoiceParserService $parserService,
    ) {}

    public function create()
    {
        return Inertia::render('Documents/ImportEInvoice', [
            'suppliers' => Client::suppliers()->orderBy('legal_name')->get(['id', 'legal_name', 'trade_name', 'nif']),
        ]);
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xml,pdf', 'max:10240'],
        ]);

        $file = $request->file('file');
        $parsed = $this->parserService->parse($file);

        // Save temp file for later import
        $tempPath = $file->store('einvoice-temp', 'local');

        $company = CompanyProfile::first();

        // Check if supplier NIF matches company NIF (should not import own invoices)
        $errors = $parsed->errors;
        if ($company && $parsed->supplierNif && strtoupper($parsed->supplierNif) === strtoupper($company->nif ?? '')) {
            $errors[] = __('documents.import_error_own_nif');
        }

        // Try to match supplier by NIF
        $matchedSupplier = null;
        if ($parsed->supplierNif) {
            $matchedSupplier = Client::where('nif', $parsed->supplierNif)
                ->where('type', 'supplier')
                ->first(['id', 'legal_name', 'trade_name', 'nif']);
        }

        return Inertia::render('Documents/ImportEInvoice', [
            'suppliers' => Client::suppliers()->orderBy('legal_name')->get(['id', 'legal_name', 'trade_name', 'nif']),
            'preview' => [
                'invoiceNumber' => $parsed->invoiceNumber,
                'issueDate' => $parsed->issueDate,
                'supplierNif' => $parsed->supplierNif,
                'supplierName' => $parsed->supplierName,
                'lines' => array_map(fn ($l) => [
                    'concept' => $l->concept,
                    'quantity' => $l->quantity,
                    'unitPrice' => $l->unitPrice,
                    'vatRate' => $l->vatRate,
                    'lineSubtotal' => $l->lineSubtotal,
                    'vatAmount' => $l->vatAmount,
                    'lineTotal' => $l->lineTotal,
                ], $parsed->lines),
                'vatBreakdown' => $parsed->vatBreakdown,
                'totalBase' => $parsed->totalBase,
                'totalVat' => $parsed->totalVat,
                'totalIrpf' => $parsed->totalIrpf,
                'total' => $parsed->total,
                'format' => $parsed->format,
                'errors' => $errors,
                'tempPath' => $tempPath,
                'matchedSupplierId' => $matchedSupplier?->id,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'temp_path' => ['required', 'string'],
            'supplier_client_id' => ['nullable', 'exists:clients,id'],
            'invoice_number' => ['required', 'string', 'max:100'],
            'issue_date' => ['required', 'date'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.concept' => ['required', 'string'],
            'lines.*.quantity' => ['required', 'numeric'],
            'lines.*.unit_price' => ['required', 'numeric'],
            'lines.*.vat_rate' => ['required', 'numeric'],
        ]);

        $tempPath = $request->input('temp_path');

        // Read original XML from temp file
        $originalContent = Storage::disk('local')->exists($tempPath)
            ? Storage::disk('local')->get($tempPath)
            : null;

        // Create purchase invoice document
        $lineData = $request->input('lines');
        $taxBase = 0;
        $totalVat = 0;
        $totalIrpf = 0;

        foreach ($lineData as &$line) {
            $line['line_subtotal'] = round($line['quantity'] * $line['unit_price'], 2);
            $line['vat_amount'] = round($line['line_subtotal'] * $line['vat_rate'] / 100, 2);
            $line['irpf_rate'] = $line['irpf_rate'] ?? 0;
            $line['irpf_amount'] = round($line['line_subtotal'] * ($line['irpf_rate'] ?? 0) / 100, 2);
            $line['line_total'] = round($line['line_subtotal'] + $line['vat_amount'] - $line['irpf_amount'], 2);
            $taxBase += $line['line_subtotal'];
            $totalVat += $line['vat_amount'];
            $totalIrpf += $line['irpf_amount'];
        }
        unset($line);

        $total = round($taxBase + $totalVat - $totalIrpf, 2);

        $document = Document::create([
            'document_type' => Document::TYPE_PURCHASE_INVOICE,
            'direction' => 'received',
            'client_id' => $request->input('supplier_client_id'),
            'number' => $request->input('invoice_number'),
            'status' => Document::STATUS_DRAFT,
            'issue_date' => $request->input('issue_date'),
            'tax_base' => round($taxBase, 2),
            'total_vat' => round($totalVat, 2),
            'total_irpf' => round($totalIrpf, 2),
            'total_surcharge' => 0,
            'total_discount' => 0,
            'subtotal' => round($taxBase, 2),
            'total' => $total,
        ]);

        foreach ($lineData as $i => $line) {
            DocumentLine::create([
                'document_id' => $document->id,
                'sort_order' => $i + 1,
                'concept' => $line['concept'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'unit' => $line['unit'] ?? 'unidad',
                'discount_percent' => 0,
                'discount_amount' => 0,
                'vat_rate' => $line['vat_rate'],
                'vat_amount' => $line['vat_amount'],
                'irpf_rate' => $line['irpf_rate'] ?? 0,
                'irpf_amount' => $line['irpf_amount'] ?? 0,
                'surcharge_rate' => 0,
                'surcharge_amount' => 0,
                'line_subtotal' => $line['line_subtotal'],
                'line_total' => $line['line_total'],
            ]);
        }

        // Store original XML as attachment
        if ($originalContent) {
            $attachmentPath = 'einvoice-originals/' . $document->id . '_original.' .
                (str_contains($originalContent, '<?xml') || str_contains($originalContent, '<') ? 'xml' : 'pdf');
            Storage::disk('local')->put($attachmentPath, $originalContent);
        }

        // Clean up temp file
        if (Storage::disk('local')->exists($tempPath)) {
            Storage::disk('local')->delete($tempPath);
        }

        return redirect()->route('documents.edit', [
            'type' => 'purchase_invoice',
            'document' => $document->id,
        ])->with('success', __('documents.import_success'));
    }
}
