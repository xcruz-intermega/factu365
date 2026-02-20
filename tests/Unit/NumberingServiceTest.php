<?php

namespace Tests\Unit;

use App\Models\DocumentSeries;
use App\Services\NumberingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NumberingServiceTest extends TestCase
{
    use RefreshDatabase;

    private NumberingService $service;

    protected function afterRefreshingDatabase(): void
    {
        // Create only the document_series table to avoid conflicts with tenant user migrations
        if (! Schema::hasTable('document_series')) {
            Schema::create('document_series', function ($table) {
                $table->id();
                $table->string('document_type');
                $table->string('prefix');
                $table->unsignedInteger('next_number')->default(1);
                $table->unsignedTinyInteger('padding')->default(5);
                $table->unsignedSmallInteger('fiscal_year');
                $table->boolean('is_default')->default(false);
                $table->timestamps();
                $table->unique(['document_type', 'fiscal_year', 'prefix']);
            });
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NumberingService;
    }

    public function test_generates_first_number(): void
    {
        $series = DocumentSeries::create([
            'document_type' => 'invoice',
            'prefix' => 'FACT-2025-',
            'next_number' => 1,
            'padding' => 5,
            'fiscal_year' => 2025,
            'is_default' => true,
        ]);

        $result = $this->service->generateNumber('invoice', null, 2025);

        $this->assertEquals($series->id, $result['series_id']);
        $this->assertEquals('FACT-2025-00001', $result['number']);
        $this->assertEquals(1, $result['raw_number']);
    }

    public function test_increments_number(): void
    {
        DocumentSeries::create([
            'document_type' => 'invoice',
            'prefix' => 'FACT-2025-',
            'next_number' => 1,
            'padding' => 5,
            'fiscal_year' => 2025,
            'is_default' => true,
        ]);

        $first = $this->service->generateNumber('invoice', null, 2025);
        $second = $this->service->generateNumber('invoice', null, 2025);

        $this->assertEquals('FACT-2025-00001', $first['number']);
        $this->assertEquals('FACT-2025-00002', $second['number']);
        $this->assertEquals(1, $first['raw_number']);
        $this->assertEquals(2, $second['raw_number']);
    }

    public function test_generates_number_for_specific_series(): void
    {
        DocumentSeries::create([
            'document_type' => 'invoice',
            'prefix' => 'FACT-2025-',
            'next_number' => 1,
            'padding' => 5,
            'fiscal_year' => 2025,
            'is_default' => true,
        ]);

        $customSeries = DocumentSeries::create([
            'document_type' => 'invoice',
            'prefix' => 'CUST-2025-',
            'next_number' => 100,
            'padding' => 4,
            'fiscal_year' => 2025,
            'is_default' => false,
        ]);

        $result = $this->service->generateNumber('invoice', $customSeries->id, 2025);

        $this->assertEquals($customSeries->id, $result['series_id']);
        $this->assertEquals('CUST-2025-0100', $result['number']);
        $this->assertEquals(100, $result['raw_number']);
    }

    public function test_uses_default_series_when_no_series_specified(): void
    {
        $default = DocumentSeries::create([
            'document_type' => 'quote',
            'prefix' => 'PRES-2025-',
            'next_number' => 5,
            'padding' => 5,
            'fiscal_year' => 2025,
            'is_default' => true,
        ]);

        $result = $this->service->generateNumber('quote', null, 2025);

        $this->assertEquals($default->id, $result['series_id']);
        $this->assertEquals('PRES-2025-00005', $result['number']);
    }

    public function test_throws_when_no_series_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->generateNumber('invoice', null, 2025);
    }

    public function test_uses_current_year_by_default(): void
    {
        $year = now()->year;

        DocumentSeries::create([
            'document_type' => 'invoice',
            'prefix' => "FACT-{$year}-",
            'next_number' => 1,
            'padding' => 5,
            'fiscal_year' => $year,
            'is_default' => true,
        ]);

        $result = $this->service->generateNumber('invoice');

        $this->assertEquals("FACT-{$year}-00001", $result['number']);
    }

    public function test_ensure_series_for_year_creates_all_types(): void
    {
        $this->service->ensureSeriesForYear(2026);

        $expectedTypes = ['invoice', 'quote', 'delivery_note', 'rectificative', 'proforma', 'receipt', 'purchase_invoice'];

        foreach ($expectedTypes as $type) {
            $this->assertDatabaseHas('document_series', [
                'document_type' => $type,
                'fiscal_year' => 2026,
                'is_default' => true,
            ]);
        }

        $this->assertEquals(count($expectedTypes), DocumentSeries::where('fiscal_year', 2026)->count());
    }

    public function test_ensure_series_for_year_is_idempotent(): void
    {
        $this->service->ensureSeriesForYear(2026);
        $this->service->ensureSeriesForYear(2026);

        $this->assertEquals(7, DocumentSeries::where('fiscal_year', 2026)->count());
    }

    public function test_different_types_have_independent_numbering(): void
    {
        DocumentSeries::create([
            'document_type' => 'invoice',
            'prefix' => 'FACT-2025-',
            'next_number' => 1,
            'padding' => 5,
            'fiscal_year' => 2025,
            'is_default' => true,
        ]);

        DocumentSeries::create([
            'document_type' => 'quote',
            'prefix' => 'PRES-2025-',
            'next_number' => 1,
            'padding' => 5,
            'fiscal_year' => 2025,
            'is_default' => true,
        ]);

        $invoice = $this->service->generateNumber('invoice', null, 2025);
        $quote = $this->service->generateNumber('quote', null, 2025);

        $this->assertEquals('FACT-2025-00001', $invoice['number']);
        $this->assertEquals('PRES-2025-00001', $quote['number']);
    }
}
