<?php

namespace Tests\Unit;

use App\Rules\ValidSpanishId;
use PHPUnit\Framework\TestCase;

class ValidSpanishIdTest extends TestCase
{
    private ValidSpanishId $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new ValidSpanishId;
    }

    private function validate(string $value): bool
    {
        $passed = true;
        $this->rule->validate('nif', $value, function () use (&$passed) {
            $passed = false;
        });

        return $passed;
    }

    public function test_valid_nif(): void
    {
        // 12345678 % 23 = 14 → Z
        $this->assertTrue($this->validate('12345678Z'));
        // 00000000 % 23 = 0 → T
        $this->assertTrue($this->validate('00000000T'));
        // 99999999 % 23 = 99999999 mod 23 = 17 → R (wait let me check)
        // Actually: 99999999 / 23 = 4347826.04... → 4347826 * 23 = 99999998 → remainder 1 → R
        $this->assertTrue($this->validate('99999999R'));
    }

    public function test_invalid_nif_wrong_letter(): void
    {
        $this->assertFalse($this->validate('12345678A'));
        $this->assertFalse($this->validate('00000000A'));
    }

    public function test_valid_nie(): void
    {
        // X0000000 → 00000000 % 23 = 0 → T
        $this->assertTrue($this->validate('X0000000T'));
        // Y0000001 → 10000001 % 23 = 15 → S
        $this->assertTrue($this->validate('Y0000001S'));
        // Z0000002 → 20000002 % 23 = 7 → F
        $this->assertTrue($this->validate('Z0000002F'));
    }

    public function test_invalid_nie_wrong_letter(): void
    {
        $this->assertFalse($this->validate('X0000000A'));
        $this->assertFalse($this->validate('Y0000001R'));
    }

    public function test_valid_cif(): void
    {
        // A58818501 - verified by hand
        $this->assertTrue($this->validate('A58818501'));
        // A28015865 - Telefónica
        $this->assertTrue($this->validate('A28015865'));
    }

    public function test_invalid_cif(): void
    {
        // B00000001 - control should be 0, not 1
        $this->assertFalse($this->validate('B00000001'));
    }

    public function test_invalid_format(): void
    {
        $this->assertFalse($this->validate(''));
        $this->assertFalse($this->validate('123'));
        $this->assertFalse($this->validate('ABCDEFGHI'));
        $this->assertFalse($this->validate('1234'));
    }

    public function test_case_insensitive(): void
    {
        $this->assertTrue($this->validate('12345678z'));
        $this->assertTrue($this->validate('x0000000t'));
        $this->assertTrue($this->validate('a58818501'));
    }
}
