<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use BelongsToTenant, Filterable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'opportunity_id',
        'account_id',
        'contact_id',
        'manager_id',
        'created_by',
        'project_number',
        'name',
        'description',
        'status',
        'start_date',
        'due_date',
        'budget',
        'currency',
        'is_from_opportunity',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status'             => ProjectStatus::class,
            'start_date'         => 'date',
            'due_date'           => 'date',
            'budget'             => 'decimal:2',
            'is_from_opportunity'=> 'boolean',
            'completed_at'       => 'datetime',
        ];
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class)->orderBy('sort_order')->orderBy('due_date');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot(['role', 'allocated_hours'])
            ->withTimestamps();
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class);
    }

    public function getMilestoneProgressAttribute(): int
    {
        $total     = $this->milestones()->count();
        $completed = $this->milestones()->where('status', 'completed')->count();

        return $total > 0 ? (int) round($completed / $total * 100) : 0;
    }
}
