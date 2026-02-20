<?php

namespace App\Services\VeriFactu;

use App\Models\AeatSubmission;
use App\Models\InvoicingRecord;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AeatSoapClient
{
    /**
     * Submit an invoicing record to AEAT.
     *
     * Uses mutual TLS with client certificate for authentication.
     */
    public function submit(InvoicingRecord $record, string $certPath, string $keyPath): AeatSubmission
    {
        $env = config('verifactu.environment', 'sandbox');
        $endpoints = config("verifactu.endpoints.{$env}");

        $isAnulacion = $record->record_type === InvoicingRecord::TYPE_ANULACION;
        $endpoint = $isAnulacion ? 'anulacion' : 'registro';
        $url = $endpoints['base_url'] . $endpoints[$endpoint];

        $attemptNumber = $record->submissions()->count() + 1;

        $submission = AeatSubmission::create([
            'invoicing_record_id' => $record->id,
            'request_xml' => $record->xml_content,
            'attempt_number' => $attemptNumber,
            'result_status' => AeatSubmission::STATUS_PENDING,
        ]);

        try {
            $response = Http::withOptions([
                'cert' => $certPath,
                'ssl_key' => $keyPath,
                'verify' => true,
                'timeout' => 30,
                'connect_timeout' => 10,
            ])
            ->withHeaders([
                'Content-Type' => 'text/xml; charset=UTF-8',
                'SOAPAction' => '',
            ])
            ->withBody($record->xml_content, 'text/xml; charset=UTF-8')
            ->post($url);

            $submission->update([
                'http_status' => $response->status(),
                'response_xml' => $response->body(),
            ]);

            // Parse response
            $this->parseResponse($submission, $response->body());

        } catch (\Throwable $e) {
            Log::error('AEAT submission failed', [
                'record_id' => $record->id,
                'error' => $e->getMessage(),
            ]);

            $submission->update([
                'result_status' => AeatSubmission::STATUS_ERROR,
                'error_code' => 'CONNECTION_ERROR',
                'error_description' => $e->getMessage(),
            ]);
        }

        // Update the invoicing record status based on submission result
        $this->updateRecordStatus($record, $submission);

        return $submission;
    }

    /**
     * Parse the AEAT SOAP response XML to extract result status.
     */
    private function parseResponse(AeatSubmission $submission, string $responseXml): void
    {
        try {
            $dom = new \DOMDocument();
            $dom->loadXML($responseXml);

            // Look for CSV (Código Seguro de Verificación)
            $csvNodes = $dom->getElementsByTagName('CSV');
            if ($csvNodes->length > 0) {
                $submission->aeat_csv = $csvNodes->item(0)->textContent;
            }

            // Look for EstadoEnvio
            $estadoNodes = $dom->getElementsByTagName('EstadoEnvio');
            if ($estadoNodes->length > 0) {
                $estado = $estadoNodes->item(0)->textContent;
                $submission->result_status = match ($estado) {
                    'Correcto' => AeatSubmission::STATUS_ACCEPTED,
                    'ParcialmenteCorrecto' => AeatSubmission::STATUS_PARTIALLY_ACCEPTED,
                    'Incorrecto' => AeatSubmission::STATUS_REJECTED,
                    default => AeatSubmission::STATUS_ERROR,
                };
            }

            // Look for error details
            $codigoErrorNodes = $dom->getElementsByTagName('CodigoErrorRegistro');
            if ($codigoErrorNodes->length > 0) {
                $submission->error_code = $codigoErrorNodes->item(0)->textContent;
            }

            $descErrorNodes = $dom->getElementsByTagName('DescripcionErrorRegistro');
            if ($descErrorNodes->length > 0) {
                $submission->error_description = $descErrorNodes->item(0)->textContent;
            }

            $submission->save();

        } catch (\Throwable $e) {
            Log::warning('Failed to parse AEAT response', [
                'submission_id' => $submission->id,
                'error' => $e->getMessage(),
            ]);

            $submission->update([
                'result_status' => AeatSubmission::STATUS_ERROR,
                'error_description' => 'Failed to parse AEAT response: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Update the invoicing record based on submission result.
     */
    private function updateRecordStatus(InvoicingRecord $record, AeatSubmission $submission): void
    {
        $statusMap = [
            AeatSubmission::STATUS_ACCEPTED => InvoicingRecord::STATUS_ACCEPTED,
            AeatSubmission::STATUS_PARTIALLY_ACCEPTED => InvoicingRecord::STATUS_ACCEPTED,
            AeatSubmission::STATUS_REJECTED => InvoicingRecord::STATUS_REJECTED,
            AeatSubmission::STATUS_ERROR => InvoicingRecord::STATUS_ERROR,
            AeatSubmission::STATUS_PENDING => InvoicingRecord::STATUS_SUBMITTED,
        ];

        $record->update([
            'submission_status' => $statusMap[$submission->result_status] ?? InvoicingRecord::STATUS_ERROR,
            'aeat_csv' => $submission->aeat_csv,
            'error_message' => $submission->error_description,
        ]);
    }
}
