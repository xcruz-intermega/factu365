<?php

namespace Tests\Unit;

use App\Services\TaxCalculatorService;
use PHPUnit\Framework\TestCase;

class TaxCalculatorServiceTest extends TestCase
{
    private TaxCalculatorService $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new TaxCalculatorService;
    }

    // -- calculateLine tests --

    public function test_basic_line_calculation(): void
    {
        $result = $this->calculator->calculateLine([
            'quantity' => 2,
            'unit_price' => 100,
            'vat_rate' => 21,
        ]);

        $this->assertEquals(200.00, $result['line_subtotal']);
        $this->assertEquals(0.00, $result['discount_amount']);
        $this->assertEquals(200.00, $result['line_total']);
        $this->assertEquals(42.00, $result['vat_amount']);
        $this->assertEquals(0.00, $result['irpf_amount']);
        $this->assertEquals(0.00, $result['surcharge_amount']);
    }

    public function test_line_with_discount(): void
    {
        $result = $this->calculator->calculateLine([
            'quantity' => 1,
            'unit_price' => 100,
            'discount_percent' => 10,
            'vat_rate' => 21,
        ]);

        $this->assertEquals(100.00, $result['line_subtotal']);
        $this->assertEquals(10.00, $result['discount_amount']);
        $this->assertEquals(90.00, $result['line_total']);
        $this->assertEquals(18.90, $result['vat_amount']);
    }

    public function test_line_with_irpf(): void
    {
        $result = $this->calculator->calculateLine([
            'quantity' => 1,
            'unit_price' => 1000,
            'vat_rate' => 21,
            'irpf_rate' => 15,
        ]);

        $this->assertEquals(1000.00, $result['line_total']);
        $this->assertEquals(210.00, $result['vat_amount']);
        $this->assertEquals(150.00, $result['irpf_amount']);
    }

    public function test_line_with_surcharge(): void
    {
        $result = $this->calculator->calculateLine([
            'quantity' => 1,
            'unit_price' => 100,
            'vat_rate' => 21,
            'surcharge_rate' => 5.2,
        ]);

        $this->assertEquals(100.00, $result['line_total']);
        $this->assertEquals(21.00, $result['vat_amount']);
        $this->assertEquals(5.20, $result['surcharge_amount']);
    }

    public function test_line_with_reduced_vat(): void
    {
        $result = $this->calculator->calculateLine([
            'quantity' => 3,
            'unit_price' => 50,
            'vat_rate' => 10,
        ]);

        $this->assertEquals(150.00, $result['line_subtotal']);
        $this->assertEquals(150.00, $result['line_total']);
        $this->assertEquals(15.00, $result['vat_amount']);
    }

    public function test_line_with_super_reduced_vat(): void
    {
        $result = $this->calculator->calculateLine([
            'quantity' => 1,
            'unit_price' => 200,
            'vat_rate' => 4,
        ]);

        $this->assertEquals(200.00, $result['line_total']);
        $this->assertEquals(8.00, $result['vat_amount']);
    }

    public function test_line_with_zero_vat(): void
    {
        $result = $this->calculator->calculateLine([
            'quantity' => 1,
            'unit_price' => 500,
            'vat_rate' => 0,
        ]);

        $this->assertEquals(500.00, $result['line_total']);
        $this->assertEquals(0.00, $result['vat_amount']);
    }

    public function test_line_defaults(): void
    {
        $result = $this->calculator->calculateLine([]);

        $this->assertEquals(0.00, $result['line_subtotal']);
        $this->assertEquals(0.00, $result['line_total']);
        $this->assertEquals(0.00, $result['vat_amount']);
    }

    public function test_line_rounding(): void
    {
        $result = $this->calculator->calculateLine([
            'quantity' => 3,
            'unit_price' => 33.33,
            'vat_rate' => 21,
        ]);

        $this->assertEquals(99.99, $result['line_subtotal']);
        $this->assertEquals(99.99, $result['line_total']);
        $this->assertEquals(21.00, $result['vat_amount']); // 99.99 * 0.21 = 20.9979 → 21.00
    }

    // -- calculateDocument tests --

    public function test_document_single_line(): void
    {
        $result = $this->calculator->calculateDocument([
            ['quantity' => 1, 'unit_price' => 100, 'vat_rate' => 21],
        ]);

        $this->assertEquals(100.00, $result['subtotal']);
        $this->assertEquals(0.00, $result['total_discount']);
        $this->assertEquals(100.00, $result['tax_base']);
        $this->assertEquals(21.00, $result['total_vat']);
        $this->assertEquals(0.00, $result['total_irpf']);
        $this->assertEquals(0.00, $result['total_surcharge']);
        $this->assertEquals(121.00, $result['total']);
    }

    public function test_document_multiple_lines(): void
    {
        $result = $this->calculator->calculateDocument([
            ['quantity' => 2, 'unit_price' => 50, 'vat_rate' => 21],
            ['quantity' => 1, 'unit_price' => 200, 'vat_rate' => 10],
        ]);

        $this->assertEquals(300.00, $result['subtotal']);
        $this->assertEquals(300.00, $result['tax_base']);
        // VAT: 100*0.21 + 200*0.10 = 21 + 20 = 41
        $this->assertEquals(41.00, $result['total_vat']);
        $this->assertEquals(341.00, $result['total']);
    }

    public function test_document_with_global_discount(): void
    {
        $result = $this->calculator->calculateDocument([
            ['quantity' => 1, 'unit_price' => 100, 'vat_rate' => 21],
        ], 10);

        $this->assertEquals(100.00, $result['subtotal']);
        $this->assertEquals(10.00, $result['global_discount_amount']);
        $this->assertEquals(90.00, $result['tax_base']);
        $this->assertEquals(18.90, $result['total_vat']); // 90 * 0.21
        $this->assertEquals(108.90, $result['total']);
    }

    public function test_document_with_line_and_global_discount(): void
    {
        $result = $this->calculator->calculateDocument([
            ['quantity' => 1, 'unit_price' => 200, 'discount_percent' => 10, 'vat_rate' => 21],
        ], 5);

        // line_subtotal: 200, discount: 20, line_total: 180
        // tax_base_before_global: 180
        // global_discount: 180 * 5% = 9
        // tax_base: 171
        $this->assertEquals(200.00, $result['subtotal']);
        $this->assertEquals(29.00, $result['total_discount']); // 20 + 9
        $this->assertEquals(9.00, $result['global_discount_amount']);
        $this->assertEquals(171.00, $result['tax_base']);
    }

    public function test_document_vat_breakdown(): void
    {
        $result = $this->calculator->calculateDocument([
            ['quantity' => 1, 'unit_price' => 100, 'vat_rate' => 21],
            ['quantity' => 1, 'unit_price' => 100, 'vat_rate' => 10],
            ['quantity' => 1, 'unit_price' => 100, 'vat_rate' => 21],
        ]);

        $this->assertCount(2, $result['vat_breakdown']);

        $breakdown21 = collect($result['vat_breakdown'])->firstWhere('rate', 21.0);
        $breakdown10 = collect($result['vat_breakdown'])->firstWhere('rate', 10.0);

        $this->assertEquals(200.00, $breakdown21['base']);
        $this->assertEquals(42.00, $breakdown21['vat']);
        $this->assertEquals(100.00, $breakdown10['base']);
        $this->assertEquals(10.00, $breakdown10['vat']);
    }

    public function test_document_with_irpf_and_surcharge(): void
    {
        $result = $this->calculator->calculateDocument([
            [
                'quantity' => 1,
                'unit_price' => 1000,
                'vat_rate' => 21,
                'irpf_rate' => 15,
                'surcharge_rate' => 5.2,
            ],
        ]);

        $this->assertEquals(1000.00, $result['tax_base']);
        $this->assertEquals(210.00, $result['total_vat']);
        $this->assertEquals(150.00, $result['total_irpf']);
        $this->assertEquals(52.00, $result['total_surcharge']);
        // total = 1000 + 210 - 150 + 52 = 1112
        $this->assertEquals(1112.00, $result['total']);
    }

    public function test_document_empty_lines(): void
    {
        $result = $this->calculator->calculateDocument([]);

        $this->assertEquals(0.00, $result['subtotal']);
        $this->assertEquals(0.00, $result['total']);
        $this->assertEmpty($result['vat_breakdown']);
    }

    public function test_document_global_discount_proportional_across_rates(): void
    {
        $result = $this->calculator->calculateDocument([
            ['quantity' => 1, 'unit_price' => 100, 'vat_rate' => 21],
            ['quantity' => 1, 'unit_price' => 100, 'vat_rate' => 10],
        ], 10);

        // tax_base_before_global: 200, global discount: 20, tax_base: 180
        // factor: 180/200 = 0.9
        // adjusted base 21%: 100 * 0.9 = 90, VAT: 18.90
        // adjusted base 10%: 100 * 0.9 = 90, VAT: 9.00
        $this->assertEquals(180.00, $result['tax_base']);
        $this->assertEquals(27.90, $result['total_vat']); // 18.90 + 9.00

        $breakdown21 = collect($result['vat_breakdown'])->firstWhere('rate', 21.0);
        $breakdown10 = collect($result['vat_breakdown'])->firstWhere('rate', 10.0);

        $this->assertEquals(90.00, $breakdown21['base']);
        $this->assertEquals(18.90, $breakdown21['vat']);
        $this->assertEquals(90.00, $breakdown10['base']);
        $this->assertEquals(9.00, $breakdown10['vat']);
    }

    // -- getSurchargeRate tests --

    public function test_surcharge_rate_for_standard_vat(): void
    {
        $this->assertEquals(5.2, $this->calculator->getSurchargeRate(21));
    }

    public function test_surcharge_rate_for_reduced_vat(): void
    {
        $this->assertEquals(1.4, $this->calculator->getSurchargeRate(10));
    }

    public function test_surcharge_rate_for_super_reduced_vat(): void
    {
        $this->assertEquals(0.5, $this->calculator->getSurchargeRate(4));
    }

    public function test_surcharge_rate_for_zero_vat(): void
    {
        $this->assertEquals(0, $this->calculator->getSurchargeRate(0));
    }

    public function test_surcharge_rate_for_unknown_vat(): void
    {
        $this->assertEquals(0, $this->calculator->getSurchargeRate(7));
    }
}
