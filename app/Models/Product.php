<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use BelongsToTenant, Filterable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'description',
        'unit_price',
        'cost_price',
        'currency',
        'unit',
        'is_active',
        'category',
        'stock_quantity',
        'reorder_level',
    ];

    protected function casts(): array
    {
        return [
            'unit_price'     => 'decimal:2',
            'cost_price'     => 'decimal:2',
            'is_active'      => 'boolean',
            'stock_quantity' => 'integer',
            'reorder_level'  => 'integer',
        ];
    }

    /* ── Relationships ──────────────────────────────────────────────── */

    public function priceBookEntries(): HasMany
    {
        return $this->hasMany(PriceBookEntry::class);
    }

    public function quoteLineItems(): HasMany
    {
        return $this->hasMany(QuoteLineItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /* ── Computed ────────────────────────────────────────────────────── */

    public function getIsLowStockAttribute(): bool
    {
        return $this->reorder_level > 0 && $this->stock_quantity <= $this->reorder_level;
    }
}
