<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowCondition extends Model
{
    protected $fillable = [
        'workflow_id',
        'field',
        'operator',
        'value',
        'sort_order',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function operatorLabel(): string
    {
        return match($this->operator) {
            'eq'       => '=',
            'neq'      => '≠',
            'gt'       => '>',
            'lt'       => '<',
            'gte'      => '≥',
            'lte'      => '≤',
            'contains' => 'contains',
            default    => $this->operator,
        };
    }
}
