<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingStep extends Model
{
    protected $fillable = [
        'onboarding_pipeline_id',
        'title',
        'description',
        'sort_order',
        'is_completed',
        'completed_at',
        'completed_by',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
            'due_date'     => 'date',
            'sort_order'   => 'integer',
        ];
    }

    /* ── Relationships ──────────────────────────────────────────────── */

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(OnboardingPipeline::class, 'onboarding_pipeline_id');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
