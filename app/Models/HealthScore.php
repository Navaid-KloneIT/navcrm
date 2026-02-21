<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthScore extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'account_id',
        'overall_score',
        'login_score',
        'ticket_score',
        'payment_score',
        'factors',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'overall_score'  => 'integer',
            'login_score'    => 'integer',
            'ticket_score'   => 'integer',
            'payment_score'  => 'integer',
            'factors'        => 'array',
            'calculated_at'  => 'datetime',
        ];
    }

    /* ── Relationships ──────────────────────────────────────────────── */

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /* ── Computed ────────────────────────────────────────────────────── */

    public function getHealthLabelAttribute(): string
    {
        return match (true) {
            $this->overall_score >= 70 => 'Healthy',
            $this->overall_score >= 40 => 'At Risk',
            default                    => 'Critical',
        };
    }

    public function getHealthColorAttribute(): string
    {
        return match (true) {
            $this->overall_score >= 70 => 'success',
            $this->overall_score >= 40 => 'warning',
            default                    => 'danger',
        };
    }
}
