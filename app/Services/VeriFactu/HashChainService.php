<?php

namespace App\Services\VeriFactu;

use App\Models\Document;
use App\Models\InvoicingRecord;

class HashChainService
{
    /**
     * Build the hash input string per AEAT VeriFactu specification.
     *
     * Format:
     * IDEmisorFactura={NIF}&NumSerieFactura={num}&FechaExpedicionFactura={DD-MM-YYYY}
     * &TipoFactura={tipo}&CuotaTotal={cuota}&ImporteTotal={total}
     * &Huella={hash_anterior}&FechaHoraHusoGenRegistro={ISO8601}
     *
     * Rules:
     * - Amounts: no trailing zeros after decimal (123.10 → 123.1, 100.00 → 100)
     * - First record: Huella is empty string
     * - UTF-8 encoding before hashing
     * - DateTime in ISO 8601 with timezone (e.g., 2024-01-15T10:30:00+01:00)
     */
    public function buildHashInput(
        string $nif,
        string $numSerie,
        string $fechaExpedicion,
        string $tipoFactura,
        float $cuotaTotal,
        float $importeTotal,
        string $previousHash,
        string $fechaHoraGeneracion,
    ): string {
        return sprintf(
            'IDEmisorFactura=%s&NumSerieFactura=%s&FechaExpedicionFactura=%s&TipoFactura=%s&CuotaTotal=%s&ImporteTotal=%s&Huella=%s&FechaHoraHusoGenRegistro=%s',
            $nif,
            $numSerie,
            $fechaExpedicion,
            $tipoFactura,
            self::formatAmount($cuotaTotal),
            self::formatAmount($importeTotal),
            $previousHash,
            $fechaHoraGeneracion,
        );
    }

    /**
     * Compute SHA-256 hash of the input string.
     */
    public function computeHash(string $input): string
    {
        // Ensure UTF-8 encoding before hashing
        $utf8Input = mb_convert_encoding($input, 'UTF-8', 'UTF-8');

        return hash('sha256', $utf8Input);
    }

    /**
     * Generate a complete invoicing record for a document.
     * Retrieves the previous hash from the chain automatically.
     */
    public function generateRecord(Document $document, string $recordType = InvoicingRecord::TYPE_ALTA): InvoicingRecord
    {
        $companyProfile = $document->tenant ?? null;

        // Get company NIF from company profile
        $nif = $this->getCompanyNif();

        // Format the expedition date as DD-MM-YYYY
        $fechaExpedicion = $document->issue_date->format('d-m-Y');

        // Invoice type (F1, F2, R1, etc.)
        $tipoFactura = $document->invoice_type ?? 'F1';

        // Calculate totals
        $cuotaTotal = (float) $document->total_vat;
        $importeTotal = (float) $document->total;

        // Get previous hash from chain
        $previousHash = $this->getPreviousHash();

        // Generation timestamp in ISO 8601 with timezone
        $fechaHoraGeneracion = now()->format('Y-m-d\TH:i:sP');

        // Build hash input and compute hash
        $hashInput = $this->buildHashInput(
            $nif,
            $document->number,
            $fechaExpedicion,
            $tipoFactura,
            $cuotaTotal,
            $importeTotal,
            $previousHash,
            $fechaHoraGeneracion,
        );

        $hash = $this->computeHash($hashInput);

        // Create the invoicing record
        return InvoicingRecord::create([
            'document_id' => $document->id,
            'record_type' => $recordType,
            'id_emisor_factura' => $nif,
            'num_serie_factura' => $document->number,
            'fecha_expedicion' => $fechaExpedicion,
            'tipo_factura' => $tipoFactura,
            'cuota_total' => self::formatAmount($cuotaTotal),
            'importe_total' => self::formatAmount($importeTotal),
            'previous_hash' => $previousHash,
            'hash' => $hash,
            'fecha_hora_generacion' => $fechaHoraGeneracion,
            'submission_status' => InvoicingRecord::STATUS_PENDING,
        ]);
    }

    /**
     * Get the hash of the most recent invoicing record (for chaining).
     * Returns empty string if this is the first record.
     */
    public function getPreviousHash(): string
    {
        $lastRecord = InvoicingRecord::query()
            ->orderByDesc('id')
            ->first();

        return $lastRecord?->hash ?? '';
    }

    /**
     * Get the company NIF from the current tenant's company profile.
     */
    private function getCompanyNif(): string
    {
        $profile = \App\Models\CompanyProfile::first();

        if (! $profile) {
            throw new \RuntimeException('Company profile not configured. Cannot generate VeriFactu record.');
        }

        return $profile->nif;
    }

    /**
     * Format an amount per AEAT rules: no trailing zeros after decimal point.
     * Examples:
     *   123.10 → "123.1"
     *   100.00 → "100"
     *   99.99  → "99.99"
     *   0.50   → "0.5"
     *   0.00   → "0"
     */
    public static function formatAmount(float $amount): string
    {
        // Format with 2 decimal places first, then strip trailing zeros
        $formatted = number_format($amount, 2, '.', '');

        // Remove trailing zeros after decimal point
        if (str_contains($formatted, '.')) {
            $formatted = rtrim($formatted, '0');
            $formatted = rtrim($formatted, '.');
        }

        return $formatted;
    }
}
