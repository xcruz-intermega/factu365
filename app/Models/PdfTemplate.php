<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class PdfTemplate extends Model
{
    use Auditable;

    public function auditExclude(): array
    {
        return ['password', 'remember_token', 'layout_json'];
    }
    protected $fillable = [
        'name',
        'blade_view',
        'settings',
        'layout_json',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'layout_json' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first()
            ?? static::first();
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }

    public function hasCustomLayout(): bool
    {
        return ! empty($this->layout_json);
    }

    public function getLayout(): array
    {
        return $this->layout_json ?? self::getDefaultLayout();
    }

    public static function getDefaultLayout(): array
    {
        return [
            'blocks' => [
                ['type' => 'header', 'enabled' => true, 'options' => ['show_logo' => true, 'logo_position' => 'left', 'show_company_info' => true]],
                ['type' => 'title_bar', 'enabled' => true, 'options' => []],
                ['type' => 'client_info', 'enabled' => true, 'options' => ['layout' => 'side-by-side']],
                ['type' => 'lines_table', 'enabled' => true, 'options' => ['columns' => ['concept', 'qty', 'price', 'discount', 'vat', 'amount'], 'show_description' => true]],
                ['type' => 'vat_breakdown', 'enabled' => true, 'options' => []],
                ['type' => 'totals', 'enabled' => true, 'options' => []],
                ['type' => 'notes', 'enabled' => true, 'options' => []],
                ['type' => 'footer_text', 'enabled' => true, 'options' => []],
                ['type' => 'qr_verifactu', 'enabled' => true, 'options' => ['position' => 'left', 'size' => 80, 'show_legal_text' => true]],
                ['type' => 'legal_text', 'enabled' => true, 'options' => []],
                ['type' => 'page_footer', 'enabled' => true, 'options' => ['content' => 'company']],
            ],
            'global' => [
                'font_family' => 'DejaVu Sans',
                'font_size' => '9pt',
                'primary_color' => '#1f2937',
                'accent_color' => '#4f46e5',
            ],
        ];
    }
}
