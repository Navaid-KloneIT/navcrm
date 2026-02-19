<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    /**
     * Scope: OR LIKE search across multiple columns.
     */
    public function scopeSearch(Builder $query, ?string $term, array $columns): void
    {
        if (! $term) {
            return;
        }

        $query->where(function (Builder $q) use ($term, $columns) {
            foreach ($columns as $col) {
                $q->orWhere($col, 'like', "%{$term}%");
            }
        });
    }

    /**
     * Scope: filter by owner_id.
     */
    public function scopeFilterOwner(Builder $query, mixed $ownerId): void
    {
        if ($ownerId) {
            $query->where('owner_id', $ownerId);
        }
    }

    /**
     * Scope: filter by a date range on the given column (defaults to created_at).
     */
    public function scopeFilterDateRange(
        Builder $query,
        ?string $from,
        ?string $to,
        string  $col = 'created_at'
    ): void {
        if ($from) {
            $query->whereDate($col, '>=', $from);
        }
        if ($to) {
            $query->whereDate($col, '<=', $to);
        }
    }
}
