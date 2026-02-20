<?php

namespace Tests\Unit;

use App\Models\Document;
use App\Services\VeriFactu\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QrCodeServiceTest extends TestCase
{
    use RefreshDatabase;

    private QrCodeService $qrService;

    protected function afterRefreshingDatabase(): void
    {
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
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->qrService = new QrCodeService;
    }

    public function test_build_validation_url_format(): void
    {
        $document = Document::create([
            'document_type' => 'invoice',
            'invoice_type' => 'F1',
            'direction' => 'issued',
            'number' => 'FACT-2025-00001',
            'issue_date' => '2025-01-15',
            'total' => 1210.00,
        ]);

        $url = $this->qrService->buildValidationUrl($document, 'A12345678');

        $this->assertStringContainsString('nif=A12345678', $url);
        $this->assertStringContainsString('numserie=FACT-2025-00001', $url);
        $this->assertStringContainsString('fecha=15-01-2025', $url);
        $this->assertStringContainsString('importe=1210', $url);
    }

    public function test_build_validation_url_uses_config_base(): void
    {
        $document = Document::create([
            'document_type' => 'invoice',
            'number' => 'FACT-2025-00001',
            'issue_date' => '2025-01-15',
            'total' => 121.00,
        ]);

        $url = $this->qrService->buildValidationUrl($document, 'A12345678');

        $baseUrl = config('verifactu.qr_validation_url');
        $this->assertStringStartsWith($baseUrl, $url);
    }

    public function test_build_validation_url_amount_without_trailing_zeros(): void
    {
        $document = Document::create([
            'document_type' => 'invoice',
            'number' => 'FACT-2025-00001',
            'issue_date' => '2025-01-15',
            'total' => 100.00,
        ]);

        $url = $this->qrService->buildValidationUrl($document, 'A12345678');

        // Should be importe=100, not importe=100.00
        $this->assertStringContainsString('importe=100', $url);
        $this->assertStringNotContainsString('importe=100.00', $url);
    }

    public function test_generate_qr_code_returns_png_data(): void
    {
        $document = Document::create([
            'document_type' => 'invoice',
            'number' => 'FACT-2025-00001',
            'issue_date' => '2025-01-15',
            'total' => 1210.00,
        ]);

        $png = $this->qrService->generateQrCode($document, 'A12345678');

        // PNG magic bytes
        $this->assertStringStartsWith("\x89PNG", $png);
        $this->assertGreaterThan(100, strlen($png));
    }

    public function test_generate_qr_code_data_uri(): void
    {
        $document = Document::create([
            'document_type' => 'invoice',
            'number' => 'FACT-2025-00001',
            'issue_date' => '2025-01-15',
            'total' => 1210.00,
        ]);

        $dataUri = $this->qrService->generateQrCodeDataUri($document, 'A12345678');

        $this->assertStringStartsWith('data:image/png;base64,', $dataUri);
    }
}
