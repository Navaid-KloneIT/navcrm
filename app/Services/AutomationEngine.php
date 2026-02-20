<?php

namespace App\Services;

use App\Jobs\ExecuteWorkflowAction;
use App\Models\Workflow;
use App\Models\WorkflowRun;
use Illuminate\Database\Eloquent\Model;

class AutomationEngine
{
    public static function fire(string $event, Model $entity): void
    {
        $tenantId = $entity->tenant_id ?? null;

        if (! $tenantId) {
            return;
        }

        $workflows = Workflow::where('tenant_id', $tenantId)
            ->where('trigger_event', $event)
            ->where('is_active', true)
            ->with(['conditions', 'actions'])
            ->get();

        $context = $entity->toArray();

        foreach ($workflows as $workflow) {
            if (! self::evaluateConditions($workflow, $context)) {
                continue;
            }

            $run = WorkflowRun::create([
                'workflow_id'         => $workflow->id,
                'tenant_id'           => $tenantId,
                'trigger_entity_type' => get_class($entity),
                'trigger_entity_id'   => $entity->id,
                'status'              => 'pending',
                'context_data'        => $context,
                'triggered_at'        => now(),
            ]);

            foreach ($workflow->actions->sortBy('sort_order') as $action) {
                ExecuteWorkflowAction::dispatch($action->id, $run->id, $context);
            }
        }
    }

    private static function evaluateConditions(Workflow $workflow, array $context): bool
    {
        foreach ($workflow->conditions as $condition) {
            // Support dot-notation like "status" or flat keys
            $key        = str_replace('.', '_', $condition->field);
            $fieldValue = data_get($context, $key) ?? data_get($context, $condition->field);

            $result = match ($condition->operator) {
                'eq'       => $fieldValue == $condition->value,
                'neq'      => $fieldValue != $condition->value,
                'gt'       => $fieldValue >  $condition->value,
                'lt'       => $fieldValue <  $condition->value,
                'gte'      => $fieldValue >= $condition->value,
                'lte'      => $fieldValue <= $condition->value,
                'contains' => str_contains((string) $fieldValue, $condition->value),
                default    => true,
            };

            if (! $result) {
                return false; // ALL conditions must pass (AND logic)
            }
        }

        return true;
    }
}
