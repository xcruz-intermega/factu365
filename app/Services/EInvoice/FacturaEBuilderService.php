<?php

namespace App\Services\EInvoice;

use App\Models\CompanyProfile;
use App\Models\Document;
use DOMDocument;
use DOMElement;

class FacturaEBuilderService
{
    private const NS_FE = 'http://www.facturae.gob.es/formato/Versiones/Facturaev3_2_2.xml';

    private DOMDocument $dom;

    public function generate(Document $document): string
    {
        $document->loadMissing(['client', 'lines', 'correctedDocument', 'series']);
        $company = CompanyProfile::first();

        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;

        // Root element
        $root = $this->dom->createElementNS(self::NS_FE, 'fe:Facturae');
        $this->dom->appendChild($root);

        // FileHeader
        $root->appendChild($this->buildFileHeader($document, $company));

        // Parties
        $root->appendChild($this->buildParties($document, $company));

        // Invoices
        $root->appendChild($this->buildInvoices($document, $company));

        return $this->dom->saveXML();
    }

    private function buildFileHeader(Document $document, CompanyProfile $company): DOMElement
    {
        $header = $this->el('FileHeader');

        $this->addChild($header, 'SchemaVersion', '3.2.2');
        $this->addChild($header, 'Modality', 'I');
        $this->addChild($header, 'InvoiceIssuerType', 'EM');

        // Batch
        $batch = $this->el('Batch');
        $batchId = $company->nif . $document->number;
        $this->addChild($batch, 'BatchIdentifier', $batchId);
        $this->addChild($batch, 'InvoicesCount', '1');

        $totalInvoicesAmount = $this->el('TotalInvoicesAmount');
        $this->addChild($totalInvoicesAmount, 'TotalAmount', $this->amount($document->total));
        $batch->appendChild($totalInvoicesAmount);

        $totalOutstandingAmount = $this->el('TotalOutstandingAmount');
        $this->addChild($totalOutstandingAmount, 'TotalAmount', $this->amount($document->total));
        $batch->appendChild($totalOutstandingAmount);

        $totalExecutableAmount = $this->el('TotalExecutableAmount');
        $this->addChild($totalExecutableAmount, 'TotalAmount', $this->amount($document->total));
        $batch->appendChild($totalExecutableAmount);

        $this->addChild($batch, 'InvoiceCurrencyCode', 'EUR');
        $header->appendChild($batch);

        return $header;
    }

    private function buildParties(Document $document, CompanyProfile $company): DOMElement
    {
        $parties = $this->el('Parties');

        // SellerParty
        $seller = $this->el('SellerParty');
        $seller->appendChild($this->buildTaxIdentification($company->nif));
        $seller->appendChild($this->buildPartyEntity(
            $company->nif,
            $company->legal_name,
            $company->trade_name ?? $company->legal_name,
            $company->address_street,
            $company->address_postal_code,
            $company->address_city,
            $company->address_province,
            $company->address_country ?? 'ESP',
        ));
        $parties->appendChild($seller);

        // BuyerParty
        $buyer = $this->el('BuyerParty');
        $client = $document->client;
        $clientNif = $client->nif ?? '';
        $buyer->appendChild($this->buildTaxIdentification($clientNif));
        $buyer->appendChild($this->buildPartyEntity(
            $clientNif,
            $client->legal_name ?? 'N/A',
            $client->trade_name ?? $client->legal_name ?? 'N/A',
            $client->address_street ?? 'N/A',
            $client->address_postal_code ?? '00000',
            $client->address_city ?? 'N/A',
            $client->address_province ?? 'N/A',
            $client->address_country ?? 'ESP',
        ));
        $parties->appendChild($buyer);

        return $parties;
    }

    private function buildTaxIdentification(string $nif): DOMElement
    {
        $taxId = $this->el('TaxIdentification');
        $this->addChild($taxId, 'PersonTypeCode', $this->personTypeCode($nif));
        $this->addChild($taxId, 'ResidenceTypeCode', 'R');
        $this->addChild($taxId, 'TaxIdentificationNumber', $nif);

        return $taxId;
    }

    private function buildPartyEntity(
        string $nif,
        string $legalName,
        string $tradeName,
        ?string $street,
        ?string $postalCode,
        ?string $city,
        ?string $province,
        string $country,
    ): DOMElement {
        $isJuridical = $this->personTypeCode($nif) === 'J';

        if ($isJuridical) {
            $entity = $this->el('LegalEntity');
            $this->addChild($entity, 'CorporateName', mb_substr($legalName, 0, 80));
            if ($tradeName && $tradeName !== $legalName) {
                $this->addChild($entity, 'TradeName', mb_substr($tradeName, 0, 40));
            }
        } else {
            $entity = $this->el('Individual');
            $parts = $this->splitName($legalName);
            $this->addChild($entity, 'Name', $parts['name']);
            $this->addChild($entity, 'FirstSurname', $parts['firstSurname']);
            if ($parts['secondSurname']) {
                $this->addChild($entity, 'SecondSurname', $parts['secondSurname']);
            }
        }

        // Address
        $address = $this->el($country === 'ESP' ? 'AddressInSpain' : 'OverseasAddress');
        $this->addChild($address, 'Address', mb_substr($street ?: 'N/A', 0, 80));
        $this->addChild($address, 'PostCode', $postalCode ?: '00000');
        $this->addChild($address, 'Town', mb_substr($city ?: 'N/A', 0, 50));
        $this->addChild($address, 'Province', mb_substr($province ?: 'N/A', 0, 20));
        $this->addChild($address, 'CountryCode', $this->countryCode($country));
        $entity->appendChild($address);

        return $entity;
    }

    private function buildInvoices(Document $document, CompanyProfile $company): DOMElement
    {
        $invoices = $this->el('Invoices');
        $invoice = $this->el('Invoice');

        // InvoiceHeader
        $invoice->appendChild($this->buildInvoiceHeader($document));

        // InvoiceIssueData
        $invoice->appendChild($this->buildInvoiceIssueData($document));

        // TaxesOutputs (IVA + surcharge)
        $invoice->appendChild($this->buildTaxesOutputs($document));

        // TaxesWithheld (IRPF)
        if ((float) $document->total_irpf > 0) {
            $invoice->appendChild($this->buildTaxesWithheld($document));
        }

        // InvoiceTotals
        $invoice->appendChild($this->buildInvoiceTotals($document));

        // Items
        $invoice->appendChild($this->buildItems($document));

        $invoices->appendChild($invoice);

        return $invoices;
    }

    private function buildInvoiceHeader(Document $document): DOMElement
    {
        $header = $this->el('InvoiceHeader');

        $this->addChild($header, 'InvoiceNumber', $document->number);
        if ($document->series) {
            $this->addChild($header, 'InvoiceSeriesCode', $document->series->prefix);
        }

        // InvoiceDocumentType: FC (completa), FA (simplificada)
        $docType = ($document->invoice_type === 'F2') ? 'FA' : 'FC';
        $this->addChild($header, 'InvoiceDocumentType', $docType);

        // InvoiceClass: OO (original), OR (rectificativa)
        $invoiceClass = $document->isRectificative() ? 'OR' : 'OO';
        $this->addChild($header, 'InvoiceClass', $invoiceClass);

        // Corrective node for rectificatives
        if ($document->isRectificative()) {
            $corrective = $this->el('Corrective');

            if ($document->corrected_document_id && $document->correctedDocument) {
                $corrected = $document->correctedDocument;
                $this->addChild($corrective, 'InvoiceNumber', $corrected->number);
                if ($corrected->series) {
                    $this->addChild($corrective, 'InvoiceSeriesCode', $corrected->series->prefix);
                }
            }

            // ReasonCode - generic
            $this->addChild($corrective, 'ReasonCode', '01');
            $this->addChild($corrective, 'ReasonDescription', 'Corrección de errores');

            // CorrectionMethod: 01=sustitución, 02=diferencias
            $method = ($document->rectificative_type === 'substitution') ? '01' : '02';
            $this->addChild($corrective, 'CorrectionMethod', $method);
            $this->addChild($corrective, 'CorrectionMethodDescription',
                $method === '01' ? 'Rectificación íntegra' : 'Rectificación por diferencias');

            // TaxPeriod (based on corrected document or current document dates)
            $taxPeriod = $this->el('TaxPeriod');
            $refDate = $document->correctedDocument ? $document->correctedDocument->issue_date : $document->issue_date;
            $this->addChild($taxPeriod, 'StartDate', $refDate->format('Y-m-d'));
            $this->addChild($taxPeriod, 'EndDate', $refDate->format('Y-m-d'));
            $corrective->appendChild($taxPeriod);

            $header->appendChild($corrective);
        }

        return $header;
    }

    private function buildInvoiceIssueData(Document $document): DOMElement
    {
        $data = $this->el('InvoiceIssueData');

        $this->addChild($data, 'IssueDate', $document->issue_date->format('Y-m-d'));
        if ($document->operation_date) {
            $this->addChild($data, 'OperationDate', $document->operation_date->format('Y-m-d'));
        }
        $this->addChild($data, 'InvoiceCurrencyCode', 'EUR');
        $this->addChild($data, 'TaxCurrencyCode', 'EUR');
        $this->addChild($data, 'LanguageName', 'es');

        return $data;
    }

    private function buildTaxesOutputs(Document $document): DOMElement
    {
        $taxesOutputs = $this->el('TaxesOutputs');

        // Group lines by VAT rate
        $grouped = $document->lines->groupBy(fn ($line) => $this->amount($line->vat_rate));

        foreach ($grouped as $vatRate => $lines) {
            $tax = $this->el('Tax');
            $this->addChild($tax, 'TaxTypeCode', '01'); // IVA
            $this->addChild($tax, 'TaxRate', $this->amount($vatRate));

            $base = $lines->sum(fn ($l) => (float) $l->line_total);
            $taxableBase = $this->el('TaxableBase');
            $this->addChild($taxableBase, 'TotalAmount', $this->amount($base));
            $tax->appendChild($taxableBase);

            $vatAmount = $lines->sum(fn ($l) => (float) $l->vat_amount);
            $taxAmount = $this->el('TaxAmount');
            $this->addChild($taxAmount, 'TotalAmount', $this->amount($vatAmount));
            $tax->appendChild($taxAmount);

            // Equivalence surcharge
            $totalSurcharge = $lines->sum(fn ($l) => (float) $l->surcharge_amount);
            if ($totalSurcharge > 0) {
                $surchargeRate = (float) $lines->first()->surcharge_rate;
                $equivalence = $this->el('EquivalenceSurcharge');
                $this->addChild($equivalence, 'SurchargeRate', $this->amount($surchargeRate));
                $surchargeAmount = $this->el('SurchargeAmount');
                $this->addChild($surchargeAmount, 'TotalAmount', $this->amount($totalSurcharge));
                $equivalence->appendChild($surchargeAmount);
                $tax->appendChild($equivalence);
            }

            $taxesOutputs->appendChild($tax);
        }

        return $taxesOutputs;
    }

    private function buildTaxesWithheld(Document $document): DOMElement
    {
        $taxesWithheld = $this->el('TaxesWithheld');

        // Group lines by IRPF rate
        $grouped = $document->lines
            ->filter(fn ($l) => (float) $l->irpf_rate > 0)
            ->groupBy(fn ($l) => $this->amount($l->irpf_rate));

        foreach ($grouped as $irpfRate => $lines) {
            $tax = $this->el('Tax');
            $this->addChild($tax, 'TaxTypeCode', '04'); // IRPF
            $this->addChild($tax, 'TaxRate', $this->amount($irpfRate));

            $base = $lines->sum(fn ($l) => (float) $l->line_total);
            $taxableBase = $this->el('TaxableBase');
            $this->addChild($taxableBase, 'TotalAmount', $this->amount($base));
            $tax->appendChild($taxableBase);

            $irpfAmount = $lines->sum(fn ($l) => (float) $l->irpf_amount);
            $taxAmount = $this->el('TaxAmount');
            $this->addChild($taxAmount, 'TotalAmount', $this->amount($irpfAmount));
            $tax->appendChild($taxAmount);

            $taxesWithheld->appendChild($tax);
        }

        return $taxesWithheld;
    }

    private function buildInvoiceTotals(Document $document): DOMElement
    {
        $totals = $this->el('InvoiceTotals');

        $this->addChild($totals, 'TotalGrossAmount', $this->amount($document->subtotal));

        // General discounts
        $globalDiscount = (float) $document->global_discount_amount;
        if ($globalDiscount > 0) {
            $generalDiscounts = $this->el('GeneralDiscounts');
            $discount = $this->el('Discount');
            $this->addChild($discount, 'DiscountReason', 'Descuento global');
            $this->addChild($discount, 'DiscountRate', $this->amount($document->global_discount_percent));
            $this->addChild($discount, 'DiscountAmount', $this->amount($globalDiscount));
            $generalDiscounts->appendChild($discount);
            $totals->appendChild($generalDiscounts);
        }

        $this->addChild($totals, 'TotalGeneralDiscounts', $this->amount($globalDiscount));
        $this->addChild($totals, 'TotalGeneralSurcharges', '0.00');
        $this->addChild($totals, 'TotalGrossAmountBeforeTaxes', $this->amount($document->tax_base));
        $this->addChild($totals, 'TotalTaxOutputs', $this->amount((float) $document->total_vat + (float) $document->total_surcharge));
        $this->addChild($totals, 'TotalTaxesWithheld', $this->amount($document->total_irpf));
        $this->addChild($totals, 'InvoiceTotal', $this->amount($document->total));

        $totalOutstandingAmount = $this->el('TotalOutstandingAmount');
        $this->addChild($totalOutstandingAmount, 'TotalAmount', $this->amount($document->total));
        // No need for nested wrapper — FacturaE 3.2.2 uses direct value
        $this->addChild($totals, 'TotalFinancialExpenses', '0.00');

        // Replace the nested element with direct value
        $totals->removeChild($totalOutstandingAmount);

        $this->addChild($totals, 'TotalOutstandingAmount', $this->amount($document->total));
        $this->addChild($totals, 'TotalExecutableAmount', $this->amount($document->total));

        return $totals;
    }

    private function buildItems(Document $document): DOMElement
    {
        $items = $this->el('Items');

        foreach ($document->lines as $line) {
            $invoiceLine = $this->el('InvoiceLine');

            $this->addChild($invoiceLine, 'ItemDescription', mb_substr($line->concept, 0, 2500));
            $this->addChild($invoiceLine, 'Quantity', $this->amount($line->quantity, 4));
            $this->addChild($invoiceLine, 'UnitOfMeasure', $this->mapUnit($line->unit));
            $this->addChild($invoiceLine, 'UnitPriceWithoutTax', $this->amount($line->unit_price, 6));

            $this->addChild($invoiceLine, 'TotalCost', $this->amount((float) $line->quantity * (float) $line->unit_price, 6));

            // Line discount
            $discountAmount = (float) $line->discount_amount;
            if ($discountAmount > 0) {
                $discountsAndRebates = $this->el('DiscountsAndRebates');
                $discount = $this->el('Discount');
                $this->addChild($discount, 'DiscountReason', 'Descuento');
                $this->addChild($discount, 'DiscountRate', $this->amount($line->discount_percent));
                $this->addChild($discount, 'DiscountAmount', $this->amount($discountAmount));
                $discountsAndRebates->appendChild($discount);
                $invoiceLine->appendChild($discountsAndRebates);
            }

            $this->addChild($invoiceLine, 'GrossAmount', $this->amount($line->line_total));

            // TaxesOutputs per line
            $lineTaxes = $this->el('TaxesOutputs');
            $lineTax = $this->el('Tax');
            $this->addChild($lineTax, 'TaxTypeCode', '01');
            $this->addChild($lineTax, 'TaxRate', $this->amount($line->vat_rate));

            $taxableBase = $this->el('TaxableBase');
            $this->addChild($taxableBase, 'TotalAmount', $this->amount($line->line_total));
            $lineTax->appendChild($taxableBase);

            $taxAmount = $this->el('TaxAmount');
            $this->addChild($taxAmount, 'TotalAmount', $this->amount($line->vat_amount));
            $lineTax->appendChild($taxAmount);

            // Line equivalence surcharge
            if ((float) $line->surcharge_amount > 0) {
                $equivalence = $this->el('EquivalenceSurcharge');
                $this->addChild($equivalence, 'SurchargeRate', $this->amount($line->surcharge_rate));
                $surchargeAmt = $this->el('SurchargeAmount');
                $this->addChild($surchargeAmt, 'TotalAmount', $this->amount($line->surcharge_amount));
                $equivalence->appendChild($surchargeAmt);
                $lineTax->appendChild($equivalence);
            }

            $lineTaxes->appendChild($lineTax);
            $invoiceLine->appendChild($lineTaxes);

            // TaxesWithheld per line (IRPF)
            if ((float) $line->irpf_rate > 0) {
                $lineWithheld = $this->el('TaxesWithheld');
                $lineIrpf = $this->el('Tax');
                $this->addChild($lineIrpf, 'TaxTypeCode', '04');
                $this->addChild($lineIrpf, 'TaxRate', $this->amount($line->irpf_rate));

                $irpfBase = $this->el('TaxableBase');
                $this->addChild($irpfBase, 'TotalAmount', $this->amount($line->line_total));
                $lineIrpf->appendChild($irpfBase);

                $irpfAmount = $this->el('TaxAmount');
                $this->addChild($irpfAmount, 'TotalAmount', $this->amount($line->irpf_amount));
                $lineIrpf->appendChild($irpfAmount);

                $lineWithheld->appendChild($lineIrpf);
                $invoiceLine->appendChild($lineWithheld);
            }

            $items->appendChild($invoiceLine);
        }

        return $items;
    }

    // -- Helpers --

    private function personTypeCode(string $nif): string
    {
        if (empty($nif)) {
            return 'F';
        }

        $first = strtoupper(mb_substr(trim($nif), 0, 1));

        // CIF: starts with A-H, J, N, P, Q, R, S, U, V, W
        if (preg_match('/^[A-HJ-NP-SUVW]/', $first)) {
            return 'J';
        }

        // NIF (starts with digit) or NIE (starts with X, Y, Z) → Individual
        return 'F';
    }

    private function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName));

        if (count($parts) <= 1) {
            return ['name' => $fullName, 'firstSurname' => $fullName, 'secondSurname' => ''];
        }

        if (count($parts) === 2) {
            return ['name' => $parts[0], 'firstSurname' => $parts[1], 'secondSurname' => ''];
        }

        return [
            'name' => $parts[0],
            'firstSurname' => $parts[1],
            'secondSurname' => implode(' ', array_slice($parts, 2)),
        ];
    }

    private function mapUnit(string $unit): string
    {
        return match ($unit) {
            'unidad' => '01',
            'hora', 'hour' => '02',
            'kg' => '03',
            'litro', 'l' => '04',
            default => '01',
        };
    }

    private function countryCode(string $country): string
    {
        if (strlen($country) === 3) {
            return $country;
        }

        return match (strtoupper($country)) {
            'ES', 'ESPAÑA', 'SPAIN' => 'ESP',
            default => $country,
        };
    }

    private function amount(mixed $value, int $decimals = 2): string
    {
        return number_format((float) $value, $decimals, '.', '');
    }

    private function el(string $name): DOMElement
    {
        return $this->dom->createElement($name);
    }

    private function addChild(DOMElement $parent, string $name, string $value): void
    {
        $element = $this->dom->createElement($name, htmlspecialchars($value, ENT_XML1, 'UTF-8'));
        $parent->appendChild($element);
    }
}
