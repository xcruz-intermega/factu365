<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static bool $auditDisabled = false;

    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            if (static::$auditDisabled) {
                return;
            }

            $newValues = $model->filterAuditValues($model->getAttributes());

            AuditLog::record(
                $model,
                AuditLog::ACTION_CREATED,
                null,
                $newValues,
                $model->buildAuditSummary(AuditLog::ACTION_CREATED),
            );
        });

        static::updated(function ($model) {
            if (static::$auditDisabled) {
                return;
            }

            $dirty = $model->getDirty();
            if (empty($dirty)) {
                return;
            }

            $oldValues = [];
            foreach (array_keys($dirty) as $key) {
                $oldValues[$key] = $model->getOriginal($key);
            }

            $oldValues = $model->filterAuditValues($oldValues);
            $newValues = $model->filterAuditValues($dirty);

            AuditLog::record(
                $model,
                AuditLog::ACTION_UPDATED,
                $oldValues,
                $newValues,
                $model->buildAuditSummary(AuditLog::ACTION_UPDATED),
            );
        });

        static::deleting(function ($model) {
            if (static::$auditDisabled) {
                return;
            }

            $oldValues = $model->filterAuditValues($model->getAttributes());

            AuditLog::record(
                $model,
                AuditLog::ACTION_DELETED,
                $oldValues,
                null,
                $model->buildAuditSummary(AuditLog::ACTION_DELETED),
            );
        });
    }

    public function auditExclude(): array
    {
        return ['password', 'remember_token'];
    }

    public function filterAuditValues(array $values): array
    {
        $excluded = $this->auditExclude();
        $filtered = [];

        foreach ($values as $key => $value) {
            if (in_array($key, $excluded)) {
                $filtered[$key] = '[REDACTED]';
            } else {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    public function buildAuditSummary(string $action): string
    {
        $modelName = class_basename(static::class);
        $identifier = $this->getAuditIdentifier();

        return match ($action) {
            AuditLog::ACTION_CREATED => "{$modelName} '{$identifier}' created",
            AuditLog::ACTION_UPDATED => "{$modelName} '{$identifier}' updated",
            AuditLog::ACTION_DELETED => "{$modelName} '{$identifier}' deleted",
            default => "{$modelName} '{$identifier}' {$action}",
        };
    }

    protected function getAuditIdentifier(): string
    {
        // Try common identifier fields
        foreach (['number', 'name', 'legal_name', 'concept', 'email', 'code'] as $field) {
            if (! empty($this->{$field})) {
                return (string) $this->{$field};
            }
        }

        return (string) $this->getKey();
    }
}
