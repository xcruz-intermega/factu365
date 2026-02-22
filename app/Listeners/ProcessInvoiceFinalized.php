<?php

namespace App\Listeners;

use App\Events\InvoiceFinalized;
use App\Jobs\SubmitInvoiceToAeat;
use App\Models\CompanyProfile;
use App\Services\VeriFactu\HashChainService;
use App\Services\VeriFactu\QrCodeService;
use App\Services\VeriFactu\XmlBuilderService;
use Illuminate\Support\Facades\Log;

class ProcessInvoiceFinalized
{
    public function __construct(
        private HashChainService $hashChainService,
        private XmlBuilderService $xmlBuilder,
        private QrCodeService $qrCodeService,
    ) {}

    public function handle(InvoiceFinalized $event): void
    {
        $document = $event->document;

        // Only process invoices and rectificatives (not quotes, delivery notes, etc.)
        if (! in_array($document->document_type, ['invoice', 'rectificative'])) {
            return;
        }

        try {
            $company = CompanyProfile::first();
            if (! $company) {
                Log::warning('VeriFactu: Company profile not configured, skipping.', [
                    'document_id' => $document->id,
                ]);
                return;
            }

            if (! $company->verifactu_enabled) {
                return;
            }

            // 1. Generate hash chain record
            $record = $this->hashChainService->generateRecord($document);

            // 2. Build XML
            $xml = $this->xmlBuilder->buildRegistroAlta($record, $document, $company);
            $record->update(['xml_content' => $xml]);

            // 3. Generate QR code
            $qrUrl = $this->qrCodeService->buildValidationUrl($document, $company->nif, $company->verifactu_environment);
            $document->update([
                'qr_code_url' => $qrUrl,
                'verifactu_status' => 'pending',
            ]);

            // 4. Dispatch async job for AEAT submission
            SubmitInvoiceToAeat::dispatch($record->id);

            Log::info('VeriFactu: Invoice finalized and queued for AEAT submission.', [
                'document_id' => $document->id,
                'record_id' => $record->id,
                'hash' => $record->hash,
            ]);

        } catch (\Throwable $e) {
            Log::error('VeriFactu: Failed to process finalized invoice.', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $document->update(['verifactu_status' => 'error']);
        }
    }
}
