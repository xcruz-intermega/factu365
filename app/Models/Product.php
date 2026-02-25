<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'product_family_id',
        'reference',
        'name',
        'description',
        'unit_price',
        'vat_rate',
        'exemption_code',
        'irpf_applicable',
        'unit',
        'track_stock',
        'stock_quantity',
        'minimum_stock',
        'allow_negative_stock',
        'stock_mode',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'irpf_applicable' => 'boolean',
            'track_stock' => 'boolean',
            'stock_quantity' => 'decimal:4',
            'minimum_stock' => 'decimal:4',
            'allow_negative_stock' => 'boolean',
        ];
    }

    public function scopeSearch($query, ?string $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('reference', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(ProductFamily::class, 'product_family_id');
    }

    public function components(): HasMany
    {
        return $this->hasMany(ProductComponent::class, 'parent_product_id');
    }

    public function usedIn(): HasMany
    {
        return $this->hasMany(ProductComponent::class, 'component_product_id');
    }

    public function isComposite(): bool
    {
        return $this->components()->exists();
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function tracksStock(): bool
    {
        return $this->type === 'product' && $this->track_stock;
    }

    public function usesComponentStock(): bool
    {
        return $this->stock_mode === 'components' && $this->isComposite();
    }

    public function isExempt(): bool
    {
        return $this->exemption_code !== null;
    }
}
