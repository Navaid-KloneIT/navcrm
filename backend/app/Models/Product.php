<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use BelongsToTenant, SoftDeletes;

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
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function priceBookEntries(): HasMany
    {
        return $this->hasMany(PriceBookEntry::class);
    }

    public function quoteLineItems(): HasMany
    {
        return $this->hasMany(QuoteLineItem::class);
    }
}
