<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use App\Enums\CampaignType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'status',
        'description',
        'start_date',
        'end_date',
        'planned_budget',
        'actual_budget',
        'target_revenue',
        'actual_revenue',
        'owner_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type'           => CampaignType::class,
            'status'         => CampaignStatus::class,
            'start_date'     => 'date',
            'end_date'       => 'date',
            'planned_budget' => 'decimal:2',
            'actual_budget'  => 'decimal:2',
            'target_revenue' => 'decimal:2',
            'actual_revenue' => 'decimal:2',
        ];
    }

    public function getRoiAttribute(): ?float
    {
        if (!$this->actual_budget || $this->actual_budget == 0) {
            return null;
        }

        return round((($this->actual_revenue - $this->actual_budget) / $this->actual_budget) * 100, 2);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function targetLists(): HasMany
    {
        return $this->hasMany(CampaignTargetList::class);
    }

    public function emailCampaigns(): HasMany
    {
        return $this->hasMany(EmailCampaign::class);
    }
}
