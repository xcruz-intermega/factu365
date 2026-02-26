<?php

namespace App\Services\EInvoice;

use Illuminate\Http\UploadedFile;

class EInvoiceParserService
{
    public function __construct(
        private FacturaEParser $facturaEParser,
        private FacturXParser $facturXParser,
    ) {}

    public function parse(UploadedFile $file): ParsedInvoice
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match ($extension) {
            'xml' => $this->facturaEParser->parse($file->getContent()),
            'pdf' => $this->facturXParser->parse($file->getContent()),
            default => new ParsedInvoice(
                invoiceNumber: '',
                issueDate: '',
                supplierNif: '',
                supplierName: '',
                lines: [],
                vatBreakdown: [],
                totalBase: 0,
                totalVat: 0,
                totalIrpf: 0,
                total: 0,
                format: 'unknown',
                originalXml: '',
                errors: [__('documents.import_error_unsupported_format')],
            ),
        };
    }
}
