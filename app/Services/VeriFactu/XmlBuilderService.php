<?php

namespace App\Services\VeriFactu;

use App\Models\CompanyProfile;
use App\Models\Document;
use App\Models\InvoicingRecord;
use DOMDocument;
use DOMElement;

class XmlBuilderService
{
    // AEAT VeriFactu namespaces
    private const NS_SOAP = 'http://schemas.xmlsoap.org/soap/envelope/';
    private const NS_SIF = 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SusFacturacion.xsd';
    private const NS_SIF_R = 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/RespSuSistemaFacturacion.xsd';

    private DOMDocument $dom;

    /**
     * Build the XML for a RegFactuSistemaFacturacion (alta) request.
     */
    public function buildRegistroAlta(InvoicingRecord $record, Document $document, CompanyProfile $company): string
    {
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;

        // SOAP Envelope
        $envelope = $this->dom->createElementNS(self::NS_SOAP, 'soapenv:Envelope');
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:sif', self::NS_SIF);
        $this->dom->appendChild($envelope);

        // SOAP Header (empty but required)
        $header = $this->dom->createElement('soapenv:Header');
        $envelope->appendChild($header);

        // SOAP Body
        $body = $this->dom->createElement('soapenv:Body');
        $envelope->appendChild($body);

        // RegFactuSistemaFacturacion
        $regFactu = $this->dom->createElement('sif:RegFactuSistemaFacturacion');
        $body->appendChild($regFactu);

        // Cabecera
        $cabecera = $this->buildCabecera($company);
        $regFactu->appendChild($cabecera);

        // RegistroFactura
        $registroFactura = $this->dom->createElement('sif:RegistroFactura');
        $regFactu->appendChild($registroFactura);

        // RegistroAlta
        $registroAlta = $this->buildRegistroAltaElement($record, $document, $company);
        $registroFactura->appendChild($registroAlta);

        return $this->dom->saveXML();
    }

    /**
     * Build the XML for an AnulacionFactu (cancellation) request.
     */
    public function buildRegistroAnulacion(InvoicingRecord $record, Document $document, CompanyProfile $company): string
    {
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;

        // SOAP Envelope
        $envelope = $this->dom->createElementNS(self::NS_SOAP, 'soapenv:Envelope');
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:sif', self::NS_SIF);
        $this->dom->appendChild($envelope);

        // SOAP Header
        $header = $this->dom->createElement('soapenv:Header');
        $envelope->appendChild($header);

        // SOAP Body
        $body = $this->dom->createElement('soapenv:Body');
        $envelope->appendChild($body);

        // RegFactuSistemaFacturacion
        $regFactu = $this->dom->createElement('sif:RegFactuSistemaFacturacion');
        $body->appendChild($regFactu);

        // Cabecera
        $cabecera = $this->buildCabecera($company);
        $regFactu->appendChild($cabecera);

        // RegistroFactura
        $registroFactura = $this->dom->createElement('sif:RegistroFactura');
        $regFactu->appendChild($registroFactura);

        // RegistroAnulacion
        $anulacion = $this->buildRegistroAnulacionElement($record, $document, $company);
        $registroFactura->appendChild($anulacion);

        return $this->dom->saveXML();
    }

    private function buildCabecera(CompanyProfile $company): DOMElement
    {
        $cabecera = $this->dom->createElement('sif:Cabecera');

        // ObligadoEmision
        $obligado = $this->dom->createElement('sif:ObligadoEmision');
        $this->addElement($obligado, 'sif:NombreRazon', $company->legal_name);
        $this->addElement($obligado, 'sif:NIF', $company->nif);
        $cabecera->appendChild($obligado);

        // Representante (optional, skip for now)

        // RemisionVoluntaria (optional)
        // For standard submission, we use "0" (normal)
        // "1" would mean resubmission

        return $cabecera;
    }

    private function buildRegistroAltaElement(InvoicingRecord $record, Document $document, CompanyProfile $company): DOMElement
    {
        $alta = $this->dom->createElement('sif:RegistroAlta');

        // IDFactura
        $idFactura = $this->dom->createElement('sif:IDFactura');
        $this->addElement($idFactura, 'sif:IDEmisorFactura', $company->nif);
        $this->addElement($idFactura, 'sif:NumSerieFactura', $document->number);
        $this->addElement($idFactura, 'sif:FechaExpedicionFactura', $document->issue_date->format('d-m-Y'));
        $alta->appendChild($idFactura);

        // NombreRazonEmisor
        $this->addElement($alta, 'sif:NombreRazonEmisor', $company->legal_name);

        // TipoFactura
        $this->addElement($alta, 'sif:TipoFactura', $document->invoice_type ?? 'F1');

        // TipoRectificativa (only for rectificative invoices)
        if ($document->isRectificative() && $document->rectificative_type) {
            $tipoRect = $document->rectificative_type === 'substitution' ? 'S' : 'I';
            $this->addElement($alta, 'sif:TipoRectificativa', $tipoRect);

            // FacturasRectificadas
            if ($document->corrected_document_id) {
                $corrected = $document->correctedDocument;
                if ($corrected) {
                    $facturasRect = $this->dom->createElement('sif:FacturasRectificadas');
                    $idFacturaRect = $this->dom->createElement('sif:IDFacturaRectificada');
                    $this->addElement($idFacturaRect, 'sif:IDEmisorFactura', $company->nif);
                    $this->addElement($idFacturaRect, 'sif:NumSerieFactura', $corrected->number);
                    $this->addElement($idFacturaRect, 'sif:FechaExpedicionFactura', $corrected->issue_date->format('d-m-Y'));
                    $facturasRect->appendChild($idFacturaRect);
                    $alta->appendChild($facturasRect);
                }
            }
        }

        // DescripcionOperacion
        $description = $this->getOperationDescription($document);
        $this->addElement($alta, 'sif:DescripcionOperacion', $description);

        // Destinatarios (client data)
        if ($document->client) {
            $destinatarios = $this->dom->createElement('sif:Destinatarios');
            $idDestinatario = $this->dom->createElement('sif:IDDestinatario');
            $this->addElement($idDestinatario, 'sif:NombreRazon', $document->client->legal_name);

            // NIF or IDOtro depending on whether it's a Spanish NIF
            $this->addElement($idDestinatario, 'sif:NIF', $document->client->nif);

            $destinatarios->appendChild($idDestinatario);
            $alta->appendChild($destinatarios);
        }

        // Desglose (tax breakdown)
        $desglose = $this->buildDesglose($document);
        $alta->appendChild($desglose);

        // CuotaTotal
        $this->addElement($alta, 'sif:CuotaTotal', HashChainService::formatAmount((float) $document->total_vat));

        // ImporteTotal
        $this->addElement($alta, 'sif:ImporteTotal', HashChainService::formatAmount((float) $document->total));

        // Encadenamiento
        $encadenamiento = $this->dom->createElement('sif:Encadenamiento');
        if ($record->previous_hash === '') {
            $this->addElement($encadenamiento, 'sif:PrimerRegistro', 'S');
        } else {
            $regAnterior = $this->dom->createElement('sif:RegistroAnterior');
            $this->addElement($regAnterior, 'sif:Huella', $record->previous_hash);
            $encadenamiento->appendChild($regAnterior);
        }
        $alta->appendChild($encadenamiento);

        // SistemaInformatico
        $sistemaInfo = $this->buildSistemaInformatico($company);
        $alta->appendChild($sistemaInfo);

        // FechaHoraHusoGenRegistro
        $this->addElement($alta, 'sif:FechaHoraHusoGenRegistro', $record->fecha_hora_generacion);

        // Huella
        $this->addElement($alta, 'sif:Huella', $record->hash);

        return $alta;
    }

    private function buildRegistroAnulacionElement(InvoicingRecord $record, Document $document, CompanyProfile $company): DOMElement
    {
        $anulacion = $this->dom->createElement('sif:RegistroAnulacion');

        // IDFactura
        $idFactura = $this->dom->createElement('sif:IDFactura');
        $this->addElement($idFactura, 'sif:IDEmisorFactura', $company->nif);
        $this->addElement($idFactura, 'sif:NumSerieFactura', $document->number);
        $this->addElement($idFactura, 'sif:FechaExpedicionFactura', $document->issue_date->format('d-m-Y'));
        $anulacion->appendChild($idFactura);

        // Encadenamiento
        $encadenamiento = $this->dom->createElement('sif:Encadenamiento');
        if ($record->previous_hash === '') {
            $this->addElement($encadenamiento, 'sif:PrimerRegistro', 'S');
        } else {
            $regAnterior = $this->dom->createElement('sif:RegistroAnterior');
            $this->addElement($regAnterior, 'sif:Huella', $record->previous_hash);
            $encadenamiento->appendChild($regAnterior);
        }
        $anulacion->appendChild($encadenamiento);

        // SistemaInformatico
        $sistemaInfo = $this->buildSistemaInformatico($company);
        $anulacion->appendChild($sistemaInfo);

        // FechaHoraHusoGenRegistro
        $this->addElement($anulacion, 'sif:FechaHoraHusoGenRegistro', $record->fecha_hora_generacion);

        // Huella
        $this->addElement($anulacion, 'sif:Huella', $record->hash);

        return $anulacion;
    }

    private function buildDesglose(Document $document): DOMElement
    {
        $desglose = $this->dom->createElement('sif:Desglose');

        // Load lines grouped by VAT rate
        $document->loadMissing('lines');
        $grouped = $document->lines->groupBy(fn ($line) => number_format((float) $line->vat_rate, 2, '.', ''));

        foreach ($grouped as $vatRate => $lines) {
            $detalle = $this->dom->createElement('sif:DetalleDesglose');

            // ClaveRegimen
            $this->addElement($detalle, 'sif:ClaveRegimen', $document->regime_key ?? '01');

            // Check if exempt
            $isExempt = (float) $vatRate === 0.0;

            if ($isExempt) {
                // CalificacionOperacion for exempt
                $exemptionCode = $lines->first()->exemption_code ?? 'E1';
                $this->addElement($detalle, 'sif:CalificacionOperacion', $exemptionCode);
            }

            // TipoImpositivo
            $this->addElement($detalle, 'sif:TipoImpositivo', HashChainService::formatAmount((float) $vatRate));

            // BaseImponibleOimporteNoSujeto
            $base = $lines->sum(fn ($l) => (float) $l->line_total);
            $this->addElement($detalle, 'sif:BaseImponibleOimporteNoSujeto', HashChainService::formatAmount($base));

            if (! $isExempt) {
                // CuotaRepercutida
                $cuota = $lines->sum(fn ($l) => (float) $l->vat_amount);
                $this->addElement($detalle, 'sif:CuotaRepercutida', HashChainService::formatAmount($cuota));
            }

            // TipoRecargoEquivalencia + CuotaRecargoEquivalencia
            $totalSurcharge = $lines->sum(fn ($l) => (float) $l->surcharge_amount);
            if ($totalSurcharge > 0) {
                $surchargeRate = (float) $lines->first()->surcharge_rate;
                $this->addElement($detalle, 'sif:TipoRecargoEquivalencia', HashChainService::formatAmount($surchargeRate));
                $this->addElement($detalle, 'sif:CuotaRecargoEquivalencia', HashChainService::formatAmount($totalSurcharge));
            }

            $desglose->appendChild($detalle);
        }

        return $desglose;
    }

    private function buildSistemaInformatico(CompanyProfile $company): DOMElement
    {
        $sistema = $this->dom->createElement('sif:SistemaInformatico');

        $this->addElement($sistema, 'sif:NombreRazon', $company->software_name ?? 'Factu01');
        $this->addElement($sistema, 'sif:NIF', $company->software_nif ?? $company->nif);
        $this->addElement($sistema, 'sif:NombreSistemaInformatico', $company->software_name ?? 'Factu01');
        $this->addElement($sistema, 'sif:IdSistemaInformatico', $company->software_id ?? 'FACTU01-001');
        $this->addElement($sistema, 'sif:Version', $company->software_version ?? '1.0');
        $this->addElement($sistema, 'sif:NumeroInstalacion', '1');
        $this->addElement($sistema, 'sif:TipoUsoPoswordsibleSoloVerifactu', 'S');

        return $sistema;
    }

    private function getOperationDescription(Document $document): string
    {
        $typeLabel = Document::documentTypeLabel($document->document_type);

        return "Prestación de servicios / Entrega de bienes - {$typeLabel} {$document->number}";
    }

    private function addElement(DOMElement $parent, string $name, string $value): void
    {
        $element = $this->dom->createElement($name, htmlspecialchars($value, ENT_XML1, 'UTF-8'));
        $parent->appendChild($element);
    }
}
