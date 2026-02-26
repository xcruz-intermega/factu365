<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreasuryEntry extends Model
{
    use Auditable;

    public const TYPE_COLLECTION = 'collection';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_MANUAL = 'manual';

    protected $fillable = [
        'bank_account_id',
        'entry_date',
        'concept',
        'amount',
        'entry_type',
        'document_due_date_id',
        'expense_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function documentDueDate(): BelongsTo
    {
        return $this->belongsTo(DocumentDueDate::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }
}
