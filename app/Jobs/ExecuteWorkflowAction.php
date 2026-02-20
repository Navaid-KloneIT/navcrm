<?php

namespace App\Jobs;

use App\Models\WorkflowAction;
use App\Models\WorkflowRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class ExecuteWorkflowAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int   $actionId,
        public readonly int   $runId,
        public readonly array $context,
    ) {}

    public function handle(): void
    {
        $action = WorkflowAction::find($this->actionId);
        $run    = WorkflowRun::find($this->runId);

        if (! $action || ! $run) {
            return;
        }

        $run->update(['status' => 'running']);

        try {
            $result = match ($action->action_type) {
                'send_email'    => $this->sendEmail($action),
                'assign_user'   => $this->assignUser($action),
                'change_status' => $this->changeStatus($action),
                'send_webhook'  => $this->sendWebhook($action, $run),
                default         => ['skipped' => 'unknown action type'],
            };

            $this->appendLog($run, $action->action_type, 'success', $result);
            $run->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $this->appendLog($run, $action->action_type, 'failed', ['error' => $e->getMessage()]);
            $run->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at'  => now(),
            ]);
        }
    }

    private function sendEmail(WorkflowAction $action): array
    {
        $config  = $action->action_config;
        $to      = $config['to_email'] ?? null;
        $subject = $config['subject'] ?? 'CRM Workflow Notification';
        $message = $config['message'] ?? 'A workflow has been triggered.';

        // Substitute simple context tokens like {status}, {name} etc.
        $message = $this->substituteTokens($message);

        if (! $to) {
            // Try to resolve owner/assigned email from context
            $to = $this->resolveOwnerEmail();
        }

        if (! $to) {
            return ['skipped' => 'no recipient email configured'];
        }

        Mail::raw($message, function ($mail) use ($to, $subject) {
            $mail->to($to)->subject($subject);
        });

        return ['sent_to' => $to, 'subject' => $subject];
    }

    private function assignUser(WorkflowAction $action): array
    {
        $config = $action->action_config;
        $userId = $config['user_id'] ?? null;

        if (! $userId) {
            return ['skipped' => 'no user_id configured'];
        }

        $entityClass = $this->context['__class'] ?? null;
        $entityId    = $this->context['id'] ?? null;

        if ($entityClass && $entityId) {
            $entity = $entityClass::find($entityId);
            if ($entity) {
                $field = $entity->getTable() === 'tickets' ? 'assigned_to' : 'owner_id';
                $entity->updateQuietly([$field => $userId]);
                return ['assigned_to_user_id' => $userId, 'field' => $field];
            }
        }

        return ['skipped' => 'entity not found'];
    }

    private function changeStatus(WorkflowAction $action): array
    {
        $config    = $action->action_config;
        $newStatus = $config['status'] ?? null;

        if (! $newStatus) {
            return ['skipped' => 'no status configured'];
        }

        $entityId = $this->context['id'] ?? null;

        // Determine entity class from run
        $run         = WorkflowRun::find($this->runId);
        $entityClass = $run?->trigger_entity_type;

        if ($entityClass && $entityId) {
            $entity = $entityClass::find($entityId);
            if ($entity) {
                $entity->updateQuietly(['status' => $newStatus]);
                return ['status_changed_to' => $newStatus];
            }
        }

        return ['skipped' => 'entity not found'];
    }

    private function sendWebhook(WorkflowAction $action, WorkflowRun $run): array
    {
        $config = $action->action_config;
        $url    = $config['url'] ?? null;

        if (! $url) {
            return ['skipped' => 'no URL configured'];
        }

        $payload = [
            'event'     => $run->workflow->trigger_event ?? '',
            'entity'    => $run->trigger_entity_type,
            'entity_id' => $run->trigger_entity_id,
            'context'   => $this->context,
            'fired_at'  => $run->triggered_at?->toIso8601String(),
        ];

        $response = Http::timeout(10)->post($url, $payload);

        return [
            'url'         => $url,
            'status_code' => $response->status(),
            'success'     => $response->successful(),
        ];
    }

    private function substituteTokens(string $text): string
    {
        foreach ($this->context as $key => $value) {
            if (is_scalar($value)) {
                $text = str_replace('{' . $key . '}', (string) $value, $text);
            }
        }
        return $text;
    }

    private function resolveOwnerEmail(): ?string
    {
        $userId = $this->context['owner_id']
            ?? $this->context['assigned_to']
            ?? $this->context['created_by']
            ?? null;

        if ($userId) {
            return \App\Models\User::find($userId)?->email;
        }

        return null;
    }

    private function appendLog(WorkflowRun $run, string $actionType, string $status, array $data): void
    {
        $log   = $run->actions_log ?? [];
        $log[] = [
            'action_type' => $actionType,
            'status'      => $status,
            'data'        => $data,
            'at'          => now()->toIso8601String(),
        ];
        $run->update(['actions_log' => $log]);
    }
}
