<?php

namespace App\Models;

use App\Enums\CallDirection;
use App\Enums\CallStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CallLog extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'direction',
        'status',
        'phone_number',
        'duration',
        'recording_url',
        'notes',
        'called_at',
        'loggable_type',
        'loggable_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'called_at' => 'datetime',
            'direction' => CallDirection::class,
            'status'    => CallStatus::class,
        ];
    }

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedDurationAttribute(): string
    {
        if (! $this->duration) {
            return '—';
        }

        $minutes = intdiv($this->duration, 60);
        $seconds = $this->duration % 60;

        return $minutes > 0
            ? "{$minutes}m {$seconds}s"
            : "{$seconds}s";
    }
}
