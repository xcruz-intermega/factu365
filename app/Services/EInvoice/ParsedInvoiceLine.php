<?php

namespace App\Services\EInvoice;

class ParsedInvoiceLine
{
    public function __construct(
        public readonly string $concept,
        public readonly float $quantity,
        public readonly float $unitPrice,
        public readonly float $vatRate,
        public readonly float $lineSubtotal,
        public readonly float $vatAmount,
        public readonly float $lineTotal,
        public readonly ?string $unit = null,
    ) {}
}
