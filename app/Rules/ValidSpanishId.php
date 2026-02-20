<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidSpanishId implements ValidationRule
{
    private const NIF_LETTERS = 'TRWAGMYFPDXBNJZSQVHLCKE';

    private const CIF_CONTROL_LETTERS = 'JABCDEFGHI';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = strtoupper(trim($value));

        if (empty($value)) {
            $fail('El NIF/CIF/NIE es obligatorio.');
            return;
        }

        if ($this->isNif($value)) {
            if (! $this->validateNif($value)) {
                $fail('El NIF no es válido. La letra de control no coincide.');
            }
            return;
        }

        if ($this->isNie($value)) {
            if (! $this->validateNie($value)) {
                $fail('El NIE no es válido. La letra de control no coincide.');
            }
            return;
        }

        if ($this->isCif($value)) {
            if (! $this->validateCif($value)) {
                $fail('El CIF no es válido. El dígito de control no coincide.');
            }
            return;
        }

        $fail('El formato del NIF/CIF/NIE no es válido.');
    }

    private function isNif(string $value): bool
    {
        return (bool) preg_match('/^\d{8}[A-Z]$/', $value);
    }

    private function validateNif(string $value): bool
    {
        $number = (int) substr($value, 0, 8);
        $letter = $value[8];

        return $letter === self::NIF_LETTERS[$number % 23];
    }

    private function isNie(string $value): bool
    {
        return (bool) preg_match('/^[XYZ]\d{7}[A-Z]$/', $value);
    }

    private function validateNie(string $value): bool
    {
        $prefix = ['X' => '0', 'Y' => '1', 'Z' => '2'];
        $nieAsNif = $prefix[$value[0]] . substr($value, 1);

        return $this->validateNif($nieAsNif);
    }

    private function isCif(string $value): bool
    {
        return (bool) preg_match('/^[ABCDEFGHJKLMNPQRSUVW]\d{7}[\dA-J]$/', $value);
    }

    private function validateCif(string $value): bool
    {
        $letter = $value[0];
        $digits = substr($value, 1, 7);
        $control = $value[8];

        $sumEven = 0;
        $sumOdd = 0;

        for ($i = 0; $i < 7; $i++) {
            $digit = (int) $digits[$i];

            if ($i % 2 === 0) {
                // Odd positions (1, 3, 5, 7) - multiply by 2 and sum digits
                $doubled = $digit * 2;
                $sumOdd += intdiv($doubled, 10) + ($doubled % 10);
            } else {
                // Even positions (2, 4, 6) - just add
                $sumEven += $digit;
            }
        }

        $total = $sumEven + $sumOdd;
        $controlDigit = (10 - ($total % 10)) % 10;

        // Some CIF types use letter control, others digit
        $letterControl = ['K', 'P', 'Q', 'S'];
        $digitControl = ['A', 'B', 'E', 'H'];

        if (in_array($letter, $letterControl)) {
            return $control === self::CIF_CONTROL_LETTERS[$controlDigit];
        }

        if (in_array($letter, $digitControl)) {
            return $control === (string) $controlDigit;
        }

        // Rest can be either
        return $control === (string) $controlDigit
            || $control === self::CIF_CONTROL_LETTERS[$controlDigit];
    }
}
