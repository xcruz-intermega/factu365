<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    use Auditable;
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

    // Non-fiscal statuses (quotes / delivery notes)
    public const STATUS_CREATED = 'created';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CONVERTED = 'converted';

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
        'title',
        'status',
        'accounted',
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
            'accounted' => 'boolean',
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

    public function dueDates(): HasMany
    {
        return $this->hasMany(DocumentDueDate::class)->orderBy('sort_order');
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

    public function isNonFiscalType(): bool
    {
        return in_array($this->document_type, [self::TYPE_QUOTE, self::TYPE_DELIVERY_NOTE]);
    }

    public function isAccountable(): bool
    {
        return in_array($this->document_type, [self::TYPE_INVOICE, self::TYPE_RECTIFICATIVE, self::TYPE_PURCHASE_INVOICE]);
    }

    public function canBeEdited(): bool
    {
        if ($this->isNonFiscalType()) {
            return ! in_array($this->status, [self::STATUS_CONVERTED, self::STATUS_CANCELLED]);
        }

        return $this->isDraft();
    }

    public function canBeFinalized(): bool
    {
        if ($this->isNonFiscalType()) {
            return false;
        }

        return $this->isDraft() && $this->lines()->count() > 0 && $this->client_id !== null;
    }

    public function canBeConverted(): bool
    {
        if ($this->document_type === self::TYPE_QUOTE) {
            return in_array($this->status, [self::STATUS_CREATED, self::STATUS_SENT, self::STATUS_ACCEPTED]);
        }

        if ($this->document_type === self::TYPE_DELIVERY_NOTE) {
            return in_array($this->status, [self::STATUS_CREATED, self::STATUS_SENT]);
        }

        return false;
    }

    public function canBeDeleted(): bool
    {
        if ($this->isNonFiscalType()) {
            return in_array($this->status, [self::STATUS_CREATED, self::STATUS_DRAFT]);
        }

        return $this->isDraft();
    }

    public function conversionTargets(): array
    {
        if ($this->document_type === self::TYPE_QUOTE) {
            return [self::TYPE_DELIVERY_NOTE, self::TYPE_INVOICE];
        }

        if ($this->document_type === self::TYPE_DELIVERY_NOTE) {
            return [self::TYPE_INVOICE];
        }

        return [];
    }

    public static function documentTypeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_INVOICE => __('documents.type_invoice'),
            self::TYPE_QUOTE => __('documents.type_quote'),
            self::TYPE_DELIVERY_NOTE => __('documents.type_delivery_note'),
            self::TYPE_PROFORMA => __('documents.type_proforma'),
            self::TYPE_RECEIPT => __('documents.type_receipt'),
            self::TYPE_RECTIFICATIVE => __('documents.type_rectificative'),
            self::TYPE_PURCHASE_INVOICE => __('documents.type_purchase_invoice'),
            default => $type,
        };
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_DRAFT => __('common.status_draft'),
            self::STATUS_FINALIZED => __('common.status_finalized'),
            self::STATUS_SENT => __('common.status_sent'),
            self::STATUS_PAID => __('common.status_paid'),
            self::STATUS_PARTIAL => __('common.status_partial'),
            self::STATUS_OVERDUE => __('common.status_overdue'),
            self::STATUS_CANCELLED => __('common.status_cancelled'),
            self::STATUS_REGISTERED => __('common.status_registered'),
            self::STATUS_CREATED => __('common.status_created'),
            self::STATUS_ACCEPTED => __('common.status_accepted'),
            self::STATUS_REJECTED => __('common.status_rejected'),
            self::STATUS_CONVERTED => __('common.status_converted'),
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
