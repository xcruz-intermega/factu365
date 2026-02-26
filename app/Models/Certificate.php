<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use Auditable;

    public function auditExclude(): array
    {
        return ['password', 'remember_token', 'pfx_path', 'pfx_password'];
    }
    protected $fillable = [
        'name',
        'pfx_path',
        'pfx_password',
        'subject_cn',
        'serial_number',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'pfx_path' => 'encrypted',
            'pfx_password' => 'encrypted',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    public function isValid(): bool
    {
        return $this->is_active
            && ! $this->isExpired()
            && ($this->valid_from === null || $this->valid_from->isPast());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
