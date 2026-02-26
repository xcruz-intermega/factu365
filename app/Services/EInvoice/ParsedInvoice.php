<?php

namespace App\Services\EInvoice;

class ParsedInvoice
{
    public function __construct(
        public readonly string $invoiceNumber,
        public readonly string $issueDate,
        public readonly string $supplierNif,
        public readonly string $supplierName,
        public readonly array $lines,
        public readonly array $vatBreakdown,
        public readonly float $totalBase,
        public readonly float $totalVat,
        public readonly float $totalIrpf,
        public readonly float $total,
        public readonly string $format,
        public readonly string $originalXml,
        public readonly array $errors = [],
    ) {}
}
