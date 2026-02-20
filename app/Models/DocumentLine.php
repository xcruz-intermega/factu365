<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentLine extends Model
{
    protected $fillable = [
        'document_id',
        'product_id',
        'sort_order',
        'concept',
        'description',
        'quantity',
        'unit_price',
        'unit',
        'discount_percent',
        'discount_amount',
        'vat_rate',
        'vat_amount',
        'exemption_code',
        'irpf_rate',
        'irpf_amount',
        'surcharge_rate',
        'surcharge_amount',
        'line_subtotal',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'irpf_rate' => 'decimal:2',
            'irpf_amount' => 'decimal:2',
            'surcharge_rate' => 'decimal:2',
            'surcharge_amount' => 'decimal:2',
            'line_subtotal' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
