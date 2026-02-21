<?php

namespace App\Models;

use App\Enums\TicketChannel;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'ticket_number',
        'subject',
        'description',
        'status',
        'priority',
        'channel',
        'contact_id',
        'account_id',
        'assigned_to',
        'created_by',
        'sla_due_at',
        'sla_breached_at',
        'first_response_at',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'status'            => TicketStatus::class,
        'priority'          => TicketPriority::class,
        'channel'           => TicketChannel::class,
        'sla_due_at'        => 'datetime',
        'sla_breached_at'   => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at'       => 'datetime',
        'closed_at'         => 'datetime',
    ];

    public static function generateTicketNumber(): string
    {
        $last = static::withoutGlobalScopes()->latest('id')->value('ticket_number');
        $seq  = $last ? ((int) substr($last, 3)) + 1 : 1;

        return 'TK-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    public function isSlaBreach(): bool
    {
        return $this->sla_due_at
            && now()->gt($this->sla_due_at)
            && ! in_array($this->status, [TicketStatus::Resolved, TicketStatus::Closed]);
    }

    public function isSlaWarning(): bool
    {
        if (! $this->sla_due_at) {
            return false;
        }

        return ! $this->isSlaBreach()
            && $this->sla_due_at->lte(now()->addHours(2))
            && ! in_array($this->status, [TicketStatus::Resolved, TicketStatus::Closed]);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class)->orderBy('created_at');
    }

    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }
}
