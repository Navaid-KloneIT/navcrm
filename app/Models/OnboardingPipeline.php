<?php

namespace App\Models;

use App\Enums\OnboardingStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnboardingPipeline extends Model
{
    use BelongsToTenant, Filterable, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'account_id',
        'contact_id',
        'assigned_to',
        'created_by',
        'pipeline_number',
        'name',
        'description',
        'status',
        'due_date',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status'       => OnboardingStatus::class,
            'due_date'     => 'date',
            'started_at'   => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /* ── Relationships ──────────────────────────────────────────────── */

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(OnboardingStep::class)->orderBy('sort_order');
    }

    /* ── Computed ────────────────────────────────────────────────────── */

    public function getProgressAttribute(): int
    {
        $total = $this->steps->count();
        if ($total === 0) {
            return 0;
        }

        return (int) round(($this->steps->where('is_completed', true)->count() / $total) * 100);
    }
}
