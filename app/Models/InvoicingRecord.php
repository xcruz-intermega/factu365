<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoicingRecord extends Model
{
    use Auditable;

    public function auditExclude(): array
    {
        return ['password', 'remember_token', 'xml_content'];
    }
    public const TYPE_ALTA = 'alta';
    public const TYPE_ANULACION = 'anulacion';

    public const STATUS_PENDING = 'pending';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ERROR = 'error';

    protected $fillable = [
        'document_id',
        'record_type',
        'id_emisor_factura',
        'num_serie_factura',
        'fecha_expedicion',
        'tipo_factura',
        'cuota_total',
        'importe_total',
        'previous_hash',
        'hash',
        'fecha_hora_generacion',
        'xml_content',
        'submission_status',
        'aeat_csv',
        'error_message',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AeatSubmission::class);
    }

    public function latestSubmission()
    {
        return $this->hasOne(AeatSubmission::class)->latestOfMany();
    }

    public function isPending(): bool
    {
        return $this->submission_status === self::STATUS_PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->submission_status === self::STATUS_ACCEPTED;
    }
}
