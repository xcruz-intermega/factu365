<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AeatSubmission extends Model
{
    use Auditable;

    public function auditExclude(): array
    {
        return ['password', 'remember_token', 'request_xml', 'response_xml'];
    }
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_PARTIALLY_ACCEPTED = 'partially_accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ERROR = 'error';

    protected $fillable = [
        'invoicing_record_id',
        'request_xml',
        'response_xml',
        'http_status',
        'result_status',
        'error_code',
        'error_description',
        'aeat_csv',
        'attempt_number',
    ];

    protected function casts(): array
    {
        return [
            'http_status' => 'integer',
            'attempt_number' => 'integer',
        ];
    }

    public function invoicingRecord(): BelongsTo
    {
        return $this->belongsTo(InvoicingRecord::class);
    }
}
