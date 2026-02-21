<?php

namespace App\Models;

use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Survey extends Model
{
    use BelongsToTenant, Filterable, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'survey_number',
        'type',
        'name',
        'description',
        'status',
        'account_id',
        'ticket_id',
        'created_by',
        'token',
    ];

    protected function casts(): array
    {
        return [
            'type'   => SurveyType::class,
            'status' => SurveyStatus::class,
        ];
    }

    /* ── Relationships ──────────────────────────────────────────────── */

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /* ── Computed ────────────────────────────────────────────────────── */

    public function getAverageScoreAttribute(): ?float
    {
        $avg = $this->responses()->avg('score');

        return $avg !== null ? round($avg, 1) : null;
    }

    public function getResponseCountAttribute(): int
    {
        return $this->responses()->count();
    }

    /**
     * NPS = (% Promoters - % Detractors) × 100
     * Promoters: 9-10, Passives: 7-8, Detractors: 1-6
     */
    public function getNpsScoreAttribute(): ?int
    {
        $total = $this->responses()->count();
        if ($total === 0) {
            return null;
        }

        $promoters  = $this->responses()->where('score', '>=', 9)->count();
        $detractors = $this->responses()->where('score', '<=', 6)->count();

        return (int) round((($promoters - $detractors) / $total) * 100);
    }
}
