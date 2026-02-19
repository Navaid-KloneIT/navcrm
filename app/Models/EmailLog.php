<?php

namespace App\Models;

use App\Enums\EmailDirection;
use App\Enums\EmailSource;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmailLog extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'direction',
        'source',
        'subject',
        'body',
        'from_email',
        'to_email',
        'cc',
        'message_id',
        'sent_at',
        'opened_at',
        'clicked_at',
        'emailable_type',
        'emailable_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'cc'         => 'array',
            'sent_at'    => 'datetime',
            'opened_at'  => 'datetime',
            'clicked_at' => 'datetime',
            'direction'  => EmailDirection::class,
            'source'     => EmailSource::class,
        ];
    }

    public function emailable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOpened(): bool
    {
        return $this->opened_at !== null;
    }

    public function isClicked(): bool
    {
        return $this->clicked_at !== null;
    }
}
