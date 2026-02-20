<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentSeries extends Model
{
    protected $fillable = [
        'document_type',
        'prefix',
        'next_number',
        'padding',
        'fiscal_year',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'next_number' => 'integer',
            'padding' => 'integer',
            'fiscal_year' => 'integer',
            'is_default' => 'boolean',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'series_id');
    }

    public function scopeForType($query, string $documentType)
    {
        return $query->where('document_type', $documentType);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('fiscal_year', $year);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function formatNumber(int $number): string
    {
        return $this->prefix . str_pad((string) $number, $this->padding, '0', STR_PAD_LEFT);
    }
}
