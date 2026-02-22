<?php

namespace App\Services\VeriFactu;

use App\Models\Document;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeService
{
    /**
     * Build the AEAT validation URL for a document.
     *
     * URL format: {base_url}?nif={nif}&numserie={num}&fecha={DD-MM-YYYY}&importe={total}
     */
    public function buildValidationUrl(Document $document, string $nif, string $environment = 'sandbox'): string
    {
        $baseUrl = config("verifactu.endpoints.{$environment}.qr_validation_url")
            ?? config('verifactu.qr_validation_url');

        $params = http_build_query([
            'nif' => $nif,
            'numserie' => $document->number,
            'fecha' => $document->issue_date->format('d-m-Y'),
            'importe' => HashChainService::formatAmount((float) $document->total),
        ]);

        return $baseUrl . '?' . $params;
    }

    /**
     * Generate a QR code PNG for the document's validation URL.
     *
     * Per AEAT specification:
     * - ISO 18004:2015
     * - Error correction level M
     * - Size 30-40mm (we use ~300px which is ~30mm at 254 DPI)
     *
     * Returns the PNG binary data.
     */
    public function generateQrCode(Document $document, string $nif, string $environment = 'sandbox'): string
    {
        $url = $this->buildValidationUrl($document, $nif, $environment);

        $builder = new Builder(
            writer: new PngWriter(),
            data: $url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 300,
            margin: 10,
        );

        return $builder->build()->getString();
    }

    /**
     * Generate a QR code and return it as a base64 data URI.
     * Useful for embedding in PDFs and HTML.
     */
    public function generateQrCodeDataUri(Document $document, string $nif, string $environment = 'sandbox'): string
    {
        $url = $this->buildValidationUrl($document, $nif, $environment);

        $builder = new Builder(
            writer: new PngWriter(),
            data: $url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 300,
            margin: 10,
        );

        return $builder->build()->getDataUri();
    }

    /**
     * Store QR code as a file and return the path.
     */
    public function storeQrCode(Document $document, string $nif, string $environment = 'sandbox'): string
    {
        $png = $this->generateQrCode($document, $nif, $environment);
        $path = 'qr-codes/' . $document->id . '.png';

        \Illuminate\Support\Facades\Storage::put($path, $png);

        return $path;
    }
}
