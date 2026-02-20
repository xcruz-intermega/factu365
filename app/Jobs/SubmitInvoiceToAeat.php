<?php

namespace App\Jobs;

use App\Models\InvoicingRecord;
use App\Services\VeriFactu\AeatSoapClient;
use App\Services\VeriFactu\CertificateManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SubmitInvoiceToAeat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum number of attempts.
     */
    public int $tries = 3;

    /**
     * Backoff in seconds between retries: 1 min, 5 min, 15 min.
     */
    public function backoff(): array
    {
        $minutes = config('verifactu.retries.backoff_minutes', [1, 5, 15]);

        return array_map(fn ($m) => $m * 60, $minutes);
    }

    public function __construct(
        public int $invoicingRecordId,
    ) {}

    public function handle(AeatSoapClient $soapClient, CertificateManager $certManager): void
    {
        $record = InvoicingRecord::find($this->invoicingRecordId);

        if (! $record) {
            Log::warning('SubmitInvoiceToAeat: Record not found.', [
                'record_id' => $this->invoicingRecordId,
            ]);
            return;
        }

        // Skip if already accepted
        if ($record->isAccepted()) {
            return;
        }

        // Get active certificate
        $certificate = $certManager->getActiveCertificate();
        if (! $certificate) {
            Log::error('SubmitInvoiceToAeat: No active certificate found.');
            $record->update([
                'submission_status' => InvoicingRecord::STATUS_ERROR,
                'error_message' => 'No active certificate configured.',
            ]);
            $this->fail(new \RuntimeException('No active certificate configured.'));
            return;
        }

        // Extract PEM files
        $pemPaths = $certManager->extractPem($certificate);

        try {
            // Update status to submitted
            $record->update(['submission_status' => InvoicingRecord::STATUS_SUBMITTED]);

            // Submit to AEAT
            $submission = $soapClient->submit($record, $pemPaths['cert_path'], $pemPaths['key_path']);

            // Update document verifactu_status
            $document = $record->document;
            if ($document) {
                $verifactuStatus = match ($record->fresh()->submission_status) {
                    InvoicingRecord::STATUS_ACCEPTED => 'accepted',
                    InvoicingRecord::STATUS_REJECTED => 'rejected',
                    InvoicingRecord::STATUS_ERROR => 'error',
                    default => 'submitted',
                };

                $document->update([
                    'verifactu_status' => $verifactuStatus,
                    'qr_code_url' => $document->qr_code_url,
                ]);
            }

            Log::info('SubmitInvoiceToAeat: Submission completed.', [
                'record_id' => $record->id,
                'status' => $submission->result_status,
                'csv' => $submission->aeat_csv,
            ]);

        } finally {
            // Always clean up temp PEM files
            $certManager->cleanupPem($pemPaths);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SubmitInvoiceToAeat: Job failed permanently.', [
            'record_id' => $this->invoicingRecordId,
            'error' => $exception->getMessage(),
        ]);

        $record = InvoicingRecord::find($this->invoicingRecordId);
        if ($record) {
            $record->update([
                'submission_status' => InvoicingRecord::STATUS_ERROR,
                'error_message' => 'Submission failed after all retries: ' . $exception->getMessage(),
            ]);

            $record->document?->update(['verifactu_status' => 'error']);
        }
    }
}
