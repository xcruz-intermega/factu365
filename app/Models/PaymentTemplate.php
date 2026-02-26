<?php

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentTemplate extends Model
{
    use Auditable;
    protected $fillable = [
        'name',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PaymentTemplateLine::class)->orderBy('sort_order');
    }

    /**
     * Generate due dates from this template.
     *
     * @return array<int, array{due_date: string, amount: string, percentage: string, sort_order: int}>
     */
    public function generateDueDates(Carbon $issueDate, float $total): array
    {
        $dueDates = [];
        $remaining = $total;

        $templateLines = $this->lines()->orderBy('sort_order')->get();
        $lastIndex = $templateLines->count() - 1;

        foreach ($templateLines as $index => $line) {
            // For the last line, use remaining to avoid rounding issues
            $amount = $index === $lastIndex
                ? $remaining
                : round($total * $line->percentage / 100, 2);

            $remaining -= $amount;

            $dueDates[] = [
                'due_date' => $issueDate->copy()->addDays($line->days_from_issue)->toDateString(),
                'amount' => number_format($amount, 2, '.', ''),
                'percentage' => number_format($line->percentage, 2, '.', ''),
                'sort_order' => $index + 1,
            ];
        }

        return $dueDates;
    }
}
