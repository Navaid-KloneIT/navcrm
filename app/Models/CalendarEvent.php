<?php

namespace App\Models;

use App\Enums\CalendarEventStatus;
use App\Enums\CalendarEventType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalendarEvent extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'event_type',
        'status',
        'starts_at',
        'ends_at',
        'is_all_day',
        'location',
        'meeting_link',
        'invite_url',
        'external_calendar_id',
        'external_calendar_source',
        'eventable_type',
        'eventable_id',
        'organizer_id',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'  => 'datetime',
            'ends_at'    => 'datetime',
            'is_all_day' => 'boolean',
            'event_type' => CalendarEventType::class,
            'status'     => CalendarEventStatus::class,
        ];
    }

    public function eventable(): MorphTo
    {
        return $this->morphTo();
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function getDurationMinutesAttribute(): int
    {
        return (int) $this->starts_at->diffInMinutes($this->ends_at);
    }
}
