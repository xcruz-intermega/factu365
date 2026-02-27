<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Casts\Attribute;

class MailConfiguration extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
        'is_active',
        'tested_at',
    ];

    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'is_active' => 'boolean',
            'tested_at' => 'datetime',
        ];
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? decrypt($value) : null,
            set: fn (?string $value) => $value ? encrypt($value) : null,
        );
    }

    public static function current(): ?static
    {
        return Cache::remember('mail_config', 3600, function () {
            return static::first();
        });
    }

    public static function clearCache(): void
    {
        Cache::forget('mail_config');
    }

    public function isConfigured(): bool
    {
        return !empty($this->host) && !empty($this->port) && !empty($this->from_address);
    }
}
