<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    // Document types
    public const TYPE_INVOICE = 'invoice';
    public const TYPE_QUOTE = 'quote';
    public const TYPE_DELIVERY_NOTE = 'delivery_note';
    public const TYPE_PROFORMA = 'proforma';
    public const TYPE_RECEIPT = 'receipt';
    public const TYPE_RECTIFICATIVE = 'rectificative';
    public const TYPE_PURCHASE_INVOICE = 'purchase_invoice';

    // Issued statuses
    public const STATUS_DRAFT = 'draft';
    public const STATUS_FINALIZED = 'finalized';
    public const STATUS_SENT = 'sent';
    public const STATUS_PAID = 'paid';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_CANCELLED = 'cancelled';

    // Received statuses
    public const STATUS_REGISTERED = 'registered';

    // Invoice types (AEAT)
    public const INVOICE_TYPE_F1 = 'F1'; // Factura completa
    public const INVOICE_TYPE_F2 = 'F2'; // Factura simplificada
    public const INVOICE_TYPE_F3 = 'F3'; // Factura emitida en sustitución simplificadas

    // Rectificative types (AEAT)
    public const INVOICE_TYPE_R1 = 'R1'; // Rectificativa Art. 80.1, 80.2 y 80.6
    public const INVOICE_TYPE_R2 = 'R2'; // Rectificativa Art. 80.3
    public const INVOICE_TYPE_R3 = 'R3'; // Rectificativa Art. 80.4
    public const INVOICE_TYPE_R4 = 'R4'; // Rectificativa otros
    public const INVOICE_TYPE_R5 = 'R5'; // Rectificativa en facturas simplificadas

    protected $fillable = [
        'document_type',
        'invoice_type',
        'direction',
        'series_id',
        'number',
        'status',
        'client_id',
        'parent_document_id',
        'corrected_document_id',
        'issue_date',
        'due_date',
        'operation_date',
        'subtotal',
        'total_discount',
        'tax_base',
        'total_vat',
        'total_irpf',
        'total_surcharge',
        'total',
        'global_discount_percent',
        'global_discount_amount',
        'regime_key',
        'rectificative_type',
        'verifactu_status',
        'qr_code_url',
        'notes',
        'footer_text',
        'attachment_path',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'operation_date' => 'date',
            'subtotal' => 'decimal:2',
            'total_discount' => 'decimal:2',
            'tax_base' => 'decimal:2',
            'total_vat' => 'decimal:2',
            'total_irpf' => 'decimal:2',
            'total_surcharge' => 'decimal:2',
            'total' => 'decimal:2',
            'global_discount_percent' => 'decimal:2',
            'global_discount_amount' => 'decimal:2',
        ];
    }

    // Relationships

    public function series(): BelongsTo
    {
        return $this->belongsTo(DocumentSeries::class, 'series_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(DocumentLine::class)->orderBy('sort_order');
    }

    public function parentDocument(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_document_id');
    }

    public function correctedDocument(): BelongsTo
    {
        return $this->belongsTo(self::class, 'corrected_document_id');
    }

    public function childDocuments(): HasMany
    {
        return $this->hasMany(self::class, 'parent_document_id');
    }

    public function invoicingRecords(): HasMany
    {
        return $this->hasMany(InvoicingRecord::class);
    }

    public function latestInvoicingRecord()
    {
        return $this->hasOne(InvoicingRecord::class)->latestOfMany();
    }

    // Scopes

    public function scopeIssued($query)
    {
        return $query->where('direction', 'issued');
    }

    public function scopeReceived($query)
    {
        return $query->where('direction', 'received');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeFinalized($query)
    {
        return $query->where('status', '!=', self::STATUS_DRAFT);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('number', 'like', "%{$search}%")
              ->orWhereHas('client', function ($q) use ($search) {
                  $q->where('legal_name', 'like', "%{$search}%")
                    ->orWhere('nif', 'like', "%{$search}%");
              });
        });
    }

    // Helpers

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isFinalized(): bool
    {
        return $this->status !== self::STATUS_DRAFT;
    }

    public function isInvoice(): bool
    {
        return $this->document_type === self::TYPE_INVOICE;
    }

    public function isRectificative(): bool
    {
        return $this->document_type === self::TYPE_RECTIFICATIVE;
    }

    public function canBeEdited(): bool
    {
        return $this->isDraft();
    }

    public function canBeFinalized(): bool
    {
        return $this->isDraft() && $this->lines()->count() > 0 && $this->client_id !== null;
    }

    public function canBeConverted(): bool
    {
        return in_array($this->document_type, [self::TYPE_QUOTE, self::TYPE_DELIVERY_NOTE])
            && $this->isFinalized();
    }

    public static function documentTypeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_INVOICE => 'Factura',
            self::TYPE_QUOTE => 'Presupuesto',
            self::TYPE_DELIVERY_NOTE => 'Albarán',
            self::TYPE_PROFORMA => 'Proforma',
            self::TYPE_RECEIPT => 'Recibo',
            self::TYPE_RECTIFICATIVE => 'Rectificativa',
            self::TYPE_PURCHASE_INVOICE => 'Factura recibida',
            default => $type,
        };
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_FINALIZED => 'Finalizada',
            self::STATUS_SENT => 'Enviada',
            self::STATUS_PAID => 'Pagada',
            self::STATUS_PARTIAL => 'Pago parcial',
            self::STATUS_OVERDUE => 'Vencida',
            self::STATUS_CANCELLED => 'Anulada',
            self::STATUS_REGISTERED => 'Registrada',
            default => $status,
        };
    }

    public static function nextConversionType(string $currentType): ?string
    {
        return match ($currentType) {
            self::TYPE_QUOTE => self::TYPE_DELIVERY_NOTE,
            self::TYPE_DELIVERY_NOTE => self::TYPE_INVOICE,
            default => null,
        };
    }
}
