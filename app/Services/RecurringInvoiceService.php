<?php

namespace App\Services;

use App\Events\InvoiceFinalized;
use App\Exceptions\InsufficientStockException;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentDueDate;
use App\Models\RecurringInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RecurringInvoiceService
{
    public function __construct(
        private TaxCalculatorService $taxCalculator,
        private NumberingService $numberingService,
        private StockService $stockService,
        private PdfGeneratorService $pdfGenerator,
        private MailConfigService $mailConfigService,
    ) {}

    /**
     * Generate all pending invoices for the current tenant.
     *
     * @return array<int, array{template_id: int, status: string, document_id?: int, error?: string}>
     */
    public function generatePendingForTenant(): array
    {
        $templates = RecurringInvoice::due()
            ->with(['client', 'lines', 'series', 'paymentTemplate.lines'])
            ->get();

        $results = [];

        foreach ($templates as $template) {
            try {
                $document = $this->generateInvoice($template);
                $results[] = [
                    'template_id' => $template->id,
                    'status' => 'success',
                    'document_id' => $document->id,
                ];
            } catch (\Throwable $e) {
                Log::warning('Recurring invoice generation failed', [
                    'template_id' => $template->id,
                    'template_name' => $template->name,
                    'error' => $e->getMessage(),
                ]);
                $results[] = [
                    'template_id' => $template->id,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Generate a single invoice from a recurring template.
     */
    public function generateInvoice(RecurringInvoice $template): Document
    {
        // Validate: client must exist
        if (!$template->client) {
            throw new \RuntimeException("Client not found for recurring invoice '{$template->name}'");
        }

        // Validate: must have lines
        if ($template->lines->isEmpty()) {
            throw new \RuntimeException("No lines found for recurring invoice '{$template->name}'");
        }

        // Build lines array for TaxCalculatorService
        $lines = $template->lines->map(fn ($line) => [
            'product_id' => $line->product_id,
            'concept' => $line->concept,
            'description' => $line->description,
            'quantity' => (float) $line->quantity,
            'unit_price' => (float) $line->unit_price,
            'unit' => $line->unit,
            'discount_percent' => (float) $line->discount_percent,
            'vat_rate' => (float) $line->vat_rate,
            'exemption_code' => $line->exemption_code,
            'irpf_rate' => (float) $line->irpf_rate,
            'surcharge_rate' => (float) $line->surcharge_rate,
        ])->toArray();

        // Calculate totals
        $calculated = $this->taxCalculator->calculateDocument(
            $lines,
            (float) $template->global_discount_percent
        );

        $document = DB::transaction(function () use ($template, $calculated) {
            // Create document
            $document = Document::create([
                'document_type' => Document::TYPE_INVOICE,
                'invoice_type' => $template->invoice_type ?? 'F1',
                'direction' => 'issued',
                'status' => Document::STATUS_DRAFT,
                'client_id' => $template->client_id,
                'recurring_invoice_id' => $template->id,
                'issue_date' => $template->next_issue_date->toDateString(),
                'subtotal' => $calculated['subtotal'],
                'total_discount' => $calculated['total_discount'],
                'global_discount_percent' => $template->global_discount_percent,
                'global_discount_amount' => $calculated['global_discount_amount'],
                'tax_base' => $calculated['tax_base'],
                'total_vat' => $calculated['total_vat'],
                'total_irpf' => $calculated['total_irpf'],
                'total_surcharge' => $calculated['total_surcharge'],
                'total' => $calculated['total'],
                'regime_key' => $template->regime_key ?? '01',
                'notes' => $template->notes,
                'footer_text' => $template->footer_text,
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
                    'unit' => $line['unit'] ?? 'unidad',
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

            // Generate due dates from payment template
            if ($template->paymentTemplate) {
                $dueDates = $template->paymentTemplate->generateDueDates(
                    $template->next_issue_date->copy(),
                    (float) $calculated['total']
                );

                foreach ($dueDates as $dd) {
                    $document->dueDates()->create($dd);
                }

                if (!empty($dueDates)) {
                    $document->update(['due_date' => $dueDates[0]['due_date']]);
                }
            }

            return $document;
        });

        // Auto-finalize if configured
        if ($template->auto_finalize) {
            $this->finalizeDocument($document, $template);
        }

        // Advance template to next date
        $template->advanceNextIssueDate();

        return $document;
    }

    private function finalizeDocument(Document $document, RecurringInvoice $template): void
    {
        try {
            $numbering = $this->numberingService->generateNumber(
                Document::TYPE_INVOICE,
                $template->series_id,
            );

            $document->update([
                'series_id' => $numbering['series_id'],
                'number' => $numbering['number'],
                'status' => Document::STATUS_FINALIZED,
            ]);

            // Stock processing
            try {
                $this->stockService->processDocumentLines($document, 'out');
            } catch (InsufficientStockException $e) {
                Log::warning('Recurring invoice stock insufficient', [
                    'document_id' => $document->id,
                    'product' => $e->productName,
                    'available' => $e->availableQuantity,
                ]);
            }

            AuditLog::record($document, AuditLog::ACTION_FINALIZED, null, null,
                "Document '{$document->number}' auto-finalized from recurring template '{$template->name}'");

            // Dispatch VeriFactu event
            InvoiceFinalized::dispatch($document->fresh());

            // Auto-send email if configured
            if ($template->auto_send_email) {
                $this->sendEmail($document, $template);
            }
        } catch (\Throwable $e) {
            Log::error('Recurring invoice finalization failed', [
                'document_id' => $document->id,
                'template_id' => $template->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendEmail(Document $document, RecurringInvoice $template): void
    {
        if (!$this->mailConfigService->isActive()) {
            Log::info('Skipping recurring invoice email: SMTP not configured', [
                'document_id' => $document->id,
            ]);
            return;
        }

        $recipients = $template->email_recipients
            ? array_map('trim', preg_split('/[\s,;]+/', $template->email_recipients, -1, PREG_SPLIT_NO_EMPTY))
            : [$template->client->email ?? null];

        $recipients = array_filter($recipients, fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL));

        if (empty($recipients)) {
            Log::info('Skipping recurring invoice email: no valid recipients', [
                'document_id' => $document->id,
            ]);
            return;
        }

        try {
            $typeLabel = Document::documentTypeLabel(Document::TYPE_INVOICE);
            $subject = "{$typeLabel} {$document->number}";
            $body = __('documents.flash_email_body', ['type' => $typeLabel, 'number' => $document->number]);

            $pdfContent = $this->pdfGenerator->content($document);

            $filename = str_replace(' ', '_', $typeLabel) . '_' . str_replace(['/', '\\'], '-', $document->number) . '.pdf';

            Mail::raw($body, function ($mail) use ($recipients, $subject, $pdfContent, $filename) {
                $mail->to($recipients[0]);
                if (count($recipients) > 1) {
                    $mail->cc(array_slice($recipients, 1));
                }
                $mail->subject($subject);
                $mail->attachData($pdfContent, $filename, ['mime' => 'application/pdf']);
            });

            $document->update(['status' => Document::STATUS_SENT]);
        } catch (\Throwable $e) {
            Log::error('Recurring invoice email failed', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
