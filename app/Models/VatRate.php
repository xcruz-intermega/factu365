<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VatRate extends Model
{
    protected $fillable = [
        'name',
        'rate',
        'surcharge_rate',
        'is_default',
        'is_exempt',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'surcharge_rate' => 'decimal:2',
            'is_default' => 'boolean',
            'is_exempt' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('rate');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function isInUse(): bool
    {
        $rate = (float) $this->rate;

        $usedInProducts = Product::where('vat_rate', $rate)->exists();
        if ($usedInProducts) return true;

        $usedInLines = DocumentLine::where('vat_rate', $rate)->exists();
        if ($usedInLines) return true;

        $usedInExpenses = Expense::where('vat_rate', $rate)->exists();
        if ($usedInExpenses) return true;

        return false;
    }
}
