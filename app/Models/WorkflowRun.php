<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowRun extends Model
{
    protected $fillable = [
        'workflow_id',
        'tenant_id',
        'trigger_entity_type',
        'trigger_entity_id',
        'status',
        'context_data',
        'actions_log',
        'error_message',
        'triggered_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'context_data' => 'array',
            'actions_log'  => 'array',
            'triggered_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'completed' => 'success',
            'failed'    => 'danger',
            'running'   => 'info',
            default     => 'secondary',
        };
    }
}
