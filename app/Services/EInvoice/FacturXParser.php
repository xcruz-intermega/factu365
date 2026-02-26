<?php

namespace App\Services\EInvoice;

use DOMDocument;
use DOMXPath;
use Smalot\PdfParser\Parser as PdfParser;

class FacturXParser
{
    public function parse(string $pdfContent): ParsedInvoice
    {
        // Extract embedded XML from PDF
        $parser = new PdfParser();
        $pdf = $parser->parseContent($pdfContent);

        $xml = null;
        $metadata = $pdf->getDetails();

        // Try to find attached XML files in the PDF
        foreach ($pdf->getObjects() as $object) {
            $details = $object->getDetails();
            if (isset($details['F']) && str_ends_with((string) $details['F'], '.xml')) {
                $stream = $object->getContent();
                if ($stream && str_contains($stream, '<?xml') || str_contains($stream, '<rsm:')) {
                    $xml = $stream;
                    break;
                }
            }
        }

        // Fallback: search all streams for XML content
        if (!$xml) {
            foreach ($pdf->getObjects() as $object) {
                $content = $object->getContent();
                if ($content && (str_contains($content, 'CrossIndustryInvoice') || str_contains($content, '<rsm:'))) {
                    $xml = $content;
                    break;
                }
            }
        }

        if (!$xml) {
            return new ParsedInvoice(
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
                format: 'facturx',
                originalXml: '',
                errors: [__('documents.import_error_no_xml_in_pdf')],
            );
        }

        return $this->parseCII($xml);
    }

    private function parseCII(string $xml): ParsedInvoice
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);

        // Register common CII namespaces
        $xpath->registerNamespace('rsm', 'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100');
        $xpath->registerNamespace('ram', 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100');

        $invoiceNumber = $this->xpathValue($xpath, '//ram:ExchangedDocument/ram:ID')
            ?: $this->xpathValue($xpath, '//*[local-name()="ExchangedDocument"]/*[local-name()="ID"]');

        $issueDate = $this->xpathValue($xpath, '//ram:ExchangedDocument/ram:IssueDateTime//*[local-name()="DateTimeString"]')
            ?: $this->xpathValue($xpath, '//*[local-name()="IssueDateTime"]//*[local-name()="DateTimeString"]');

        // Convert YYYYMMDD to YYYY-MM-DD
        if ($issueDate && strlen($issueDate) === 8) {
            $issueDate = substr($issueDate, 0, 4) . '-' . substr($issueDate, 4, 2) . '-' . substr($issueDate, 6, 2);
        }

        // Seller
        $supplierNif = $this->xpathValue($xpath, '//*[local-name()="SellerTradeParty"]//*[local-name()="ID"]')
            ?: $this->xpathValue($xpath, '//*[local-name()="SellerTradeParty"]//*[local-name()="SpecifiedTaxRegistration"]//*[local-name()="ID"]');
        $supplierName = $this->xpathValue($xpath, '//*[local-name()="SellerTradeParty"]/*[local-name()="Name"]') ?? '';

        // Lines
        $lines = [];
        $lineNodes = $xpath->query('//*[local-name()="IncludedSupplyChainTradeLineItem"]');
        foreach ($lineNodes as $lineNode) {
            $concept = $this->nodeValue($xpath, $lineNode, './/*[local-name()="SpecifiedTradeProduct"]/*[local-name()="Name"]') ?? 'Sin concepto';
            $quantity = (float) ($this->nodeValue($xpath, $lineNode, './/*[local-name()="BilledQuantity"]') ?? 1);
            $unitPrice = (float) ($this->nodeValue($xpath, $lineNode, './/*[local-name()="NetPriceProductTradePrice"]/*[local-name()="ChargeAmount"]') ?? 0);
            $lineSubtotal = round($quantity * $unitPrice, 2);

            $vatRate = (float) ($this->nodeValue($xpath, $lineNode, './/*[local-name()="ApplicableTradeTax"]/*[local-name()="RateApplicablePercent"]') ?? 0);
            $vatAmount = round($lineSubtotal * $vatRate / 100, 2);
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

        // VAT breakdown
        $vatBreakdown = [];
        $taxNodes = $xpath->query('//*[local-name()="ApplicableHeaderTradeSettlement"]/*[local-name()="ApplicableTradeTax"]');
        foreach ($taxNodes as $taxNode) {
            $rate = (float) ($this->nodeValue($xpath, $taxNode, '*[local-name()="RateApplicablePercent"]') ?? 0);
            $base = (float) ($this->nodeValue($xpath, $taxNode, '*[local-name()="BasisAmount"]') ?? 0);
            $vat = (float) ($this->nodeValue($xpath, $taxNode, '*[local-name()="CalculatedAmount"]') ?? 0);
            $vatBreakdown[] = ['rate' => $rate, 'base' => $base, 'vat' => $vat];
        }

        // Totals
        $totalBase = (float) ($this->xpathValue($xpath, '//*[local-name()="SpecifiedTradeSettlementHeaderMonetarySummation"]/*[local-name()="TaxBasisTotalAmount"]') ?? 0);
        $totalVat = (float) ($this->xpathValue($xpath, '//*[local-name()="SpecifiedTradeSettlementHeaderMonetarySummation"]/*[local-name()="TaxTotalAmount"]') ?? 0);
        $total = (float) ($this->xpathValue($xpath, '//*[local-name()="SpecifiedTradeSettlementHeaderMonetarySummation"]/*[local-name()="GrandTotalAmount"]') ?? 0);
        $totalIrpf = 0;

        if ($totalBase == 0 && count($lines) > 0) {
            $totalBase = round(array_sum(array_map(fn ($l) => $l->lineSubtotal, $lines)), 2);
            $totalVat = round(array_sum(array_map(fn ($l) => $l->vatAmount, $lines)), 2);
            $total = round($totalBase + $totalVat, 2);
        }

        $errors = [];
        if (empty($invoiceNumber)) {
            $errors[] = __('documents.import_error_no_number');
        }
        $computedTotal = round($totalBase + $totalVat - $totalIrpf, 2);
        if ($total > 0 && abs($computedTotal - $total) > 0.02) {
            $errors[] = __('documents.import_error_totals_mismatch');
        }

        return new ParsedInvoice(
            invoiceNumber: $invoiceNumber ?? '',
            issueDate: $issueDate ?? '',
            supplierNif: $supplierNif ?? '',
            supplierName: $supplierName,
            lines: $lines,
            vatBreakdown: $vatBreakdown,
            totalBase: $totalBase,
            totalVat: $totalVat,
            totalIrpf: $totalIrpf,
            total: $total,
            format: 'facturx',
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
