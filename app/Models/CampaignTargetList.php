<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampaignTargetList extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'campaign_id',
        'name',
        'description',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'campaign_target_list_contacts')
                    ->withTimestamps();
    }

    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class, 'campaign_target_list_leads')
                    ->withTimestamps();
    }

    public function getMemberCountAttribute(): int
    {
        return $this->contacts()->count() + $this->leads()->count();
    }
}
