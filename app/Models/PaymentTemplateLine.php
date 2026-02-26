<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTemplateLine extends Model
{
    use Auditable;
    protected $fillable = [
        'payment_template_id',
        'days_from_issue',
        'percentage',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'days_from_issue' => 'integer',
            'percentage' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PaymentTemplate::class, 'payment_template_id');
    }
}
