<?php

namespace App\Services\EInvoice;

use DOMDocument;
use DOMXPath;

class FacturaEParser
{
    public function parse(string $xml): ParsedInvoice
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);

        // Use local-name() for namespace-agnostic queries
        $invoiceNumber = $this->xpathValue($xpath, '//*[local-name()="InvoiceNumber"]')
            ?: $this->xpathValue($xpath, '//*[local-name()="InvoiceSeriesCode"]');
        $issueDate = $this->xpathValue($xpath, '//*[local-name()="InvoiceIssueData"]/*[local-name()="IssueDate"]') ?? '';

        // Seller party
        $supplierNif = $this->xpathValue($xpath, '//*[local-name()="SellerParty"]//*[local-name()="TaxIdentificationNumber"]') ?? '';
        $supplierCorporate = $this->xpathValue($xpath, '//*[local-name()="SellerParty"]//*[local-name()="CorporateName"]');
        $supplierFirstName = $this->xpathValue($xpath, '//*[local-name()="SellerParty"]//*[local-name()="Name"]');
        $supplierLastName = $this->xpathValue($xpath, '//*[local-name()="SellerParty"]//*[local-name()="FirstSurname"]');
        $supplierName = $supplierCorporate ?: trim(($supplierFirstName ?? '') . ' ' . ($supplierLastName ?? ''));

        // Lines
        $lines = [];
        $lineNodes = $xpath->query('//*[local-name()="InvoiceLine"]');
        foreach ($lineNodes as $lineNode) {
            $concept = $this->nodeValue($xpath, $lineNode, '*[local-name()="ItemDescription"]') ?? 'Sin concepto';
            $quantity = (float) ($this->nodeValue($xpath, $lineNode, '*[local-name()="Quantity"]') ?? 1);
            $unitPrice = (float) ($this->nodeValue($xpath, $lineNode, '*[local-name()="UnitPriceWithoutTax"]') ?? 0);
            $lineSubtotal = (float) ($this->nodeValue($xpath, $lineNode, '*[local-name()="TotalCost"]') ?? ($quantity * $unitPrice));

            // Tax from line
            $vatRate = (float) ($this->nodeValue($xpath, $lineNode, './/*[local-name()="TaxRate"]') ?? 0);
            $vatAmount = (float) ($this->nodeValue($xpath, $lineNode, './/*[local-name()="TaxAmount"]/*[local-name()="TotalAmount"]') ?? 0);
            if ($vatAmount == 0 && $vatRate > 0) {
                $vatAmount = round($lineSubtotal * $vatRate / 100, 2);
            }

            $lineTotal = round($lineSubtotal + $vatAmount, 2);

            $lines[] = new ParsedInvoiceLine(
                concept: $concept,
                quantity: $quantity,
                unitPrice: $unitPrice,
                vatRate: $vatRate,
                lineSubtotal: $lineSubtotal,
                vatAmount: $vatAmount,
                lineTotal: $lineTotal,
            );
        }

        // VAT breakdown from TaxesOutputs
        $vatBreakdown = [];
        $taxNodes = $xpath->query('//*[local-name()="TaxesOutputs"]/*[local-name()="Tax"]');
        foreach ($taxNodes as $taxNode) {
            $rate = (float) ($this->nodeValue($xpath, $taxNode, '*[local-name()="TaxRate"]') ?? 0);
            $base = (float) ($this->nodeValue($xpath, $taxNode, '*[local-name()="TaxableBase"]/*[local-name()="TotalAmount"]') ?? 0);
            $vat = (float) ($this->nodeValue($xpath, $taxNode, '*[local-name()="TaxAmount"]/*[local-name()="TotalAmount"]') ?? 0);
            $vatBreakdown[] = ['rate' => $rate, 'base' => $base, 'vat' => $vat];
        }

        // Totals from InvoiceTotals
        $totalBase = (float) ($this->xpathValue($xpath, '//*[local-name()="InvoiceTotals"]/*[local-name()="TotalGrossAmountBeforeTaxes"]') ?? 0);
        $totalVat = (float) ($this->xpathValue($xpath, '//*[local-name()="InvoiceTotals"]/*[local-name()="TotalTaxOutputs"]') ?? 0);
        $totalIrpf = (float) ($this->xpathValue($xpath, '//*[local-name()="InvoiceTotals"]/*[local-name()="TotalTaxesWithheld"]') ?? 0);
        $total = (float) ($this->xpathValue($xpath, '//*[local-name()="InvoiceTotals"]/*[local-name()="InvoiceTotal"]') ?? 0);

        // Fallback: compute from lines if totals are zero
        if ($totalBase == 0 && count($lines) > 0) {
            $totalBase = round(array_sum(array_map(fn ($l) => $l->lineSubtotal, $lines)), 2);
            $totalVat = round(array_sum(array_map(fn ($l) => $l->vatAmount, $lines)), 2);
            $total = round($totalBase + $totalVat - $totalIrpf, 2);
        }

        // Validation
        $errors = [];
        if (empty($invoiceNumber)) {
            $errors[] = __('documents.import_error_no_number');
        }
        if (empty($supplierNif)) {
            $errors[] = __('documents.import_error_no_nif');
        }
        $computedTotal = round($totalBase + $totalVat - $totalIrpf, 2);
        if (abs($computedTotal - $total) > 0.02) {
            $errors[] = __('documents.import_error_totals_mismatch');
        }

        return new ParsedInvoice(
            invoiceNumber: $invoiceNumber ?? '',
            issueDate: $issueDate,
            supplierNif: $supplierNif,
            supplierName: $supplierName,
            lines: $lines,
            vatBreakdown: $vatBreakdown,
            totalBase: $totalBase,
            totalVat: $totalVat,
            totalIrpf: $totalIrpf,
            total: $total,
            format: 'facturae',
            originalXml: $xml,
            errors: $errors,
        );
    }

    private function xpathValue(DOMXPath $xpath, string $query): ?string
    {
        $nodes = $xpath->query($query);
        return $nodes->length > 0 ? trim($nodes->item(0)->textContent) : null;
    }

    private function nodeValue(DOMXPath $xpath, \DOMNode $context, string $query): ?string
    {
        $nodes = $xpath->query($query, $context);
        return $nodes->length > 0 ? trim($nodes->item(0)->textContent) : null;
    }
}
