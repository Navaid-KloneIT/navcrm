<?php

namespace App\Models;

use App\Enums\ForecastCategory;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForecastEntry extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'opportunity_id',
        'forecast_category',
        'amount',
        'period_start',
        'period_end',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'forecast_category' => ForecastCategory::class,
            'amount' => 'decimal:2',
            'period_start' => 'date',
            'period_end' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }
}
