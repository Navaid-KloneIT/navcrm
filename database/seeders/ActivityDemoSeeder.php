<?php

namespace Database\Seeders;

use App\Enums\CalendarEventStatus;
use App\Enums\CalendarEventType;
use App\Enums\CallDirection;
use App\Enums\CallStatus;
use App\Enums\EmailDirection;
use App\Enums\EmailSource;
use App\Enums\TaskPriority;
use App\Enums\TaskRecurrence;
use App\Enums\TaskStatus;
use App\Models\Account;
use App\Models\CalendarEvent;
use App\Models\CallLog;
use App\Models\Contact;
use App\Models\EmailLog;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityDemoSeeder extends Seeder
{
    public function run(): void
    {
        // BelongsToTenant scope is inactive in seeder context — direct where() queries required
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $users = User::where('tenant_id', $tenant->id)->get();

            if ($users->isEmpty()) {
                $this->command->warn("No users found for tenant [{$tenant->name}], skipping.");
                continue;
            }

            $contacts      = Contact::where('tenant_id', $tenant->id)->get();
            $accounts      = Account::where('tenant_id', $tenant->id)->get();
            $leads         = Lead::where('tenant_id', $tenant->id)->get();
            $opportunities = Opportunity::where('tenant_id', $tenant->id)->get();

            $this->command->info("Seeding Activity data for tenant: {$tenant->name}");

            $this->createTasks($tenant, $users, $contacts, $accounts, $opportunities);
            $this->createCalendarEvents($tenant, $users, $contacts, $accounts, $opportunities);
            $this->createCallLogs($tenant, $users, $contacts, $leads, $accounts);
            $this->createEmailLogs($tenant, $users, $contacts, $leads, $accounts);

            $this->command->line("  ✓ Tasks, Calendar Events, Call Logs and Email Logs created.");
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Tasks
    // ─────────────────────────────────────────────────────────────────────────

    private function createTasks(
        object $tenant,
        $users,
        $contacts,
        $accounts,
        $opportunities
    ): void {
        $tasksData = [
            // Pending tasks
            [
                'title'        => 'Send follow-up proposal to decision maker',
                'description'  => 'Prepare and send the updated proposal document with revised pricing. Include ROI calculations and implementation timeline.',
                'due_date'     => now()->addDays(2),
                'due_time'     => '10:00',
                'priority'     => TaskPriority::High,
                'status'       => TaskStatus::Pending,
                'is_recurring' => false,
                'relate_to'    => 'contact',
            ],
            [
                'title'        => 'Schedule product demo for new prospect',
                'description'  => 'Coordinate with the prospect to find a suitable time slot. Prepare demo environment and custom use-case scenarios.',
                'due_date'     => now()->addDays(3),
                'due_time'     => '14:00',
                'priority'     => TaskPriority::Medium,
                'status'       => TaskStatus::Pending,
                'is_recurring' => false,
                'relate_to'    => 'opportunity',
            ],
            [
                'title'        => 'Review contract terms with legal team',
                'description'  => 'Cross-check the latest MSA draft from the client. Flag any non-standard clauses for legal review.',
                'due_date'     => now()->addDays(5),
                'priority'     => TaskPriority::Urgent,
                'status'       => TaskStatus::Pending,
                'is_recurring' => false,
                'relate_to'    => 'account',
            ],
            [
                'title'        => 'Update CRM contact records from trade show',
                'description'  => 'Import business cards scanned at the event and link to relevant accounts. Assign follow-up tasks.',
                'due_date'     => now()->addDays(1),
                'priority'     => TaskPriority::Medium,
                'status'       => TaskStatus::Pending,
                'is_recurring' => false,
                'relate_to'    => 'contact',
            ],
            [
                'title'        => 'Prepare Q1 sales pipeline report',
                'description'  => 'Compile pipeline data from all reps. Include open opportunities, weighted values, and forecast vs. target comparison.',
                'due_date'     => now()->addDays(7),
                'priority'     => TaskPriority::High,
                'status'       => TaskStatus::Pending,
                'is_recurring' => false,
                'relate_to'    => null,
            ],

            // In Progress tasks
            [
                'title'        => 'Negotiate renewal terms with enterprise client',
                'description'  => 'Client has requested a 15% discount on the annual renewal. Prepare counter-offer with value-add options.',
                'due_date'     => now()->addDays(4),
                'priority'     => TaskPriority::Urgent,
                'status'       => TaskStatus::InProgress,
                'is_recurring' => false,
                'relate_to'    => 'account',
            ],
            [
                'title'        => 'Research competitor pricing for mid-market segment',
                'description'  => 'Compile competitor feature comparison and pricing matrix. Use for internal training and prospect objection handling.',
                'due_date'     => now()->addDays(6),
                'priority'     => TaskPriority::Medium,
                'status'       => TaskStatus::InProgress,
                'is_recurring' => false,
                'relate_to'    => null,
            ],
            [
                'title'        => 'Onboard new sales representative',
                'description'  => 'Complete CRM training, product walk-through, and introduce to key accounts in territory.',
                'due_date'     => now()->addDays(10),
                'priority'     => TaskPriority::High,
                'status'       => TaskStatus::InProgress,
                'is_recurring' => false,
                'relate_to'    => null,
            ],

            // Recurring tasks
            [
                'title'               => 'Weekly pipeline review with sales team',
                'description'         => 'Review all open opportunities, update stages, and identify any blockers. Assign action items for the week.',
                'due_date'            => now()->next('Monday'),
                'due_time'            => '09:00',
                'priority'            => TaskPriority::High,
                'status'              => TaskStatus::Pending,
                'is_recurring'        => true,
                'recurrence_type'     => TaskRecurrence::Weekly,
                'recurrence_interval' => 1,
                'recurrence_ends_at'  => now()->addMonths(6),
                'relate_to'           => null,
            ],
            [
                'title'               => 'Monthly customer check-in calls',
                'description'         => 'Call top 10 accounts to review satisfaction, gather feedback, and identify upsell opportunities.',
                'due_date'            => now()->startOfMonth()->addMonth(),
                'priority'            => TaskPriority::Medium,
                'status'              => TaskStatus::Pending,
                'is_recurring'        => true,
                'recurrence_type'     => TaskRecurrence::Monthly,
                'recurrence_interval' => 1,
                'recurrence_ends_at'  => now()->addYear(),
                'relate_to'           => null,
            ],
            [
                'title'               => 'Send monthly newsletter to prospect list',
                'description'         => 'Coordinate with marketing to draft, review and send the monthly email newsletter to all subscribed prospects.',
                'due_date'            => now()->startOfMonth()->addMonth()->addDays(2),
                'priority'            => TaskPriority::Low,
                'status'              => TaskStatus::Pending,
                'is_recurring'        => true,
                'recurrence_type'     => TaskRecurrence::Monthly,
                'recurrence_interval' => 1,
                'relate_to'           => null,
            ],

            // Completed tasks
            [
                'title'        => 'Send welcome email to new account',
                'description'  => 'Draft personalized welcome email introducing the account team and next steps for onboarding.',
                'due_date'     => now()->subDays(5),
                'priority'     => TaskPriority::Medium,
                'status'       => TaskStatus::Completed,
                'is_recurring' => false,
                'completed_at' => now()->subDays(5)->setTime(11, 30),
                'relate_to'    => 'contact',
            ],
            [
                'title'        => 'Complete annual account review presentation',
                'description'  => 'Prepare slides covering usage metrics, ROI delivered, expansion opportunities, and roadmap highlights.',
                'due_date'     => now()->subDays(3),
                'priority'     => TaskPriority::High,
                'status'       => TaskStatus::Completed,
                'is_recurring' => false,
                'completed_at' => now()->subDays(3)->setTime(16, 0),
                'relate_to'    => 'account',
            ],
            [
                'title'        => 'Update pricing sheet for new product tier',
                'description'  => 'Revise the internal pricing guide and customer-facing one-pager to include the new Enterprise Plus tier.',
                'due_date'     => now()->subDays(7),
                'priority'     => TaskPriority::Medium,
                'status'       => TaskStatus::Completed,
                'is_recurring' => false,
                'completed_at' => now()->subDays(6)->setTime(14, 15),
                'relate_to'    => null,
            ],

            // Cancelled task
            [
                'title'        => 'Attend regional partner summit',
                'description'  => 'Register and prepare materials for the regional partner event. Travel and accommodation required.',
                'due_date'     => now()->subDays(2),
                'priority'     => TaskPriority::Low,
                'status'       => TaskStatus::Cancelled,
                'is_recurring' => false,
                'relate_to'    => null,
            ],
        ];

        foreach ($tasksData as $data) {
            $assignee  = $users->random();
            $creator   = $users->random();
            $relatesTo = $data['relate_to'] ?? null;

            $taskableType = null;
            $taskableId   = null;

            if ($relatesTo === 'contact' && $contacts->isNotEmpty()) {
                $taskableType = 'App\Models\Contact';
                $taskableId   = $contacts->random()->id;
            } elseif ($relatesTo === 'account' && $accounts->isNotEmpty()) {
                $taskableType = 'App\Models\Account';
                $taskableId   = $accounts->random()->id;
            } elseif ($relatesTo === 'opportunity' && $opportunities->isNotEmpty()) {
                $taskableType = 'App\Models\Opportunity';
                $taskableId   = $opportunities->random()->id;
            }

            Task::create([
                'tenant_id'           => $tenant->id,
                'title'               => $data['title'],
                'description'         => $data['description'] ?? null,
                'due_date'            => $data['due_date'] ?? null,
                'due_time'            => $data['due_time'] ?? null,
                'priority'            => $data['priority']->value,
                'status'              => $data['status']->value,
                'is_recurring'        => $data['is_recurring'],
                'recurrence_type'     => isset($data['recurrence_type']) ? $data['recurrence_type']->value : null,
                'recurrence_interval' => $data['recurrence_interval'] ?? null,
                'recurrence_ends_at'  => $data['recurrence_ends_at'] ?? null,
                'taskable_type'       => $taskableType,
                'taskable_id'         => $taskableId,
                'assigned_to'         => $assignee->id,
                'created_by'          => $creator->id,
                'completed_at'        => $data['completed_at'] ?? null,
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Calendar Events
    // ─────────────────────────────────────────────────────────────────────────

    private function createCalendarEvents(
        object $tenant,
        $users,
        $contacts,
        $accounts,
        $opportunities
    ): void {
        $eventsData = [
            // Upcoming scheduled events
            [
                'title'        => 'Q2 Business Review — Acme Corp',
                'description'  => 'Quarterly review covering KPIs, usage trends, expansion roadmap, and strategic alignment for Q2.',
                'event_type'   => CalendarEventType::Meeting,
                'status'       => CalendarEventStatus::Scheduled,
                'starts_at'    => now()->addDays(3)->setTime(10, 0),
                'ends_at'      => now()->addDays(3)->setTime(11, 30),
                'location'     => 'Conference Room A, 3rd Floor',
                'relate_to'    => 'account',
            ],
            [
                'title'        => 'Product Demo — Enterprise Prospect',
                'description'  => 'Live product demonstration covering core CRM modules, reporting dashboards, and API integrations.',
                'event_type'   => CalendarEventType::Demo,
                'status'       => CalendarEventStatus::Scheduled,
                'starts_at'    => now()->addDays(5)->setTime(14, 0),
                'ends_at'      => now()->addDays(5)->setTime(15, 0),
                'meeting_link' => 'https://meet.google.com/abc-demo-link',
                'invite_url'   => 'https://calendly.com/navcrm/demo',
                'relate_to'    => 'opportunity',
            ],
            [
                'title'        => 'Discovery Call — New Lead',
                'description'  => 'Initial discovery call to understand pain points, current tools, team size, and decision-making process.',
                'event_type'   => CalendarEventType::Call,
                'status'       => CalendarEventStatus::Scheduled,
                'starts_at'    => now()->addDays(1)->setTime(9, 30),
                'ends_at'      => now()->addDays(1)->setTime(10, 0),
                'meeting_link' => 'https://zoom.us/j/discovery-call',
                'relate_to'    => 'contact',
            ],
            [
                'title'        => 'Sales Team Weekly Stand-up',
                'description'  => 'Weekly team sync covering pipeline updates, blockers, wins of the week, and priorities for next 7 days.',
                'event_type'   => CalendarEventType::Meeting,
                'status'       => CalendarEventStatus::Scheduled,
                'starts_at'    => now()->next('Monday')->setTime(9, 0),
                'ends_at'      => now()->next('Monday')->setTime(9, 30),
                'location'     => 'Main Boardroom',
                'relate_to'    => null,
            ],
            [
                'title'        => 'Contract Negotiation — GlobalTech',
                'description'  => 'Final round of contract negotiations. Review redlines from legal and discuss payment terms and SLA commitments.',
                'event_type'   => CalendarEventType::Meeting,
                'status'       => CalendarEventStatus::Scheduled,
                'starts_at'    => now()->addDays(7)->setTime(11, 0),
                'ends_at'      => now()->addDays(7)->setTime(12, 30),
                'location'     => 'Client HQ, 10th Floor Boardroom',
                'relate_to'    => 'account',
            ],
            [
                'title'        => 'Follow-Up: Proposal Review',
                'description'  => 'Follow-up call to address any questions about the proposal sent last week and gauge likelihood of closing.',
                'event_type'   => CalendarEventType::FollowUp,
                'status'       => CalendarEventStatus::Scheduled,
                'starts_at'    => now()->addDays(2)->setTime(16, 0),
                'ends_at'      => now()->addDays(2)->setTime(16, 30),
                'meeting_link' => 'https://teams.microsoft.com/l/meetup-join/followup',
                'relate_to'    => 'opportunity',
            ],
            [
                'title'        => 'Webinar: CRM Best Practices for SMBs',
                'description'  => 'Customer-facing webinar on best practices for SMB teams. Topics: lead management, pipeline hygiene, and reporting.',
                'event_type'   => CalendarEventType::Webinar,
                'status'       => CalendarEventStatus::Scheduled,
                'starts_at'    => now()->addDays(14)->setTime(13, 0),
                'ends_at'      => now()->addDays(14)->setTime(14, 0),
                'meeting_link' => 'https://zoom.us/j/webinar-crm-smb',
                'invite_url'   => 'https://calendly.com/navcrm/webinar',
                'relate_to'    => null,
            ],

            // Past completed events
            [
                'title'        => 'Onboarding Kick-off — New Client',
                'description'  => 'Project kick-off meeting to align on implementation plan, data migration approach, and go-live timeline.',
                'event_type'   => CalendarEventType::Meeting,
                'status'       => CalendarEventStatus::Completed,
                'starts_at'    => now()->subDays(5)->setTime(10, 0),
                'ends_at'      => now()->subDays(5)->setTime(11, 0),
                'location'     => 'Zoom',
                'relate_to'    => 'account',
            ],
            [
                'title'        => 'Quarterly Business Review — Enterprise Account',
                'description'  => 'QBR covering usage metrics, ROI analysis, outstanding support tickets, and roadmap alignment.',
                'event_type'   => CalendarEventType::Meeting,
                'status'       => CalendarEventStatus::Completed,
                'starts_at'    => now()->subDays(10)->setTime(14, 0),
                'ends_at'      => now()->subDays(10)->setTime(15, 30),
                'location'     => 'Client Offices',
                'relate_to'    => 'account',
            ],
            [
                'title'        => 'Demo — Mid-Market Prospect',
                'description'  => 'Tailored demo for the finance and operations team. Focused on integrations with ERP and BI tools.',
                'event_type'   => CalendarEventType::Demo,
                'status'       => CalendarEventStatus::Completed,
                'starts_at'    => now()->subDays(4)->setTime(15, 0),
                'ends_at'      => now()->subDays(4)->setTime(16, 0),
                'meeting_link' => 'https://meet.google.com/demo-completed',
                'relate_to'    => 'contact',
            ],

            // No-show / Cancelled
            [
                'title'        => 'Intro Call — Inbound Lead',
                'description'  => 'Initial call with inbound lead from website contact form.',
                'event_type'   => CalendarEventType::Call,
                'status'       => CalendarEventStatus::NoShow,
                'starts_at'    => now()->subDays(2)->setTime(11, 0),
                'ends_at'      => now()->subDays(2)->setTime(11, 30),
                'relate_to'    => 'contact',
            ],
            [
                'title'        => 'Executive Briefing — Cancelled',
                'description'  => 'Executive briefing requested by VP Sales. Cancelled due to scheduling conflict.',
                'event_type'   => CalendarEventType::Meeting,
                'status'       => CalendarEventStatus::Cancelled,
                'starts_at'    => now()->subDays(1)->setTime(9, 0),
                'ends_at'      => now()->subDays(1)->setTime(10, 0),
                'location'     => 'HQ Executive Suite',
                'relate_to'    => null,
            ],
        ];

        foreach ($eventsData as $data) {
            $organizer = $users->random();
            $relatesTo = $data['relate_to'] ?? null;

            $eventableType = null;
            $eventableId   = null;

            if ($relatesTo === 'contact' && $contacts->isNotEmpty()) {
                $eventableType = 'App\Models\Contact';
                $eventableId   = $contacts->random()->id;
            } elseif ($relatesTo === 'account' && $accounts->isNotEmpty()) {
                $eventableType = 'App\Models\Account';
                $eventableId   = $accounts->random()->id;
            } elseif ($relatesTo === 'opportunity' && $opportunities->isNotEmpty()) {
                $eventableType = 'App\Models\Opportunity';
                $eventableId   = $opportunities->random()->id;
            }

            CalendarEvent::create([
                'tenant_id'                => $tenant->id,
                'title'                    => $data['title'],
                'description'              => $data['description'] ?? null,
                'event_type'               => $data['event_type']->value,
                'status'                   => $data['status']->value,
                'starts_at'                => $data['starts_at'],
                'ends_at'                  => $data['ends_at'],
                'is_all_day'               => false,
                'location'                 => $data['location'] ?? null,
                'meeting_link'             => $data['meeting_link'] ?? null,
                'invite_url'               => $data['invite_url'] ?? null,
                'external_calendar_id'     => null,
                'external_calendar_source' => null,
                'eventable_type'           => $eventableType,
                'eventable_id'             => $eventableId,
                'organizer_id'             => $organizer->id,
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Call Logs
    // ─────────────────────────────────────────────────────────────────────────

    private function createCallLogs(
        object $tenant,
        $users,
        $contacts,
        $leads,
        $accounts
    ): void {
        $callsData = [
            [
                'direction'    => CallDirection::Outbound,
                'status'       => CallStatus::Completed,
                'phone_number' => '+1 555 234 5678',
                'duration'     => 742,
                'notes'        => 'Discussed renewal timeline. Client happy with product but concerned about pricing increase. Agreed to send revised pricing options by Friday. Decision expected end of month.',
                'called_at'    => now()->subHours(3),
                'relate_to'    => 'contact',
            ],
            [
                'direction'    => CallDirection::Outbound,
                'status'       => CallStatus::Completed,
                'phone_number' => '+1 555 876 5432',
                'duration'     => 1245,
                'notes'        => 'Full product walkthrough for the VP of Sales and two directors. Very engaged, lots of questions about API capabilities and Salesforce migration. Requested a formal proposal by next Wednesday.',
                'called_at'    => now()->subDays(1)->setTime(10, 30),
                'relate_to'    => 'contact',
            ],
            [
                'direction'    => CallDirection::Outbound,
                'status'       => CallStatus::Completed,
                'phone_number' => '+1 555 111 9900',
                'duration'     => 384,
                'notes'        => 'Quick check-in after onboarding session. Client team is now active on the platform. Minor questions about report configuration — directed to knowledge base article on custom reports.',
                'called_at'    => now()->subDays(2)->setTime(14, 0),
                'relate_to'    => 'contact',
            ],
            [
                'direction'    => CallDirection::Outbound,
                'status'       => CallStatus::Completed,
                'phone_number' => '+1 555 444 7788',
                'duration'     => 198,
                'notes'        => 'Left a voicemail — they didn\'t pick up initially. Called back, spoke with the assistant who confirmed contact is traveling. Rescheduled follow-up to next Tuesday at 10am.',
                'called_at'    => now()->subDays(3)->setTime(9, 15),
                'relate_to'    => 'account',
            ],
            [
                'direction'    => CallDirection::Outbound,
                'status'       => CallStatus::Completed,
                'phone_number' => '+1 555 333 2211',
                'duration'     => 2156,
                'notes'        => 'Long negotiation call on contract terms. Customer pushed back on the 3-year lock-in. Agreed to come back with a revised 1+1+1 structure. Escalated to manager for approval.',
                'called_at'    => now()->subDays(4)->setTime(15, 30),
                'relate_to'    => 'account',
            ],
            [
                'direction'    => CallDirection::Inbound,
                'status'       => CallStatus::Completed,
                'phone_number' => '+1 555 987 6543',
                'duration'     => 527,
                'notes'        => 'Client called to report an issue with their CSV import. Walked them through correct column mapping format. Issue resolved on the call. Will follow up with a written guide via email.',
                'called_at'    => now()->subDays(1)->setTime(11, 0),
                'relate_to'    => 'contact',
            ],
            [
                'direction'    => CallDirection::Inbound,
                'status'       => CallStatus::Completed,
                'phone_number' => '+1 555 654 3210',
                'duration'     => 893,
                'notes'        => 'Inbound from a warm referral lead. Currently using a competitor but frustrated with lack of support. Very interested in switching. Scheduled a formal demo for next Thursday at 2pm.',
                'called_at'    => now()->subDays(2)->setTime(16, 45),
                'relate_to'    => 'lead',
            ],
            [
                'direction'    => CallDirection::Inbound,
                'status'       => CallStatus::Completed,
                'phone_number' => '+1 555 222 4455',
                'duration'     => 315,
                'notes'        => 'Client called requesting references from a similar company in their industry. Connected them with two reference accounts. Positive interaction — they seem close to signing.',
                'called_at'    => now()->subHours(5),
                'relate_to'    => 'contact',
            ],
            [
                'direction'    => CallDirection::Outbound,
                'status'       => CallStatus::NoAnswer,
                'phone_number' => '+1 555 555 0101',
                'duration'     => null,
                'notes'        => 'No answer. Will try again tomorrow morning.',
                'called_at'    => now()->subHours(2),
                'relate_to'    => 'lead',
            ],
            [
                'direction'    => CallDirection::Outbound,
                'status'       => CallStatus::Voicemail,
                'phone_number' => '+1 555 700 8899',
                'duration'     => 45,
                'notes'        => 'Left voicemail: introduced myself, referenced the demo request from the website, and asked them to call back or reply to my email.',
                'called_at'    => now()->subDays(1)->setTime(8, 45),
                'relate_to'    => 'lead',
            ],
            [
                'direction'    => CallDirection::Outbound,
                'status'       => CallStatus::Busy,
                'phone_number' => '+1 555 300 1122',
                'duration'     => null,
                'notes'        => 'Line was busy. Sent a quick text and will try again in the afternoon.',
                'called_at'    => now()->subDays(1)->setTime(9, 0),
                'relate_to'    => 'contact',
            ],
            [
                'direction'    => CallDirection::Outbound,
                'status'       => CallStatus::Completed,
                'phone_number' => '+1 555 888 2233',
                'duration'     => 614,
                'notes'        => 'Annual check-in call. Customer very happy. Mentioned they\'re adding a new department — potential expansion opportunity. Asked to connect with their IT director.',
                'called_at'    => now()->subDays(7)->setTime(10, 0),
                'relate_to'    => 'account',
            ],
            [
                'direction'    => CallDirection::Inbound,
                'status'       => CallStatus::Completed,
                'phone_number' => '+1 555 411 7733',
                'duration'     => 1089,
                'notes'        => 'Support escalation: billing discrepancy on last invoice. Identified issue (proration miscalculation) and issued a credit note. Sent confirmation email. Customer satisfied with resolution.',
                'called_at'    => now()->subDays(6)->setTime(13, 30),
                'relate_to'    => 'account',
            ],
        ];

        foreach ($callsData as $data) {
            $user      = $users->random();
            $relatesTo = $data['relate_to'] ?? null;

            $loggableType = null;
            $loggableId   = null;

            if ($relatesTo === 'contact' && $contacts->isNotEmpty()) {
                $loggableType = 'App\Models\Contact';
                $loggableId   = $contacts->random()->id;
            } elseif ($relatesTo === 'lead' && $leads->isNotEmpty()) {
                $loggableType = 'App\Models\Lead';
                $loggableId   = $leads->random()->id;
            } elseif ($relatesTo === 'account' && $accounts->isNotEmpty()) {
                $loggableType = 'App\Models\Account';
                $loggableId   = $accounts->random()->id;
            }

            CallLog::create([
                'tenant_id'     => $tenant->id,
                'direction'     => $data['direction']->value,
                'status'        => $data['status']->value,
                'phone_number'  => $data['phone_number'] ?? null,
                'duration'      => $data['duration'] ?? null,
                'recording_url' => null,
                'notes'         => $data['notes'] ?? null,
                'called_at'     => $data['called_at'],
                'loggable_type' => $loggableType,
                'loggable_id'   => $loggableId,
                'user_id'       => $user->id,
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Email Logs
    // ─────────────────────────────────────────────────────────────────────────

    private function createEmailLogs(
        object $tenant,
        $users,
        $contacts,
        $leads,
        $accounts
    ): void {
        $slug = \Illuminate\Support\Str::slug($tenant->name);

        $emailsData = [
            // Outbound — opened + clicked
            [
                'direction'  => EmailDirection::Outbound,
                'source'     => EmailSource::Manual,
                'subject'    => 'Proposal: NavCRM Enterprise License — Q2 2026',
                'body'       => "Hi Sarah,\n\nThank you for your time on our call last Wednesday. Please find attached our proposal for the NavCRM Enterprise License for your team of 45 users.\n\nKey highlights:\n- Unlimited contacts and accounts\n- Advanced reporting and forecasting\n- Priority support with dedicated CSM\n- Custom onboarding and training\n\nThe proposal is valid until 31 March 2026. Looking forward to partnering with you!\n\nBest regards,\nJames",
                'from_email' => "sales@{$slug}.com",
                'to_email'   => 'sarah.johnson@prospect.com',
                'sent_at'    => now()->subDays(3)->setTime(9, 0),
                'opened_at'  => now()->subDays(3)->setTime(9, 47),
                'clicked_at' => now()->subDays(3)->setTime(9, 48),
                'relate_to'  => 'contact',
            ],
            [
                'direction'  => EmailDirection::Outbound,
                'source'     => EmailSource::Outlook,
                'subject'    => 'Follow-Up: Product Demo — Questions & Next Steps',
                'body'       => "Hi Marcus,\n\nThank you for attending our product demo yesterday. As promised, I'm sharing the demo recording, feature comparison sheet, and sample implementation timeline.\n\nBased on our conversation, I've tailored the proposal to reflect your team's needs around the Salesforce migration and API integrations.\n\nI'll follow up by Friday. Feel free to reach out with any questions!\n\nBest,\nAmanda",
                'from_email' => "amanda@{$slug}.com",
                'to_email'   => 'marcus.lee@techbridge.com',
                'sent_at'    => now()->subDays(2)->setTime(8, 0),
                'opened_at'  => now()->subDays(2)->setTime(8, 35),
                'clicked_at' => now()->subDays(2)->setTime(8, 36),
                'relate_to'  => 'contact',
            ],
            [
                'direction'  => EmailDirection::Outbound,
                'source'     => EmailSource::BccDropbox,
                'subject'    => 'Welcome to NavCRM — Your Onboarding Resources',
                'body'       => "Hi Jennifer,\n\nWelcome to NavCRM! We're thrilled to have Summit Financial on board.\n\nTo help you get started:\n📖 Getting Started Guide\n🎥 Video Walkthrough (15 min)\n🗓 Schedule your onboarding session\n\nYour dedicated Customer Success Manager is Lisa Park (lisa.park@navcrm.com).\n\nExcited to support your team's success!\n\nBest,\nThe NavCRM Team",
                'from_email' => "onboarding@{$slug}.com",
                'to_email'   => 'jennifer.watts@summitfinancial.com',
                'sent_at'    => now()->subDays(7)->setTime(9, 0),
                'opened_at'  => now()->subDays(7)->setTime(9, 12),
                'clicked_at' => now()->subDays(7)->setTime(9, 14),
                'relate_to'  => 'account',
            ],
            [
                'direction'  => EmailDirection::Outbound,
                'source'     => EmailSource::Manual,
                'subject'    => 'Contract Signed — Next Steps for Your NavCRM Implementation',
                'body'       => "Hi Robert,\n\nCongratulations — the contract is countersigned and we're officially kicking off your NavCRM implementation!\n\nHere's what happens next:\n1. You'll receive a setup confirmation email within 24 hours\n2. Your CSM will schedule the kick-off call\n3. Data migration template will be sent by EOD Friday\n\nWe're thrilled to partner with Pinnacle Group!\n\nWarm regards,\nThe NavCRM Team",
                'from_email' => "onboarding@{$slug}.com",
                'to_email'   => 'robert.harris@pinnaclegroup.com',
                'sent_at'    => now()->subDays(9)->setTime(11, 0),
                'opened_at'  => now()->subDays(9)->setTime(11, 8),
                'clicked_at' => now()->subDays(9)->setTime(11, 10),
                'relate_to'  => 'account',
            ],

            // Outbound — opened, not clicked
            [
                'direction'  => EmailDirection::Outbound,
                'source'     => EmailSource::Gmail,
                'subject'    => 'Your NavCRM Renewal Reminder — Action Required',
                'body'       => "Hi David,\n\nYour NavCRM subscription is set to renew on 15 April 2026. I wanted to reach out early to discuss your renewal options.\n\nYour current plan: Professional (20 users)\nRenewal amount: $18,000/year\n\nWould you like to schedule a quick call to review your plan?\n\nBest,\nMichelle",
                'from_email' => "michelle@{$slug}.com",
                'to_email'   => 'david.chen@client.com',
                'sent_at'    => now()->subDays(5)->setTime(10, 15),
                'opened_at'  => now()->subDays(5)->setTime(14, 22),
                'clicked_at' => null,
                'relate_to'  => 'contact',
            ],

            // Outbound — not opened (cold outreach)
            [
                'direction'  => EmailDirection::Outbound,
                'source'     => EmailSource::Outlook,
                'subject'    => 'Quick Question About Your Current CRM Setup',
                'body'       => "Hi Carlos,\n\nI came across your profile while researching fast-growing tech companies in the Southeast. Congrats on the Series B!\n\nNavCRM helps sales teams like yours close deals 30% faster with pipeline automation and AI-powered forecasting.\n\nWould you be open to a 20-minute call to explore if there's a fit?\n\nBest,\nJames",
                'from_email' => "james@{$slug}.com",
                'to_email'   => 'carlos.mendez@fastgrowth.io',
                'sent_at'    => now()->subDays(6)->setTime(8, 30),
                'opened_at'  => null,
                'clicked_at' => null,
                'relate_to'  => 'lead',
            ],
            [
                'direction'  => EmailDirection::Outbound,
                'source'     => EmailSource::Gmail,
                'subject'    => 'NavCRM — Helping Your Team Scale Faster',
                'body'       => "Hi Nina,\n\nHope you're having a great week. I noticed your company recently expanded to three new markets — impressive growth!\n\nNavCRM has helped similar companies centralize customer data, automate follow-ups, and give leadership real-time pipeline visibility.\n\nAre you the right person to speak with about CRM strategy?\n\nThanks,\nAmanda",
                'from_email' => "amanda@{$slug}.com",
                'to_email'   => 'nina.brooks@expandco.com',
                'sent_at'    => now()->subDays(8)->setTime(9, 0),
                'opened_at'  => null,
                'clicked_at' => null,
                'relate_to'  => 'lead',
            ],
            [
                'direction'  => EmailDirection::Outbound,
                'source'     => EmailSource::Manual,
                'subject'    => 'Checking In — Have You Had a Chance to Review Our Proposal?',
                'body'       => "Hi Priya,\n\nI wanted to check in on the proposal I sent over last Tuesday. I know Q1 is always busy — just wanted to make sure it reached you and see if you have any questions.\n\nHappy to jump on a 15-minute call this week to walk you through the key points.\n\nBest,\nRobert",
                'from_email' => "robert@{$slug}.com",
                'to_email'   => 'priya.sharma@company.com',
                'sent_at'    => now()->subDays(1)->setTime(11, 0),
                'opened_at'  => null,
                'clicked_at' => null,
                'relate_to'  => 'lead',
            ],

            // Inbound emails
            [
                'direction'  => EmailDirection::Inbound,
                'source'     => EmailSource::Manual,
                'subject'    => 'Re: Proposal: NavCRM Enterprise License — Q2 2026',
                'body'       => "Hi James,\n\nThank you for the detailed proposal. I've shared it with our IT director and CFO for review.\n\nA couple of questions:\n1. Is there flexibility on the payment terms? We prefer quarterly billing.\n2. Can the onboarding be completed within 30 days?\n\nI'll have a decision for you by end of next week.\n\nThanks,\nSarah",
                'from_email' => 'sarah.johnson@prospect.com',
                'to_email'   => "sales@{$slug}.com",
                'sent_at'    => now()->subDays(2)->setTime(10, 30),
                'opened_at'  => now()->subDays(2)->setTime(10, 31),
                'clicked_at' => null,
                'relate_to'  => 'contact',
            ],
            [
                'direction'  => EmailDirection::Inbound,
                'source'     => EmailSource::Gmail,
                'subject'    => 'Urgent: Invoice Discrepancy on March Statement',
                'body'       => "Hello,\n\nWe noticed an overcharge on our March invoice. We were billed for 25 users but our contract specifies 20 users.\n\nInvoice number: INV-2026-0342\nBilled amount: \$2,500\nExpected amount: \$2,000\n\nPlease let us know your timeline for resolution.\n\nRegards,\nMark Thompson\nController, Apex Industries",
                'from_email' => 'mark.thompson@apexindustries.com',
                'to_email'   => "billing@{$slug}.com",
                'sent_at'    => now()->subDays(1)->setTime(8, 0),
                'opened_at'  => now()->subDays(1)->setTime(8, 45),
                'clicked_at' => null,
                'relate_to'  => 'account',
            ],
            [
                'direction'  => EmailDirection::Inbound,
                'source'     => EmailSource::BccDropbox,
                'subject'    => 'Interested in NavCRM — Saw Your Webinar',
                'body'       => "Hi,\n\nI attended your CRM Best Practices webinar last week and was very impressed. We're a 50-person SaaS company currently using spreadsheets to manage our pipeline and need a proper CRM.\n\nWould you be able to give us a demo next week? We're particularly interested in pipeline management and forecasting features.\n\nBest,\nLucia Fernandez\nHead of Sales, Brightline SaaS",
                'from_email' => 'lucia.fernandez@brightline.io',
                'to_email'   => "sales@{$slug}.com",
                'sent_at'    => now()->subDays(3)->setTime(16, 0),
                'opened_at'  => now()->subDays(3)->setTime(16, 5),
                'clicked_at' => null,
                'relate_to'  => 'lead',
            ],
            [
                'direction'  => EmailDirection::Inbound,
                'source'     => EmailSource::Outlook,
                'subject'    => 'Reference Request for NavCRM Evaluation',
                'body'       => "Hello,\n\nWe're evaluating NavCRM vs two other solutions. Could you connect us with 2-3 reference customers in the financial services sector?\n\nWe're specifically interested in speaking with teams that migrated from Salesforce.\n\nThank you,\nKevin Park\nVP Operations, Meridian Capital",
                'from_email' => 'kevin.park@meridiancapital.com',
                'to_email'   => "sales@{$slug}.com",
                'sent_at'    => now()->subHours(4),
                'opened_at'  => now()->subHours(4)->addMinutes(2),
                'clicked_at' => null,
                'relate_to'  => 'lead',
            ],
        ];

        foreach ($emailsData as $data) {
            $user      = $users->random();
            $relatesTo = $data['relate_to'] ?? null;

            $emailableType = null;
            $emailableId   = null;

            if ($relatesTo === 'contact' && $contacts->isNotEmpty()) {
                $emailableType = 'App\Models\Contact';
                $emailableId   = $contacts->random()->id;
            } elseif ($relatesTo === 'lead' && $leads->isNotEmpty()) {
                $emailableType = 'App\Models\Lead';
                $emailableId   = $leads->random()->id;
            } elseif ($relatesTo === 'account' && $accounts->isNotEmpty()) {
                $emailableType = 'App\Models\Account';
                $emailableId   = $accounts->random()->id;
            }

            EmailLog::create([
                'tenant_id'      => $tenant->id,
                'direction'      => $data['direction']->value,
                'source'         => $data['source']->value,
                'subject'        => $data['subject'],
                'body'           => $data['body'],
                'from_email'     => $data['from_email'] ?? null,
                'to_email'       => $data['to_email'] ?? null,
                'cc'             => null,
                'message_id'     => null,
                'sent_at'        => $data['sent_at'] ?? null,
                'opened_at'      => $data['opened_at'] ?? null,
                'clicked_at'     => $data['clicked_at'] ?? null,
                'emailable_type' => $emailableType,
                'emailable_id'   => $emailableId,
                'user_id'        => $user->id,
            ]);
        }
    }
}
