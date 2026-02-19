<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmailLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'subject',
        'body',
        'direction',
        'from_email',
        'to_email',
        'cc_emails',
        'status',
        'source',
        'message_id',
        'emailable_type',
        'emailable_id',
        'user_id',
        'sent_at',
        'opened_at',
    ];

    protected function casts(): array
    {
        return [
            'cc_emails'  => 'array',
            'sent_at'    => 'datetime',
            'opened_at'  => 'datetime',
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
}
