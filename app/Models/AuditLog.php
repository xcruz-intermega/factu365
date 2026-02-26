<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    // Actions
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_FINALIZED = 'finalized';
    public const ACTION_SENT_TO_AEAT = 'sent_to_aeat';
    public const ACTION_MARKED_PAID = 'marked_paid';
    public const ACTION_STATUS_CHANGED = 'status_changed';
    public const ACTION_CONVERTED = 'converted';
    public const ACTION_RESTORED = 'restored';

    protected $fillable = [
        'subject_type',
        'subject_id',
        'action',
        'old_values',
        'new_values',
        'user_id',
        'user_name',
        'user_email',
        'ip_address',
        'user_agent',
        'summary',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    // --- Immutability ---

    public function update(array $attributes = [], array $options = []): bool
    {
        throw new \RuntimeException('Audit logs are immutable and cannot be updated.');
    }

    public function delete(): ?bool
    {
        throw new \RuntimeException('Audit logs are immutable and cannot be deleted.');
    }

    // --- Relationships ---

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // --- Scopes ---

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('summary', 'like', "%{$term}%")
                ->orWhere('user_name', 'like', "%{$term}%")
                ->orWhere('user_email', 'like', "%{$term}%");
        });
    }

    public function scopeForAction(Builder $query, ?string $action): Builder
    {
        return $action ? $query->where('action', $action) : $query;
    }

    public function scopeForSubjectType(Builder $query, ?string $type): Builder
    {
        return $type ? $query->where('subject_type', $type) : $query;
    }

    public function scopeForUser(Builder $query, ?int $userId): Builder
    {
        return $userId ? $query->where('user_id', $userId) : $query;
    }

    public function scopeDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->where('created_at', '>=', $from . ' 00:00:00');
        }
        if ($to) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        return $query;
    }

    // --- Static factory ---

    public static function record(Model $subject, string $action, ?array $oldValues = null, ?array $newValues = null, ?string $summary = null): static
    {
        $user = Auth::user();

        $instance = new static;
        $instance->subject_type = get_class($subject);
        $instance->subject_id = $subject->getKey();
        $instance->action = $action;
        $instance->old_values = $oldValues;
        $instance->new_values = $newValues;
        $instance->user_id = $user?->id;
        $instance->user_name = $user?->name;
        $instance->user_email = $user?->email;
        $instance->ip_address = Request::ip();
        $instance->user_agent = Request::userAgent();
        $instance->summary = $summary;

        // Use the base Model::save() to bypass our immutability override
        $instance->saveQuietly();

        return $instance;
    }

    // --- Helpers ---

    public function shortModelName(): string
    {
        return class_basename($this->subject_type);
    }

    public static function modelTypeLabel(string $className): string
    {
        $map = [
            'App\\Models\\Document' => 'audit.model_document',
            'App\\Models\\DocumentLine' => 'audit.model_document_line',
            'App\\Models\\DocumentSeries' => 'audit.model_document_series',
            'App\\Models\\DocumentDueDate' => 'audit.model_document_due_date',
            'App\\Models\\Expense' => 'audit.model_expense',
            'App\\Models\\ExpenseCategory' => 'audit.model_expense_category',
            'App\\Models\\InvoicingRecord' => 'audit.model_invoicing_record',
            'App\\Models\\AeatSubmission' => 'audit.model_aeat_submission',
            'App\\Models\\Client' => 'audit.model_client',
            'App\\Models\\Product' => 'audit.model_product',
            'App\\Models\\ProductFamily' => 'audit.model_product_family',
            'App\\Models\\ProductComponent' => 'audit.model_product_component',
            'App\\Models\\ClientDiscount' => 'audit.model_client_discount',
            'App\\Models\\CompanyProfile' => 'audit.model_company_profile',
            'App\\Models\\Certificate' => 'audit.model_certificate',
            'App\\Models\\PdfTemplate' => 'audit.model_pdf_template',
            'App\\Models\\PaymentTemplate' => 'audit.model_payment_template',
            'App\\Models\\PaymentTemplateLine' => 'audit.model_payment_template_line',
            'App\\Models\\StockMovement' => 'audit.model_stock_movement',
            'App\\Models\\User' => 'audit.model_user',
        ];

        return $map[$className] ?? class_basename($className);
    }

    public static function actionLabel(string $action): string
    {
        return 'audit.action_' . $action;
    }

    public static function allActions(): array
    {
        return [
            self::ACTION_CREATED,
            self::ACTION_UPDATED,
            self::ACTION_DELETED,
            self::ACTION_FINALIZED,
            self::ACTION_SENT_TO_AEAT,
            self::ACTION_MARKED_PAID,
            self::ACTION_STATUS_CHANGED,
            self::ACTION_CONVERTED,
            self::ACTION_RESTORED,
        ];
    }

    public static function allSubjectTypes(): array
    {
        return [
            'App\\Models\\Document',
            'App\\Models\\DocumentLine',
            'App\\Models\\DocumentSeries',
            'App\\Models\\DocumentDueDate',
            'App\\Models\\Expense',
            'App\\Models\\ExpenseCategory',
            'App\\Models\\InvoicingRecord',
            'App\\Models\\AeatSubmission',
            'App\\Models\\Client',
            'App\\Models\\Product',
            'App\\Models\\ProductFamily',
            'App\\Models\\ProductComponent',
            'App\\Models\\ClientDiscount',
            'App\\Models\\CompanyProfile',
            'App\\Models\\Certificate',
            'App\\Models\\PdfTemplate',
            'App\\Models\\PaymentTemplate',
            'App\\Models\\PaymentTemplateLine',
            'App\\Models\\StockMovement',
            'App\\Models\\User',
        ];
    }
}
