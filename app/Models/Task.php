<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskRecurrence;
use App\Enums\TaskStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'due_date',
        'due_time',
        'priority',
        'status',
        'is_recurring',
        'recurrence_type',
        'recurrence_interval',
        'recurrence_ends_at',
        'taskable_type',
        'taskable_id',
        'assigned_to',
        'created_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date'           => 'date',
            'recurrence_ends_at' => 'date',
            'completed_at'       => 'datetime',
            'is_recurring'       => 'boolean',
            'priority'           => TaskPriority::class,
            'status'             => TaskStatus::class,
            'recurrence_type'    => TaskRecurrence::class,
        ];
    }

    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && ! in_array($this->status, [TaskStatus::Completed, TaskStatus::Cancelled]);
    }

    public function isDueToday(): bool
    {
        return $this->due_date && $this->due_date->isToday();
    }
}
