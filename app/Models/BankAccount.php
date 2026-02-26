<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use Auditable;

    protected $fillable = [
        'name',
        'iban',
        'initial_balance',
        'opening_date',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'opening_date' => 'date',
            'initial_balance' => 'decimal:2',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TreasuryEntry::class)->orderByDesc('entry_date');
    }

    public function currentBalance(): float
    {
        return (float) $this->initial_balance + (float) $this->entries()->sum('amount');
    }
}
