<?php

namespace App\Services;

use App\Models\VatRate;

class TaxCalculatorService
{
    // Fallback surcharge rates (used when DB is not available, e.g. tests/migrations)
    private const FALLBACK_SURCHARGE_RATES = [
        '21.00' => 5.2,
        '10.00' => 1.4,
        '4.00' => 0.5,
        '0.00' => 0,
    ];

    private static ?array $surchargeRatesCache = null;

    /**
     * Calculate a single line's amounts.
     */
    public function calculateLine(array $line): array
    {
        $quantity = (float) ($line['quantity'] ?? 1);
        $unitPrice = (float) ($line['unit_price'] ?? 0);
        $discountPercent = (float) ($line['discount_percent'] ?? 0);
        $vatRate = (float) ($line['vat_rate'] ?? 21);
        $irpfRate = (float) ($line['irpf_rate'] ?? 0);
        $surchargeRate = (float) ($line['surcharge_rate'] ?? 0);

        // Line subtotal before discount
        $lineSubtotal = $this->round($quantity * $unitPrice);

        // Discount
        $discountAmount = $this->round($lineSubtotal * $discountPercent / 100);

        // Line total (after discount, before tax) = tax base for this line
        $lineTotal = $this->round($lineSubtotal - $discountAmount);

        // Tax amounts
        $vatAmount = $this->round($lineTotal * $vatRate / 100);
        $irpfAmount = $this->round($lineTotal * $irpfRate / 100);
        $surchargeAmount = $this->round($lineTotal * $surchargeRate / 100);

        return [
            'line_subtotal' => $lineSubtotal,
            'discount_amount' => $discountAmount,
            'line_total' => $lineTotal,
            'vat_amount' => $vatAmount,
            'irpf_amount' => $irpfAmount,
            'surcharge_amount' => $surchargeAmount,
        ];
    }

    /**
     * Calculate document totals from all lines.
     */
    public function calculateDocument(array $lines, float $globalDiscountPercent = 0): array
    {
        $subtotal = 0;
        $totalLineDiscount = 0;
        $calculatedLines = [];

        foreach ($lines as $line) {
            $calc = $this->calculateLine($line);
            $calculatedLines[] = array_merge($line, $calc);

            $subtotal = $this->round($subtotal + $calc['line_subtotal']);
            $totalLineDiscount = $this->round($totalLineDiscount + $calc['discount_amount']);
        }

        // Tax base after line discounts
        $taxBaseBeforeGlobal = $this->round($subtotal - $totalLineDiscount);

        // Global discount
        $globalDiscountAmount = $this->round($taxBaseBeforeGlobal * $globalDiscountPercent / 100);
        $taxBase = $this->round($taxBaseBeforeGlobal - $globalDiscountAmount);

        // Apply global discount proportionally to each line for tax calculation
        $globalFactor = $taxBaseBeforeGlobal > 0
            ? $taxBase / $taxBaseBeforeGlobal
            : 0;

        // Tax breakdown by rate
        $vatBreakdown = [];
        $totalVat = 0;
        $totalIrpf = 0;
        $totalSurcharge = 0;

        foreach ($calculatedLines as &$line) {
            // Adjust line amounts if there's a global discount
            $adjustedBase = $this->round((float) $line['line_total'] * $globalFactor);

            $vatRate = number_format((float) ($line['vat_rate'] ?? 21), 2, '.', '');
            $irpfRate = (float) ($line['irpf_rate'] ?? 0);
            $surchargeRate = (float) ($line['surcharge_rate'] ?? 0);

            $lineVat = $this->round($adjustedBase * (float) $vatRate / 100);
            $lineIrpf = $this->round($adjustedBase * $irpfRate / 100);
            $lineSurcharge = $this->round($adjustedBase * $surchargeRate / 100);

            if (! isset($vatBreakdown[$vatRate])) {
                $vatBreakdown[$vatRate] = [
                    'rate' => (float) $vatRate,
                    'base' => 0,
                    'vat' => 0,
                    'surcharge_rate' => $surchargeRate,
                    'surcharge' => 0,
                ];
            }

            $vatBreakdown[$vatRate]['base'] = $this->round($vatBreakdown[$vatRate]['base'] + $adjustedBase);
            $vatBreakdown[$vatRate]['vat'] = $this->round($vatBreakdown[$vatRate]['vat'] + $lineVat);
            $vatBreakdown[$vatRate]['surcharge'] = $this->round($vatBreakdown[$vatRate]['surcharge'] + $lineSurcharge);

            $totalVat = $this->round($totalVat + $lineVat);
            $totalIrpf = $this->round($totalIrpf + $lineIrpf);
            $totalSurcharge = $this->round($totalSurcharge + $lineSurcharge);
        }

        $total = $this->round($taxBase + $totalVat - $totalIrpf + $totalSurcharge);
        $totalDiscount = $this->round($totalLineDiscount + $globalDiscountAmount);

        return [
            'lines' => $calculatedLines,
            'subtotal' => $subtotal,
            'total_discount' => $totalDiscount,
            'global_discount_amount' => $globalDiscountAmount,
            'tax_base' => $taxBase,
            'total_vat' => $totalVat,
            'total_irpf' => $totalIrpf,
            'total_surcharge' => $totalSurcharge,
            'total' => $total,
            'vat_breakdown' => array_values($vatBreakdown),
        ];
    }

    /**
     * Get the surcharge rate for a given VAT rate.
     */
    public function getSurchargeRate(float $vatRate): float
    {
        $key = number_format($vatRate, 2, '.', '');

        return $this->getSurchargeRatesMap()[$key] ?? 0;
    }

    private function getSurchargeRatesMap(): array
    {
        if (self::$surchargeRatesCache !== null) {
            return self::$surchargeRatesCache;
        }

        try {
            $map = VatRate::pluck('surcharge_rate', 'rate')
                ->mapWithKeys(fn ($surcharge, $rate) => [number_format((float) $rate, 2, '.', '') => (float) $surcharge])
                ->toArray();

            if (!empty($map)) {
                self::$surchargeRatesCache = $map;
                return $map;
            }
        } catch (\Throwable) {
            // DB not available (tests, migrations)
        }

        return self::FALLBACK_SURCHARGE_RATES;
    }

    /**
     * Round to 2 decimal places using banker's rounding.
     */
    private function round(float $value): float
    {
        return round($value, 2);
    }
}
