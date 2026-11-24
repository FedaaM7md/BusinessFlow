<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Catalog\Models;

use App\Modules\Inventory\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sku', 'name', 'name_ar', 'type', 'category_id', 'unit', 'price_minor',
        'cost_minor', 'tax_rate', 'is_tracked', 'low_stock_threshold', 'is_active',
        'notes', 'created_by',
    ];

    protected $casts = [
        'price_minor' => 'integer',
        'cost_minor' => 'integer',
        'tax_rate' => 'integer',
        'is_tracked' => 'boolean',
        'is_active' => 'boolean',
        'low_stock_threshold' => 'float',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function displayName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'ar' ? ($this->name_ar ?: $this->name) : $this->name;
    }

    public function onHand(?int $warehouseId = null): float
    {
        return (float) $this->stockMovements()
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->sum('quantity');
    }

    public function isLowStock(?int $warehouseId = null): bool
    {
        return $this->is_tracked && $this->onHand($warehouseId) <= (float) $this->low_stock_threshold;
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('is_tracked', true)
            ->withSum('stockMovements as on_hand_quantity', 'quantity')
            ->whereRaw('(SELECT COALESCE(SUM(stock_movements.quantity), 0) FROM stock_movements WHERE stock_movements.product_id = products.id) <= products.low_stock_threshold');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('name_ar', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%");
        });
    }
}
