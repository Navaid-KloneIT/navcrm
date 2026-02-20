<?php

namespace App\Models;

use App\Enums\WorkflowTrigger;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    use BelongsToTenant, Filterable;

    protected $fillable = [
        'tenant_id',
        'created_by',
        'name',
        'description',
        'is_active',
        'trigger_event',
        'trigger_config',
    ];

    protected function casts(): array
    {
        return [
            'is_active'      => 'boolean',
            'trigger_event'  => WorkflowTrigger::class,
            'trigger_config' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(WorkflowCondition::class)->orderBy('sort_order');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(WorkflowAction::class)->orderBy('sort_order');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(WorkflowRun::class)->orderByDesc('triggered_at');
    }
}
