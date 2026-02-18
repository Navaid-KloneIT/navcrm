<?php

namespace App\Models;

use App\Enums\EmailCampaignStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailCampaign extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'campaign_id',
        'email_template_id',
        'name',
        'type',
        'status',
        'from_name',
        'from_email',
        'subject',
        'subject_a',
        'subject_b',
        'scheduled_at',
        'sent_at',
        'winning_variant',
        'total_sent',
        'total_opens',
        'total_clicks',
        'total_bounces',
        'total_unsubscribes',
        'owner_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status'       => EmailCampaignStatus::class,
            'scheduled_at' => 'datetime',
            'sent_at'      => 'datetime',
        ];
    }

    public function getOpenRateAttribute(): float
    {
        if (!$this->total_sent) return 0;
        return round(($this->total_opens / $this->total_sent) * 100, 2);
    }

    public function getClickRateAttribute(): float
    {
        if (!$this->total_sent) return 0;
        return round(($this->total_clicks / $this->total_sent) * 100, 2);
    }

    public function getBounceRateAttribute(): float
    {
        if (!$this->total_sent) return 0;
        return round(($this->total_bounces / $this->total_sent) * 100, 2);
    }

    public function getUnsubscribeRateAttribute(): float
    {
        if (!$this->total_sent) return 0;
        return round(($this->total_unsubscribes / $this->total_sent) * 100, 2);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
