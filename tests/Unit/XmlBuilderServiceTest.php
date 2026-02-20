<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\CompanyProfile;
use App\Models\Document;
use App\Models\DocumentLine;
use App\Models\InvoicingRecord;
use App\Services\VeriFactu\XmlBuilderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class XmlBuilderServiceTest extends TestCase
{
    use RefreshDatabase;

    private XmlBuilderService $xmlBuilder;

    protected function afterRefreshingDatabase(): void
    {
        foreach (['company_profiles', 'clients', 'document_series', 'documents', 'document_lines', 'invoicing_records'] as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }
        }

        // Create only needed tables
        if (! Schema::hasTable('company_profiles')) {
            Schema::create('company_profiles', function ($t) {
                $t->id();
                $t->string('legal_name');
                $t->string('trade_name')->nullable();
                $t->string('nif', 20);
                $t->string('software_id')->nullable();
                $t->string('software_name')->nullable();
                $t->string('software_version')->nullable();
                $t->string('software_nif')->nullable();
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('clients')) {
            Schema::create('clients', function ($t) {
                $t->id();
                $t->string('legal_name');
                $t->string('nif', 20);
                $t->string('type')->default('customer');
                $t->timestamps();
                $t->softDeletes();
            });
        }

        if (! Schema::hasTable('document_series')) {
            Schema::create('document_series', function ($t) {
                $t->id();
                $t->string('document_type');
                $t->string('prefix');
                $t->unsignedInteger('next_number')->default(1);
                $t->unsignedTinyInteger('padding')->default(5);
                $t->unsignedSmallInteger('fiscal_year');
                $t->boolean('is_default')->default(false);
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('documents')) {
            Schema::create('documents', function ($t) {
                $t->id();
                $t->string('document_type');
                $t->string('invoice_type')->nullable();
                $t->string('direction')->default('issued');
                $t->unsignedBigInteger('series_id')->nullable();
                $t->string('number')->nullable();
                $t->string('status')->default('draft');
                $t->unsignedBigInteger('client_id')->nullable();
                $t->unsignedBigInteger('parent_document_id')->nullable();
                $t->unsignedBigInteger('corrected_document_id')->nullable();
                $t->date('issue_date');
                $t->date('due_date')->nullable();
                $t->date('operation_date')->nullable();
                $t->decimal('subtotal', 14, 2)->default(0);
                $t->decimal('total_discount', 14, 2)->default(0);
                $t->decimal('tax_base', 14, 2)->default(0);
                $t->decimal('total_vat', 14, 2)->default(0);
                $t->decimal('total_irpf', 14, 2)->default(0);
                $t->decimal('total_surcharge', 14, 2)->default(0);
                $t->decimal('total', 14, 2)->default(0);
                $t->decimal('global_discount_percent', 5, 2)->default(0);
                $t->decimal('global_discount_amount', 14, 2)->default(0);
                $t->string('regime_key')->nullable();
                $t->string('rectificative_type')->nullable();
                $t->string('verifactu_status')->nullable();
                $t->string('qr_code_url')->nullable();
                $t->text('notes')->nullable();
                $t->text('footer_text')->nullable();
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('document_lines')) {
            Schema::create('document_lines', function ($t) {
                $t->id();
                $t->unsignedBigInteger('document_id');
                $t->unsignedBigInteger('product_id')->nullable();
                $t->unsignedInteger('sort_order')->default(0);
                $t->string('concept');
                $t->text('description')->nullable();
                $t->decimal('quantity', 12, 4)->default(1);
                $t->decimal('unit_price', 14, 2)->default(0);
                $t->string('unit')->nullable();
                $t->decimal('discount_percent', 5, 2)->default(0);
                $t->decimal('discount_amount', 14, 2)->default(0);
                $t->decimal('vat_rate', 5, 2)->default(21);
                $t->decimal('vat_amount', 14, 2)->default(0);
                $t->string('exemption_code')->nullable();
                $t->decimal('irpf_rate', 5, 2)->default(0);
                $t->decimal('irpf_amount', 14, 2)->default(0);
                $t->decimal('surcharge_rate', 5, 2)->default(0);
                $t->decimal('surcharge_amount', 14, 2)->default(0);
                $t->decimal('line_subtotal', 14, 2)->default(0);
                $t->decimal('line_total', 14, 2)->default(0);
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('invoicing_records')) {
            Schema::create('invoicing_records', function ($t) {
                $t->id();
                $t->unsignedBigInteger('document_id');
                $t->string('record_type')->default('alta');
                $t->string('id_emisor_factura', 20);
                $t->string('num_serie_factura');
                $t->string('fecha_expedicion', 10);
                $t->string('tipo_factura', 5);
                $t->string('cuota_total');
                $t->string('importe_total');
                $t->string('previous_hash', 64)->default('');
                $t->string('hash', 64);
                $t->string('fecha_hora_generacion');
                $t->longText('xml_content')->nullable();
                $t->string('submission_status')->default('pending');
                $t->string('aeat_csv')->nullable();
                $t->text('error_message')->nullable();
                $t->timestamps();
            });
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->xmlBuilder = new XmlBuilderService;
    }

    public function test_build_registro_alta_produces_valid_xml(): void
    {
        $company = CompanyProfile::create([
            'legal_name' => 'Test Company SL',
            'nif' => 'A12345678',
            'software_name' => 'Factu01',
            'software_id' => 'FACTU01-001',
            'software_version' => '1.0',
            'software_nif' => 'B87654321',
        ]);

        $client = Client::create([
            'legal_name' => 'Client Corp',
            'nif' => 'B11111111',
            'type' => 'customer',
        ]);

        $document = Document::create([
            'document_type' => 'invoice',
            'invoice_type' => 'F1',
            'direction' => 'issued',
            'status' => 'finalized',
            'number' => 'FACT-2025-00001',
            'client_id' => $client->id,
            'issue_date' => '2025-01-15',
            'tax_base' => 1000.00,
            'total_vat' => 210.00,
            'total' => 1210.00,
            'regime_key' => '01',
        ]);

        DocumentLine::create([
            'document_id' => $document->id,
            'concept' => 'Servicio de consultoría',
            'quantity' => 1,
            'unit_price' => 1000.00,
            'vat_rate' => 21.00,
            'vat_amount' => 210.00,
            'line_subtotal' => 1000.00,
            'line_total' => 1000.00,
        ]);

        $record = InvoicingRecord::create([
            'document_id' => $document->id,
            'record_type' => 'alta',
            'id_emisor_factura' => 'A12345678',
            'num_serie_factura' => 'FACT-2025-00001',
            'fecha_expedicion' => '15-01-2025',
            'tipo_factura' => 'F1',
            'cuota_total' => '210',
            'importe_total' => '1210',
            'previous_hash' => '',
            'hash' => str_repeat('a', 64),
            'fecha_hora_generacion' => '2025-01-15T10:30:00+01:00',
        ]);

        $xml = $this->xmlBuilder->buildRegistroAlta($record, $document, $company);

        // Validate it's valid XML
        $dom = new \DOMDocument();
        $this->assertTrue($dom->loadXML($xml));

        // Check SOAP envelope
        $this->assertStringContainsString('soapenv:Envelope', $xml);
        $this->assertStringContainsString('soapenv:Body', $xml);

        // Check key elements
        $this->assertStringContainsString('RegFactuSistemaFacturacion', $xml);
        $this->assertStringContainsString('RegistroAlta', $xml);
        $this->assertStringContainsString('A12345678', $xml); // NIF emisor
        $this->assertStringContainsString('FACT-2025-00001', $xml); // Num serie
        $this->assertStringContainsString('15-01-2025', $xml); // Fecha
        $this->assertStringContainsString('F1', $xml); // Tipo factura
        $this->assertStringContainsString('Test Company SL', $xml); // Nombre
        $this->assertStringContainsString('Client Corp', $xml); // Destinatario
        $this->assertStringContainsString('PrimerRegistro', $xml); // First record
        $this->assertStringContainsString(str_repeat('a', 64), $xml); // Hash
    }

    public function test_build_registro_alta_with_previous_hash(): void
    {
        $company = CompanyProfile::create([
            'legal_name' => 'Test SL',
            'nif' => 'A12345678',
        ]);

        $document = Document::create([
            'document_type' => 'invoice',
            'invoice_type' => 'F1',
            'direction' => 'issued',
            'status' => 'finalized',
            'number' => 'FACT-2025-00002',
            'issue_date' => '2025-01-20',
            'tax_base' => 100.00,
            'total_vat' => 21.00,
            'total' => 121.00,
            'regime_key' => '01',
        ]);

        DocumentLine::create([
            'document_id' => $document->id,
            'concept' => 'Test',
            'quantity' => 1,
            'unit_price' => 100.00,
            'vat_rate' => 21.00,
            'vat_amount' => 21.00,
            'line_subtotal' => 100.00,
            'line_total' => 100.00,
        ]);

        $previousHash = hash('sha256', 'previous record data');

        $record = InvoicingRecord::create([
            'document_id' => $document->id,
            'record_type' => 'alta',
            'id_emisor_factura' => 'A12345678',
            'num_serie_factura' => 'FACT-2025-00002',
            'fecha_expedicion' => '20-01-2025',
            'tipo_factura' => 'F1',
            'cuota_total' => '21',
            'importe_total' => '121',
            'previous_hash' => $previousHash,
            'hash' => str_repeat('b', 64),
            'fecha_hora_generacion' => '2025-01-20T14:00:00+01:00',
        ]);

        $xml = $this->xmlBuilder->buildRegistroAlta($record, $document, $company);

        // Should NOT contain PrimerRegistro
        $this->assertStringNotContainsString('PrimerRegistro', $xml);
        // Should contain RegistroAnterior with hash
        $this->assertStringContainsString('RegistroAnterior', $xml);
        $this->assertStringContainsString($previousHash, $xml);
    }

    public function test_build_registro_alta_contains_desglose(): void
    {
        $company = CompanyProfile::create([
            'legal_name' => 'Test SL',
            'nif' => 'A12345678',
        ]);

        $document = Document::create([
            'document_type' => 'invoice',
            'invoice_type' => 'F1',
            'direction' => 'issued',
            'status' => 'finalized',
            'number' => 'FACT-2025-00001',
            'issue_date' => '2025-01-15',
            'tax_base' => 200.00,
            'total_vat' => 31.00,
            'total' => 231.00,
            'regime_key' => '01',
        ]);

        // Two lines with different VAT rates
        DocumentLine::create([
            'document_id' => $document->id,
            'concept' => 'Service A',
            'quantity' => 1,
            'unit_price' => 100.00,
            'vat_rate' => 21.00,
            'vat_amount' => 21.00,
            'line_subtotal' => 100.00,
            'line_total' => 100.00,
        ]);

        DocumentLine::create([
            'document_id' => $document->id,
            'concept' => 'Service B',
            'quantity' => 1,
            'unit_price' => 100.00,
            'vat_rate' => 10.00,
            'vat_amount' => 10.00,
            'line_subtotal' => 100.00,
            'line_total' => 100.00,
        ]);

        $record = InvoicingRecord::create([
            'document_id' => $document->id,
            'record_type' => 'alta',
            'id_emisor_factura' => 'A12345678',
            'num_serie_factura' => 'FACT-2025-00001',
            'fecha_expedicion' => '15-01-2025',
            'tipo_factura' => 'F1',
            'cuota_total' => '31',
            'importe_total' => '231',
            'previous_hash' => '',
            'hash' => str_repeat('c', 64),
            'fecha_hora_generacion' => '2025-01-15T10:00:00+01:00',
        ]);

        $xml = $this->xmlBuilder->buildRegistroAlta($record, $document, $company);

        // Should contain Desglose with both rates
        $this->assertStringContainsString('Desglose', $xml);
        $this->assertStringContainsString('DetalleDesglose', $xml);

        // Both VAT rates should appear
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $detalles = $dom->getElementsByTagName('DetalleDesglose');
        $this->assertEquals(2, $detalles->length);
    }

    public function test_build_registro_alta_contains_sistema_informatico(): void
    {
        $company = CompanyProfile::create([
            'legal_name' => 'Test SL',
            'nif' => 'A12345678',
            'software_name' => 'Factu01',
            'software_id' => 'FACTU01-001',
            'software_version' => '1.0',
        ]);

        $document = Document::create([
            'document_type' => 'invoice',
            'invoice_type' => 'F1',
            'direction' => 'issued',
            'status' => 'finalized',
            'number' => 'FACT-2025-00001',
            'issue_date' => '2025-01-15',
            'tax_base' => 100.00,
            'total_vat' => 21.00,
            'total' => 121.00,
            'regime_key' => '01',
        ]);

        DocumentLine::create([
            'document_id' => $document->id,
            'concept' => 'Test',
            'quantity' => 1,
            'unit_price' => 100.00,
            'vat_rate' => 21.00,
            'vat_amount' => 21.00,
            'line_subtotal' => 100.00,
            'line_total' => 100.00,
        ]);

        $record = InvoicingRecord::create([
            'document_id' => $document->id,
            'record_type' => 'alta',
            'id_emisor_factura' => 'A12345678',
            'num_serie_factura' => 'FACT-2025-00001',
            'fecha_expedicion' => '15-01-2025',
            'tipo_factura' => 'F1',
            'cuota_total' => '21',
            'importe_total' => '121',
            'previous_hash' => '',
            'hash' => str_repeat('d', 64),
            'fecha_hora_generacion' => '2025-01-15T10:00:00+01:00',
        ]);

        $xml = $this->xmlBuilder->buildRegistroAlta($record, $document, $company);

        $this->assertStringContainsString('SistemaInformatico', $xml);
        $this->assertStringContainsString('Factu01', $xml);
        $this->assertStringContainsString('FACTU01-001', $xml);
    }
}
