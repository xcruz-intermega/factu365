<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientDiscount extends Model
{
    protected $fillable = [
        'client_id',
        'discount_type',
        'discount_percent',
        'product_type',
        'product_family_id',
        'min_amount',
        'valid_from',
        'valid_to',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'discount_percent' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'valid_from' => 'date',
            'valid_to' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function productFamily(): BelongsTo
    {
        return $this->belongsTo(ProductFamily::class);
    }

    public function isValidNow(): bool
    {
        $today = Carbon::today();

        if ($this->valid_from && $today->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_to && $today->gt($this->valid_to)) {
            return false;
        }

        return true;
    }
}
