<?php

namespace Database\Seeders;

use App\Enums\QuoteStatus;
use App\Models\Quote;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowAction;
use App\Models\WorkflowCondition;
use App\Models\WorkflowRun;
use Illuminate\Database\Seeder;

class WorkflowDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        if (! $tenant) {
            return;
        }

        $user  = User::where('tenant_id', $tenant->id)->first();
        $user2 = User::where('tenant_id', $tenant->id)->skip(1)->first();

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
                    'to_email' => null,
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
                    'to_email' => null,
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

        // ----------------------------------------------------------------
        // Workflow 4: Quote Discount Approval
        // ----------------------------------------------------------------
        $wf4 = Workflow::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Quote Discount Approval Required'],
            [
                'created_by'    => $user->id,
                'description'   => 'When a quote discount exceeds 15%, email the manager for approval.',
                'is_active'     => true,
                'trigger_event' => 'quote_discount_exceeded',
                'trigger_config'=> ['discount_threshold' => 15],
            ]
        );

        if ($wf4->wasRecentlyCreated) {
            WorkflowCondition::create([
                'workflow_id' => $wf4->id,
                'field'       => 'discount_value',
                'operator'    => 'gt',
                'value'       => '15',
                'sort_order'  => 0,
            ]);

            WorkflowAction::create([
                'workflow_id'   => $wf4->id,
                'action_type'   => 'send_email',
                'action_config' => [
                    'to_email' => $user->email,
                    'subject'  => 'Quote Approval Required: {quote_number}',
                    'message'  => "Quote {quote_number} has a discount of {discount_value}% which exceeds the 15% threshold.\nPlease review and approve or reject at the Approvals page.",
                ],
                'sort_order' => 0,
            ]);

            WorkflowAction::create([
                'workflow_id'   => $wf4->id,
                'action_type'   => 'change_status',
                'action_config' => [
                    'status' => 'pending_approval',
                ],
                'sort_order' => 1,
            ]);
        }

        // ----------------------------------------------------------------
        // Workflow 5: Qualified Lead Auto-Assign
        // ----------------------------------------------------------------
        $wf5 = Workflow::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Qualified Lead Auto-Assign'],
            [
                'created_by'    => $user->id,
                'description'   => 'When a lead reaches "qualified" status, automatically assign to a senior sales rep.',
                'is_active'     => true,
                'trigger_event' => 'lead_status_changed',
                'trigger_config'=> null,
            ]
        );

        if ($wf5->wasRecentlyCreated) {
            WorkflowCondition::create([
                'workflow_id' => $wf5->id,
                'field'       => 'status',
                'operator'    => 'eq',
                'value'       => 'qualified',
                'sort_order'  => 0,
            ]);

            WorkflowAction::create([
                'workflow_id'   => $wf5->id,
                'action_type'   => 'assign_user',
                'action_config' => [
                    'user_id' => $user2?->id ?? $user->id,
                ],
                'sort_order' => 0,
            ]);

            WorkflowAction::create([
                'workflow_id'   => $wf5->id,
                'action_type'   => 'send_email',
                'action_config' => [
                    'to_email' => null,
                    'subject'  => 'New Qualified Lead Assigned to You',
                    'message'  => 'A newly qualified lead has been assigned to you. Lead: {first_name} {last_name}, Company: {company}.',
                ],
                'sort_order' => 1,
            ]);
        }

        // ----------------------------------------------------------------
        // Workflow 6: Closed-Won Celebration Webhook (inactive)
        // ----------------------------------------------------------------
        $wf6 = Workflow::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Closed-Won Slack Notification'],
            [
                'created_by'    => $user->id,
                'description'   => 'Posts to Slack when an opportunity is marked as closed-won. Currently disabled.',
                'is_active'     => false,
                'trigger_event' => 'opportunity_stage_changed',
                'trigger_config'=> null,
            ]
        );

        if ($wf6->wasRecentlyCreated) {
            WorkflowCondition::create([
                'workflow_id' => $wf6->id,
                'field'       => 'is_won',
                'operator'    => 'eq',
                'value'       => '1',
                'sort_order'  => 0,
            ]);

            WorkflowAction::create([
                'workflow_id'   => $wf6->id,
                'action_type'   => 'send_webhook',
                'action_config' => [
                    'url' => 'https://hooks.slack.com/services/EXAMPLE/PLACEHOLDER',
                ],
                'sort_order' => 0,
            ]);
        }

        // ----------------------------------------------------------------
        // Sample Workflow Runs (historical audit data)
        // ----------------------------------------------------------------
        $this->seedWorkflowRuns($wf1, $wf2, $wf3, $tenant);

        // ----------------------------------------------------------------
        // Sample Quote Pending Approval
        // ----------------------------------------------------------------
        $this->seedPendingApprovalQuote($tenant, $user);

        $this->command->info('WorkflowDemoSeeder: 6 workflows, sample runs, and pending approval quote created.');
    }

    private function seedWorkflowRuns(Workflow $wf1, Workflow $wf2, Workflow $wf3, Tenant $tenant): void
    {
        // Only seed if no runs exist yet
        if (WorkflowRun::where('tenant_id', $tenant->id)->exists()) {
            return;
        }

        // Run 1: Lead workflow completed successfully (2 days ago)
        WorkflowRun::create([
            'workflow_id'         => $wf1->id,
            'tenant_id'           => $tenant->id,
            'trigger_entity_type' => 'App\Models\Lead',
            'trigger_entity_id'   => 1,
            'status'              => 'completed',
            'context_data'        => [
                'id'         => 1,
                'first_name' => 'John',
                'last_name'  => 'Smith',
                'status'     => 'contacted',
                'company'    => 'Acme Corp',
            ],
            'actions_log' => [
                [
                    'action_type' => 'send_email',
                    'status'      => 'success',
                    'data'        => ['sent_to' => 'admin@example.com', 'subject' => 'Lead Status Updated'],
                    'at'          => now()->subDays(2)->toIso8601String(),
                ],
            ],
            'triggered_at' => now()->subDays(2),
            'completed_at' => now()->subDays(2)->addSeconds(3),
        ]);

        // Run 2: Lead workflow completed (yesterday)
        WorkflowRun::create([
            'workflow_id'         => $wf1->id,
            'tenant_id'           => $tenant->id,
            'trigger_entity_type' => 'App\Models\Lead',
            'trigger_entity_id'   => 2,
            'status'              => 'completed',
            'context_data'        => [
                'id'         => 2,
                'first_name' => 'Sarah',
                'last_name'  => 'Connor',
                'status'     => 'qualified',
                'company'    => 'Cyberdyne',
            ],
            'actions_log' => [
                [
                    'action_type' => 'send_email',
                    'status'      => 'success',
                    'data'        => ['sent_to' => 'admin@example.com', 'subject' => 'Lead Status Updated'],
                    'at'          => now()->subDay()->toIso8601String(),
                ],
            ],
            'triggered_at' => now()->subDay(),
            'completed_at' => now()->subDay()->addSeconds(2),
        ]);

        // Run 3: SLA breach notification completed (today, 3 hours ago)
        WorkflowRun::create([
            'workflow_id'         => $wf2->id,
            'tenant_id'           => $tenant->id,
            'trigger_entity_type' => 'App\Models\Ticket',
            'trigger_entity_id'   => 1,
            'status'              => 'completed',
            'context_data'        => [
                'id'            => 1,
                'ticket_number' => 'TK-00001',
                'subject'       => 'Login issues on mobile app',
                'priority'      => 'high',
                'status'        => 'open',
            ],
            'actions_log' => [
                [
                    'action_type' => 'send_email',
                    'status'      => 'success',
                    'data'        => ['sent_to' => 'admin@example.com', 'subject' => 'SLA Breach Alert: Ticket #TK-00001'],
                    'at'          => now()->subHours(3)->toIso8601String(),
                ],
            ],
            'triggered_at' => now()->subHours(3),
            'completed_at' => now()->subHours(3)->addSeconds(4),
        ]);

        // Run 4: SLA breach notification (today, 1 hour ago)
        WorkflowRun::create([
            'workflow_id'         => $wf2->id,
            'tenant_id'           => $tenant->id,
            'trigger_entity_type' => 'App\Models\Ticket',
            'trigger_entity_id'   => 3,
            'status'              => 'completed',
            'context_data'        => [
                'id'            => 3,
                'ticket_number' => 'TK-00003',
                'subject'       => 'Payment gateway timeout',
                'priority'      => 'critical',
                'status'        => 'in_progress',
            ],
            'actions_log' => [
                [
                    'action_type' => 'send_email',
                    'status'      => 'success',
                    'data'        => ['sent_to' => 'admin@example.com', 'subject' => 'SLA Breach Alert: Ticket #TK-00003'],
                    'at'          => now()->subHour()->toIso8601String(),
                ],
            ],
            'triggered_at' => now()->subHour(),
            'completed_at' => now()->subHour()->addSeconds(2),
        ]);

        // Run 5: Webhook failed (3 days ago — simulating a timeout)
        WorkflowRun::create([
            'workflow_id'         => $wf3->id,
            'tenant_id'           => $tenant->id,
            'trigger_entity_type' => 'App\Models\Opportunity',
            'trigger_entity_id'   => 1,
            'status'              => 'failed',
            'context_data'        => [
                'id'     => 1,
                'name'   => 'Enterprise Platform Deal',
                'amount' => 75000,
            ],
            'actions_log' => [
                [
                    'action_type' => 'send_webhook',
                    'status'      => 'failed',
                    'data'        => ['error' => 'cURL error 28: Connection timed out after 10001 milliseconds'],
                    'at'          => now()->subDays(3)->toIso8601String(),
                ],
            ],
            'error_message' => 'cURL error 28: Connection timed out after 10001 milliseconds',
            'triggered_at'  => now()->subDays(3),
            'completed_at'  => now()->subDays(3)->addSeconds(11),
        ]);

        // Run 6: Webhook success (yesterday)
        WorkflowRun::create([
            'workflow_id'         => $wf3->id,
            'tenant_id'           => $tenant->id,
            'trigger_entity_type' => 'App\Models\Opportunity',
            'trigger_entity_id'   => 2,
            'status'              => 'completed',
            'context_data'        => [
                'id'     => 2,
                'name'   => 'Cloud Migration Project',
                'amount' => 120000,
            ],
            'actions_log' => [
                [
                    'action_type' => 'send_webhook',
                    'status'      => 'success',
                    'data'        => ['url' => 'https://webhook.site/placeholder-demo-url', 'status_code' => 200, 'success' => true],
                    'at'          => now()->subDay()->toIso8601String(),
                ],
            ],
            'triggered_at' => now()->subDay(),
            'completed_at' => now()->subDay()->addSeconds(1),
        ]);

        // Run 7: Lead workflow — today
        WorkflowRun::create([
            'workflow_id'         => $wf1->id,
            'tenant_id'           => $tenant->id,
            'trigger_entity_type' => 'App\Models\Lead',
            'trigger_entity_id'   => 4,
            'status'              => 'completed',
            'context_data'        => [
                'id'         => 4,
                'first_name' => 'David',
                'last_name'  => 'Chen',
                'status'     => 'new',
                'company'    => 'Globex Corp',
            ],
            'actions_log' => [
                [
                    'action_type' => 'send_email',
                    'status'      => 'success',
                    'data'        => ['sent_to' => 'admin@example.com', 'subject' => 'Lead Status Updated'],
                    'at'          => now()->subMinutes(30)->toIso8601String(),
                ],
            ],
            'triggered_at' => now()->subMinutes(30),
            'completed_at' => now()->subMinutes(30)->addSeconds(2),
        ]);
    }

    private function seedPendingApprovalQuote(Tenant $tenant, User $user): void
    {
        // Only create if no pending_approval quotes exist
        if (Quote::where('tenant_id', $tenant->id)->where('status', QuoteStatus::PendingApproval)->exists()) {
            return;
        }

        // Find an existing draft/sent quote with a discount to convert, or create a new one
        $quote = Quote::where('tenant_id', $tenant->id)
            ->whereIn('status', [QuoteStatus::Draft, QuoteStatus::Sent])
            ->where('discount_value', '>', 0)
            ->first();

        if ($quote) {
            $quote->updateQuietly([
                'status'            => QuoteStatus::PendingApproval,
                'approval_required' => true,
                'discount_value'    => max($quote->discount_value, 20.00),
            ]);
        } else {
            $account = \App\Models\Account::where('tenant_id', $tenant->id)->first();

            Quote::create([
                'tenant_id'         => $tenant->id,
                'quote_number'      => 'QT-99001',
                'account_id'        => $account?->id,
                'status'            => QuoteStatus::PendingApproval,
                'valid_until'       => now()->addDays(30),
                'subtotal'          => 25000.00,
                'discount_type'     => 'percentage',
                'discount_value'    => 22.00,
                'discount_amount'   => 5500.00,
                'tax_rate'          => 10.00,
                'tax_amount'        => 1950.00,
                'total'             => 21450.00,
                'notes'             => 'Large enterprise deal — discount requires manager approval.',
                'terms'             => 'Net 30',
                'prepared_by'       => $user->id,
                'approval_required' => true,
            ]);
        }
    }
}
