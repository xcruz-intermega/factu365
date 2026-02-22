<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'legal_name',
        'trade_name',
        'nif',
        'address_street',
        'address_city',
        'address_postal_code',
        'address_province',
        'address_country',
        'phone',
        'email',
        'website',
        'contact_person',
        'payment_terms_days',
        'payment_template_id',
        'payment_method',
        'iban',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'payment_terms_days' => 'integer',
        ];
    }

    public function scopeCustomers($query)
    {
        return $query->whereIn('type', ['customer', 'both']);
    }

    public function scopeSuppliers($query)
    {
        return $query->whereIn('type', ['supplier', 'both']);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('legal_name', 'like', "%{$search}%")
              ->orWhere('trade_name', 'like', "%{$search}%")
              ->orWhere('nif', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    public function paymentTemplate(): BelongsTo
    {
        return $this->belongsTo(PaymentTemplate::class);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(ClientDiscount::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->trade_name ?: $this->legal_name;
    }
}
