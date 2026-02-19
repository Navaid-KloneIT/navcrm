<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CallLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'subject',
        'description',
        'direction',
        'duration_seconds',
        'status',
        'phone_number',
        'recording_url',
        'loggable_type',
        'loggable_id',
        'user_id',
        'called_at',
    ];

    protected function casts(): array
    {
        return [
            'called_at' => 'datetime',
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
}
