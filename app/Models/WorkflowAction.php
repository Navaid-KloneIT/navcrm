<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowAction extends Model
{
    protected $fillable = [
        'workflow_id',
        'action_type',
        'action_config',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'action_config' => 'array',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function actionTypeLabel(): string
    {
        return match($this->action_type) {
            'send_email'    => 'Send Email',
            'assign_user'   => 'Assign User',
            'change_status' => 'Change Status',
            'send_webhook'  => 'Send Webhook',
            default         => ucfirst(str_replace('_', ' ', $this->action_type)),
        };
    }
}
