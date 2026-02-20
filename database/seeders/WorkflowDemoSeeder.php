<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowAction;
use App\Models\WorkflowCondition;
use Illuminate\Database\Seeder;

class WorkflowDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        if (! $tenant) {
            return;
        }

        $user = User::where('tenant_id', $tenant->id)->first();

        if (! $user) {
            return;
        }

        // ----------------------------------------------------------------
        // Workflow 1: New Lead Welcome Email
        // ----------------------------------------------------------------
        $wf1 = Workflow::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'New Lead Welcome Email'],
            [
                'created_by'    => $user->id,
                'description'   => 'Sends a welcome email to the lead owner when a lead status is updated.',
                'is_active'     => true,
                'trigger_event' => 'lead_status_changed',
                'trigger_config'=> null,
            ]
        );

        if ($wf1->wasRecentlyCreated) {
            WorkflowAction::create([
                'workflow_id'   => $wf1->id,
                'action_type'   => 'send_email',
                'action_config' => [
                    'to_email' => null, // uses owner email
                    'subject'  => 'Lead Status Updated',
                    'message'  => 'A lead you own has been updated. Status is now: {status}.',
                ],
                'sort_order' => 0,
            ]);
        }

        // ----------------------------------------------------------------
        // Workflow 2: SLA Breach Notify Manager
        // ----------------------------------------------------------------
        $wf2 = Workflow::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'SLA Breach Notify Manager'],
            [
                'created_by'    => $user->id,
                'description'   => 'Emails the assigned agent when a support ticket breaches its SLA deadline.',
                'is_active'     => true,
                'trigger_event' => 'ticket_sla_breached',
                'trigger_config'=> null,
            ]
        );

        if ($wf2->wasRecentlyCreated) {
            WorkflowAction::create([
                'workflow_id'   => $wf2->id,
                'action_type'   => 'send_email',
                'action_config' => [
                    'to_email' => null, // uses owner email
                    'subject'  => 'SLA Breach Alert: Ticket #{ticket_number}',
                    'message'  => "Ticket {ticket_number} has breached its SLA deadline.\nSubject: {subject}\nPlease take immediate action.",
                ],
                'sort_order' => 0,
            ]);
        }

        // ----------------------------------------------------------------
        // Workflow 3: Large Deal Webhook
        // ----------------------------------------------------------------
        $wf3 = Workflow::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Large Deal Stage Change Webhook'],
            [
                'created_by'    => $user->id,
                'description'   => 'Fires a webhook when a high-value opportunity advances to a new stage.',
                'is_active'     => true,
                'trigger_event' => 'opportunity_stage_changed',
                'trigger_config'=> null,
            ]
        );

        if ($wf3->wasRecentlyCreated) {
            WorkflowCondition::create([
                'workflow_id' => $wf3->id,
                'field'       => 'amount',
                'operator'    => 'gt',
                'value'       => '50000',
                'sort_order'  => 0,
            ]);

            WorkflowAction::create([
                'workflow_id'   => $wf3->id,
                'action_type'   => 'send_webhook',
                'action_config' => [
                    'url' => 'https://webhook.site/placeholder-demo-url',
                ],
                'sort_order' => 0,
            ]);
        }

        $this->command->info('WorkflowDemoSeeder: 3 demo workflows created.');
    }
}
