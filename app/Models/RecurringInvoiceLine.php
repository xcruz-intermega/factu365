<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringInvoiceLine extends Model
{
    protected $fillable = [
        'recurring_invoice_id',
        'product_id',
        'sort_order',
        'concept',
        'description',
        'quantity',
        'unit_price',
        'unit',
        'discount_percent',
        'vat_rate',
        'exemption_code',
        'irpf_rate',
        'surcharge_rate',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'irpf_rate' => 'decimal:2',
            'surcharge_rate' => 'decimal:2',
        ];
    }

    public function recurringInvoice(): BelongsTo
    {
        return $this->belongsTo(RecurringInvoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
