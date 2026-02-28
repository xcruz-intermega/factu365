<?php

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringInvoice extends Model
{
    use Auditable;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_FINISHED = 'finished';

    public const UNIT_DAY = 'day';
    public const UNIT_WEEK = 'week';
    public const UNIT_MONTH = 'month';
    public const UNIT_YEAR = 'year';

    protected $fillable = [
        'name',
        'status',
        'client_id',
        'series_id',
        'payment_template_id',
        'invoice_type',
        'regime_key',
        'global_discount_percent',
        'notes',
        'footer_text',
        'interval_value',
        'interval_unit',
        'start_date',
        'next_issue_date',
        'end_date',
        'max_occurrences',
        'occurrences_count',
        'auto_finalize',
        'auto_send_email',
        'email_recipients',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'next_issue_date' => 'date',
            'end_date' => 'date',
            'global_discount_percent' => 'decimal:2',
            'interval_value' => 'integer',
            'max_occurrences' => 'integer',
            'occurrences_count' => 'integer',
            'auto_finalize' => 'boolean',
            'auto_send_email' => 'boolean',
        ];
    }

    // Relationships

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(DocumentSeries::class, 'series_id');
    }

    public function paymentTemplate(): BelongsTo
    {
        return $this->belongsTo(PaymentTemplate::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(RecurringInvoiceLine::class)->orderBy('sort_order');
    }

    public function generatedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'recurring_invoice_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeDue($query, ?Carbon $date = null)
    {
        $date = $date ?? now();

        return $query->where('status', self::STATUS_ACTIVE)
            ->where('next_issue_date', '<=', $date->toDateString());
    }

    // Helpers

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPaused(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    public function isFinished(): bool
    {
        return $this->status === self::STATUS_FINISHED;
    }

    public function shouldFinish(): bool
    {
        if ($this->end_date && $this->next_issue_date->gt($this->end_date)) {
            return true;
        }

        if ($this->max_occurrences && $this->occurrences_count >= $this->max_occurrences) {
            return true;
        }

        return false;
    }

    public function advanceNextIssueDate(): void
    {
        $this->occurrences_count++;

        $next = $this->next_issue_date->copy();

        $next = match ($this->interval_unit) {
            self::UNIT_DAY => $next->addDays($this->interval_value),
            self::UNIT_WEEK => $next->addWeeks($this->interval_value),
            self::UNIT_MONTH => $next->addMonths($this->interval_value),
            self::UNIT_YEAR => $next->addYears($this->interval_value),
            default => $next->addMonths($this->interval_value),
        };

        $this->next_issue_date = $next;

        if ($this->shouldFinish()) {
            $this->status = self::STATUS_FINISHED;
        }

        $this->save();
    }

    public static function intervalUnits(): array
    {
        return [
            self::UNIT_DAY,
            self::UNIT_WEEK,
            self::UNIT_MONTH,
            self::UNIT_YEAR,
        ];
    }
}
