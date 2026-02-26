<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DocumentDueDate extends Model
{
    use Auditable;
    protected $fillable = [
        'document_id',
        'due_date',
        'amount',
        'percentage',
        'payment_status',
        'payment_date',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'percentage' => 'decimal:2',
            'payment_date' => 'date',
            'sort_order' => 'integer',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    public function treasuryEntry(): HasOne
    {
        return $this->hasOne(TreasuryEntry::class);
    }
}
