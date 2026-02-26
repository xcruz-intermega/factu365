<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use Auditable;
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'category_id',
        'supplier_client_id',
        'supplier_name',
        'invoice_number',
        'expense_date',
        'due_date',
        'concept',
        'description',
        'subtotal',
        'vat_rate',
        'vat_amount',
        'irpf_rate',
        'irpf_amount',
        'total',
        'payment_status',
        'payment_date',
        'payment_method',
        'attachment_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'due_date' => 'date',
            'payment_date' => 'date',
            'subtotal' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'irpf_rate' => 'decimal:2',
            'irpf_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'supplier_client_id');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::STATUS_PAID;
    }

    public function getSupplierDisplayNameAttribute(): string
    {
        if ($this->supplier) {
            return $this->supplier->trade_name ?: $this->supplier->legal_name;
        }

        return $this->supplier_name ?? '—';
    }

    public function scopeSearch($query, ?string $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('concept', 'like', "%{$search}%")
              ->orWhere('invoice_number', 'like', "%{$search}%")
              ->orWhere('supplier_name', 'like', "%{$search}%")
              ->orWhereHas('supplier', function ($q) use ($search) {
                  $q->where('legal_name', 'like', "%{$search}%");
              });
        });
    }
}
