<?php

namespace Tests\Unit;

use App\Services\VeriFactu\HashChainService;
use PHPUnit\Framework\TestCase;

class HashChainServiceTest extends TestCase
{
    private HashChainService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HashChainService;
    }

    // -- formatAmount tests --

    public function test_format_amount_removes_trailing_zeros(): void
    {
        $this->assertEquals('123.1', HashChainService::formatAmount(123.10));
        $this->assertEquals('100', HashChainService::formatAmount(100.00));
        $this->assertEquals('99.99', HashChainService::formatAmount(99.99));
        $this->assertEquals('0.5', HashChainService::formatAmount(0.50));
        $this->assertEquals('0', HashChainService::formatAmount(0.00));
    }

    public function test_format_amount_negative(): void
    {
        $this->assertEquals('-10.5', HashChainService::formatAmount(-10.50));
        $this->assertEquals('-100', HashChainService::formatAmount(-100.00));
    }

    public function test_format_amount_small_values(): void
    {
        $this->assertEquals('0.01', HashChainService::formatAmount(0.01));
        $this->assertEquals('0.1', HashChainService::formatAmount(0.10));
        $this->assertEquals('1', HashChainService::formatAmount(1.00));
    }

    public function test_format_amount_large_values(): void
    {
        $this->assertEquals('123456.78', HashChainService::formatAmount(123456.78));
        $this->assertEquals('1000000', HashChainService::formatAmount(1000000.00));
    }

    // -- buildHashInput tests --

    public function test_build_hash_input_format(): void
    {
        $input = $this->service->buildHashInput(
            'A12345678',
            'FACT-2025-00001',
            '15-01-2025',
            'F1',
            210.00,
            1210.00,
            '',
            '2025-01-15T10:30:00+01:00'
        );

        $expected = 'IDEmisorFactura=A12345678'
            . '&NumSerieFactura=FACT-2025-00001'
            . '&FechaExpedicionFactura=15-01-2025'
            . '&TipoFactura=F1'
            . '&CuotaTotal=210'
            . '&ImporteTotal=1210'
            . '&Huella='
            . '&FechaHoraHusoGenRegistro=2025-01-15T10:30:00+01:00';

        $this->assertEquals($expected, $input);
    }

    public function test_build_hash_input_with_previous_hash(): void
    {
        $previousHash = 'abc123def456789012345678901234567890123456789012345678901234';

        $input = $this->service->buildHashInput(
            'B87654321',
            'FACT-2025-00002',
            '20-01-2025',
            'F1',
            42.00,
            242.00,
            $previousHash,
            '2025-01-20T14:00:00+01:00'
        );

        $this->assertStringContainsString('&Huella=' . $previousHash . '&', $input);
    }

    public function test_build_hash_input_amounts_without_trailing_zeros(): void
    {
        $input = $this->service->buildHashInput(
            'A12345678',
            'FACT-2025-00001',
            '15-01-2025',
            'F1',
            21.10,
            121.10,
            '',
            '2025-01-15T10:30:00+01:00'
        );

        $this->assertStringContainsString('CuotaTotal=21.1', $input);
        $this->assertStringContainsString('ImporteTotal=121.1', $input);
    }

    public function test_build_hash_input_zero_vat(): void
    {
        $input = $this->service->buildHashInput(
            'A12345678',
            'FACT-2025-00001',
            '15-01-2025',
            'F1',
            0.00,
            1000.00,
            '',
            '2025-01-15T10:30:00+01:00'
        );

        $this->assertStringContainsString('CuotaTotal=0', $input);
        $this->assertStringContainsString('ImporteTotal=1000', $input);
    }

    // -- computeHash tests --

    public function test_compute_hash_returns_sha256(): void
    {
        $hash = $this->service->computeHash('test input');

        $this->assertEquals(64, strlen($hash));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hash);
    }

    public function test_compute_hash_deterministic(): void
    {
        $input = 'IDEmisorFactura=A12345678&NumSerieFactura=FACT-2025-00001&FechaExpedicionFactura=15-01-2025&TipoFactura=F1&CuotaTotal=210&ImporteTotal=1210&Huella=&FechaHoraHusoGenRegistro=2025-01-15T10:30:00+01:00';

        $hash1 = $this->service->computeHash($input);
        $hash2 = $this->service->computeHash($input);

        $this->assertEquals($hash1, $hash2);
    }

    public function test_compute_hash_different_inputs_produce_different_hashes(): void
    {
        $hash1 = $this->service->computeHash('input A');
        $hash2 = $this->service->computeHash('input B');

        $this->assertNotEquals($hash1, $hash2);
    }

    public function test_compute_hash_matches_known_sha256(): void
    {
        // Known SHA-256 of "hello"
        $hash = $this->service->computeHash('hello');
        $this->assertEquals('2cf24dba5fb0a30e26e83b2ac5b9e29e1b161e5c1fa7425e73043362938b9824', $hash);
    }

    public function test_compute_hash_utf8_encoding(): void
    {
        // Test with Spanish characters
        $hash = $this->service->computeHash('Facturación electrónica');
        $this->assertEquals(64, strlen($hash));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hash);
    }

    // -- End-to-end hash chain test --

    public function test_hash_chain_first_record(): void
    {
        $input = $this->service->buildHashInput(
            'A12345678',
            'FACT-2025-00001',
            '15-01-2025',
            'F1',
            210.00,
            1210.00,
            '', // First record: empty Huella
            '2025-01-15T10:30:00+01:00'
        );

        $hash = $this->service->computeHash($input);

        // Verify it's a valid SHA-256
        $this->assertEquals(64, strlen($hash));

        // Verify the hash is the SHA-256 of the expected input
        $expectedInput = 'IDEmisorFactura=A12345678&NumSerieFactura=FACT-2025-00001&FechaExpedicionFactura=15-01-2025&TipoFactura=F1&CuotaTotal=210&ImporteTotal=1210&Huella=&FechaHoraHusoGenRegistro=2025-01-15T10:30:00+01:00';
        $this->assertEquals(hash('sha256', $expectedInput), $hash);
    }

    public function test_hash_chain_second_record_uses_previous_hash(): void
    {
        // First record
        $input1 = $this->service->buildHashInput(
            'A12345678',
            'FACT-2025-00001',
            '15-01-2025',
            'F1',
            210.00,
            1210.00,
            '',
            '2025-01-15T10:30:00+01:00'
        );
        $hash1 = $this->service->computeHash($input1);

        // Second record uses first hash
        $input2 = $this->service->buildHashInput(
            'A12345678',
            'FACT-2025-00002',
            '20-01-2025',
            'F1',
            42.00,
            242.00,
            $hash1,
            '2025-01-20T14:00:00+01:00'
        );
        $hash2 = $this->service->computeHash($input2);

        // Verify chain: hash2 depends on hash1
        $this->assertNotEquals($hash1, $hash2);
        $this->assertStringContainsString($hash1, $input2);

        // Verify changing the first hash would change the second
        $inputModified = $this->service->buildHashInput(
            'A12345678',
            'FACT-2025-00002',
            '20-01-2025',
            'F1',
            42.00,
            242.00,
            'different_hash_value',
            '2025-01-20T14:00:00+01:00'
        );
        $hashModified = $this->service->computeHash($inputModified);

        $this->assertNotEquals($hash2, $hashModified);
    }

    public function test_hash_chain_three_records(): void
    {
        // Record 1
        $input1 = $this->service->buildHashInput('A12345678', 'FACT-2025-00001', '01-01-2025', 'F1', 21.00, 121.00, '', '2025-01-01T09:00:00+01:00');
        $hash1 = $this->service->computeHash($input1);

        // Record 2
        $input2 = $this->service->buildHashInput('A12345678', 'FACT-2025-00002', '02-01-2025', 'F1', 42.00, 242.00, $hash1, '2025-01-02T10:00:00+01:00');
        $hash2 = $this->service->computeHash($input2);

        // Record 3
        $input3 = $this->service->buildHashInput('A12345678', 'FACT-2025-00003', '03-01-2025', 'F2', 10.00, 110.00, $hash2, '2025-01-03T11:00:00+01:00');
        $hash3 = $this->service->computeHash($input3);

        // All three hashes are different
        $hashes = [$hash1, $hash2, $hash3];
        $this->assertCount(3, array_unique($hashes));

        // Each is valid SHA-256
        foreach ($hashes as $hash) {
            $this->assertEquals(64, strlen($hash));
            $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hash);
        }
    }

    public function test_hash_input_with_rectificative_type(): void
    {
        $input = $this->service->buildHashInput(
            'A12345678',
            'RECT-2025-00001',
            '15-02-2025',
            'R1',
            21.00,
            121.00,
            'abc123',
            '2025-02-15T10:00:00+01:00'
        );

        $this->assertStringContainsString('TipoFactura=R1', $input);
        $this->assertStringContainsString('NumSerieFactura=RECT-2025-00001', $input);
    }
}
