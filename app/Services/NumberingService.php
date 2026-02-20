<?php

namespace App\Services;

use App\Models\DocumentSeries;
use Illuminate\Support\Facades\DB;

class NumberingService
{
    /**
     * Generate the next document number atomically.
     * Uses SELECT FOR UPDATE to prevent duplicate numbers under concurrency.
     */
    public function generateNumber(string $documentType, ?int $seriesId = null, ?int $fiscalYear = null): array
    {
        $fiscalYear = $fiscalYear ?? now()->year;

        return DB::transaction(function () use ($documentType, $seriesId, $fiscalYear) {
            $query = DocumentSeries::query()
                ->where('document_type', $documentType)
                ->where('fiscal_year', $fiscalYear);

            if ($seriesId) {
                $query->where('id', $seriesId);
            } else {
                $query->where('is_default', true);
            }

            // Lock the row for update
            $series = $query->lockForUpdate()->firstOrFail();

            $currentNumber = $series->next_number;
            $formattedNumber = $series->formatNumber($currentNumber);

            // Increment for next call
            $series->increment('next_number');

            return [
                'series_id' => $series->id,
                'number' => $formattedNumber,
                'raw_number' => $currentNumber,
            ];
        });
    }

    /**
     * Create a series if it doesn't exist for the given year.
     */
    public function ensureSeriesForYear(int $year): void
    {
        $types = [
            'invoice' => 'FACT',
            'quote' => 'PRES',
            'delivery_note' => 'ALB',
            'rectificative' => 'RECT',
            'proforma' => 'PROF',
            'receipt' => 'REC',
            'purchase_invoice' => 'COMP',
        ];

        foreach ($types as $type => $prefix) {
            DocumentSeries::firstOrCreate([
                'document_type' => $type,
                'fiscal_year' => $year,
                'is_default' => true,
            ], [
                'prefix' => "{$prefix}-{$year}-",
                'next_number' => 1,
                'padding' => 5,
            ]);
        }
    }
}
