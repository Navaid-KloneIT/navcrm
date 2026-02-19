<?php

namespace Database\Seeders;

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
use Illuminate\Support\Str;

class ActivityDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch all tenants — BelongsToTenant scope is inactive in seeder context
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $users = User::where('tenant_id', $tenant->id)->get();

            if ($users->isEmpty()) {
                $this->command->warn("No users found for tenant [{$tenant->name}], skipping.");
                continue;
            }

            $admin        = $users->firstWhere('is_active', true) ?? $users->first();
            $contacts     = Contact::where('tenant_id', $tenant->id)->get();
            $accounts     = Account::where('tenant_id', $tenant->id)->get();
            $leads        = Lead::where('tenant_id', $tenant->id)->get();
            $opportunities = Opportunity::where('tenant_id', $tenant->id)->get();

            $this->command->info("Seeding Activity & Communication data for tenant: {$tenant->name}");

            $this->createTasks($tenant, $admin, $users, $contacts, $accounts, $opportunities);
            $this->createCalendarEvents($tenant, $admin, $users, $contacts, $accounts, $opportunities);
            $this->createCallLogs($tenant, $admin, $users, $contacts, $leads, $accounts);
            $this->createEmailLogs($tenant, $admin, $users, $contacts, $leads, $accounts);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Task Management
    // ─────────────────────────────────────────────────────────────────────────

    private function createTasks(
        object $tenant,
        object $admin,
        object $users,
        object $contacts,
        object $accounts,
        object $opportunities
    ): void {
        $taskTitles = [
            'Follow up with client after demo',
            'Send proposal document',
            'Review and sign contract',
            'Schedule onboarding call',
            'Update contact information in CRM',
            'Prepare quarterly review presentation',
            'Research competitor pricing',
            'Send invoice reminder',
            'Book meeting with decision maker',
            'Complete customer satisfaction survey',
            'Verify billing details',
            'Review open support tickets',
            'Prepare case study from recent win',
            'Check in on renewal status',
            'Send product update announcement',
            'Coordinate implementation kickoff',
            'Collect feedback after training session',
            'Escalate unresponsive lead to manager',
            'Update opportunity stage in pipeline',
            'Draft welcome email for new account',
        ];

        $recurringTitles = [
            'Monthly check-in call',
            'Quarterly business review',
            'Weekly pipeline sync',
            'Follow up every 3 months',
            'Monthly account health review',
        ];

        $priorities     = ['low', 'medium', 'high', 'urgent'];
        $statuses       = ['pending', 'in_progress', 'completed', 'cancelled'];
        $recurrenceTypes = ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'];

        $morphTargets = $this->buildMorphTargets($contacts, $accounts, $opportunities);

        // Create 20 regular tasks per tenant
        foreach ($taskTitles as $index => $title) {
            $user        = $users->random();
            $status      = $statuses[array_rand($statuses)];
            $daysOffset  = rand(-10, 30);
            $morphTarget = $morphTargets[array_rand($morphTargets)];

            Task::create([
                'tenant_id'           => $tenant->id,
                'title'               => $title,
                'description'         => fake()->optional(0.7)->sentence(),
                'due_date'            => now()->addDays($daysOffset)->toDateString(),
                'due_time'            => fake()->optional(0.6)->time('H:i'),
                'priority'            => $priorities[array_rand($priorities)],
                'status'              => $status,
                'is_recurring'        => false,
                'taskable_type'       => $morphTarget['type'],
                'taskable_id'         => $morphTarget['id'],
                'assigned_to'         => $user->id,
                'created_by'          => $admin->id,
                'completed_at'        => $status === 'completed' ? now()->subDays(rand(1, 15)) : null,
            ]);
        }

        // Create 5 recurring tasks per tenant
        foreach ($recurringTitles as $title) {
            $user         = $users->random();
            $morphTarget  = $morphTargets[array_rand($morphTargets)];
            $recType      = $recurrenceTypes[array_rand($recurrenceTypes)];

            Task::create([
                'tenant_id'              => $tenant->id,
                'title'                  => $title,
                'description'            => 'Recurring task — auto-generated on schedule.',
                'due_date'               => now()->addDays(rand(1, 14))->toDateString(),
                'due_time'               => '10:00',
                'priority'               => fake()->randomElement(['medium', 'high']),
                'status'                 => 'pending',
                'is_recurring'           => true,
                'recurrence_type'        => $recType,
                'recurrence_interval'    => $recType === 'daily' ? rand(1, 7) : 1,
                'recurrence_end_date'    => now()->addMonths(rand(3, 12))->toDateString(),
                'taskable_type'          => $morphTarget['type'],
                'taskable_id'            => $morphTarget['id'],
                'assigned_to'            => $user->id,
                'created_by'             => $admin->id,
                'completed_at'           => null,
            ]);
        }

        $this->command->line("  → {$tenant->name}: Created 25 tasks (20 regular + 5 recurring).");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Calendar Events
    // ─────────────────────────────────────────────────────────────────────────

    private function createCalendarEvents(
        object $tenant,
        object $admin,
        object $users,
        object $contacts,
        object $accounts,
        object $opportunities
    ): void {
        $eventsData = [
            ['title' => 'Initial Discovery Call',          'type' => 'call',      'duration_minutes' => 30],
            ['title' => 'Product Demo Session',            'type' => 'demo',      'duration_minutes' => 60],
            ['title' => 'Contract Review Meeting',         'type' => 'meeting',   'duration_minutes' => 45],
            ['title' => 'Quarterly Business Review',       'type' => 'meeting',   'duration_minutes' => 90],
            ['title' => 'Onboarding Kickoff',              'type' => 'meeting',   'duration_minutes' => 60],
            ['title' => 'Technical Deep Dive',             'type' => 'demo',      'duration_minutes' => 90],
            ['title' => 'Sales Follow-Up Call',            'type' => 'follow_up', 'duration_minutes' => 20],
            ['title' => 'Annual Strategy Session',         'type' => 'meeting',   'duration_minutes' => 120],
            ['title' => 'CRM Best Practices Webinar',      'type' => 'webinar',   'duration_minutes' => 60],
            ['title' => 'Pipeline Review Sync',            'type' => 'meeting',   'duration_minutes' => 30],
            ['title' => 'Proposal Walk-Through',           'type' => 'follow_up', 'duration_minutes' => 45],
            ['title' => 'Customer Health Check Call',      'type' => 'call',      'duration_minutes' => 30],
            ['title' => 'Renewal Negotiation Call',        'type' => 'call',      'duration_minutes' => 60],
            ['title' => 'New Feature Showcase Demo',       'type' => 'demo',      'duration_minutes' => 45],
            ['title' => 'Executive Stakeholder Briefing',  'type' => 'meeting',   'duration_minutes' => 60],
            ['title' => 'Competitive Analysis Workshop',   'type' => 'meeting',   'duration_minutes' => 90],
            ['title' => 'Implementation Planning Meeting', 'type' => 'meeting',   'duration_minutes' => 60],
            ['title' => 'Feedback & Review Session',       'type' => 'follow_up', 'duration_minutes' => 30],
            ['title' => 'Industry Trends Webinar',         'type' => 'webinar',   'duration_minutes' => 60],
            ['title' => 'Deal Closing Strategy Call',      'type' => 'call',      'duration_minutes' => 45],
        ];

        $statuses        = ['scheduled', 'completed', 'cancelled', 'no_show'];
        $calSources      = ['google', 'outlook', 'ical', null, null]; // null = no external sync
        $meetingPrefixes = ['https://meet.google.com/', 'https://teams.microsoft.com/l/meetup-join/', 'https://zoom.us/j/'];
        $locations       = [
            'Conference Room A', 'Board Room', 'Client Office', 'Virtual / Zoom',
            'Headquarters', '3rd Floor Meeting Room', null,
        ];

        $morphTargets = $this->buildMorphTargets($contacts, $accounts, $opportunities);

        foreach ($eventsData as $data) {
            $user         = $users->random();
            $daysOffset   = rand(-20, 45);
            $startAt      = now()->addDays($daysOffset)->setHour(rand(8, 16))->setMinute(fake()->randomElement([0, 15, 30, 45]))->setSecond(0);
            $endAt        = $startAt->copy()->addMinutes($data['duration_minutes']);
            $status       = $daysOffset < -2 ? fake()->randomElement(['completed', 'no_show', 'cancelled']) : 'scheduled';
            $calSource    = $calSources[array_rand($calSources)];
            $morphTarget  = $morphTargets[array_rand($morphTargets)];

            CalendarEvent::create([
                'tenant_id'                => $tenant->id,
                'title'                    => $data['title'],
                'description'              => fake()->optional(0.6)->sentence(),
                'start_at'                 => $startAt,
                'end_at'                   => $endAt,
                'all_day'                  => false,
                'location'                 => $locations[array_rand($locations)],
                'meeting_link'             => fake()->optional(0.7)->randomElement($meetingPrefixes) ? $meetingPrefixes[array_rand($meetingPrefixes)] . Str::random(10) : null,
                'type'                     => $data['type'],
                'status'                   => $status,
                'external_calendar_id'     => $calSource ? Str::uuid()->toString() : null,
                'external_calendar_source' => $calSource,
                'invite_url'               => $data['type'] === 'demo' ? 'https://calendly.com/' . Str::slug($tenant->name) . '/demo' : null,
                'eventable_type'           => $morphTarget['type'],
                'eventable_id'             => $morphTarget['id'],
                'organizer_id'             => $user->id,
                'created_by'               => $admin->id,
            ]);
        }

        $this->command->line("  → {$tenant->name}: Created " . count($eventsData) . " calendar events.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Call Logs
    // ─────────────────────────────────────────────────────────────────────────

    private function createCallLogs(
        object $tenant,
        object $admin,
        object $users,
        object $contacts,
        object $leads,
        object $accounts
    ): void {
        $callSubjects = [
            'Initial outreach call',
            'Follow-up after email',
            'Qualification call',
            'Product enquiry call',
            'Demo scheduling call',
            'Contract discussion',
            'Renewal conversation',
            'Support escalation call',
            'Inbound sales enquiry',
            'Customer check-in call',
            'Upsell opportunity call',
            'Voicemail — awaiting callback',
            'Call to confirm meeting details',
            'Post-demo debrief call',
            'Feedback call after onboarding',
            'Pricing negotiation call',
            'Call to discuss proposal',
            'Competitor comparison call',
            'Cold outreach call',
            'Partnership discussion',
            'Lead re-engagement call',
            'End-of-quarter push call',
            'Referral introduction call',
            'Technical troubleshooting call',
            'Contract renewal final call',
        ];

        $directions = ['inbound', 'outbound'];
        $statuses   = ['completed', 'completed', 'completed', 'no_answer', 'busy', 'voicemail', 'failed'];

        // Mix contacts, leads, and accounts as loggable targets
        $loggableTargets = [];

        foreach ($contacts->take(10) as $c) {
            $loggableTargets[] = ['type' => Contact::class, 'id' => $c->id, 'phone' => $c->phone];
        }
        foreach ($leads->take(8) as $l) {
            $loggableTargets[] = ['type' => Lead::class, 'id' => $l->id, 'phone' => $l->phone];
        }
        foreach ($accounts->take(5) as $a) {
            $loggableTargets[] = ['type' => Account::class, 'id' => $a->id, 'phone' => $a->phone];
        }

        if (empty($loggableTargets)) {
            $this->command->warn("  → {$tenant->name}: No targets found for call logs, skipping.");
            return;
        }

        foreach ($callSubjects as $subject) {
            $user    = $users->random();
            $target  = $loggableTargets[array_rand($loggableTargets)];
            $status  = $statuses[array_rand($statuses)];
            $calledAt = now()->subDays(rand(0, 60))->subHours(rand(0, 8));

            $durationSeconds = $status === 'completed'
                ? rand(30, 2700)  // 30 seconds to 45 minutes
                : ($status === 'voicemail' ? rand(15, 90) : null);

            CallLog::create([
                'tenant_id'        => $tenant->id,
                'subject'          => $subject,
                'description'      => fake()->optional(0.6)->sentence(),
                'direction'        => $directions[array_rand($directions)],
                'duration_seconds' => $durationSeconds,
                'status'           => $status,
                'phone_number'     => $target['phone'] ?? fake()->phoneNumber(),
                'recording_url'    => ($status === 'completed' && fake()->boolean(30))
                    ? 'https://recordings.example.com/' . Str::uuid() . '.mp3'
                    : null,
                'loggable_type'    => $target['type'],
                'loggable_id'      => $target['id'],
                'user_id'          => $user->id,
                'called_at'        => $calledAt,
            ]);
        }

        $this->command->line("  → {$tenant->name}: Created " . count($callSubjects) . " call logs.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Email Logs
    // ─────────────────────────────────────────────────────────────────────────

    private function createEmailLogs(
        object $tenant,
        object $admin,
        object $users,
        object $contacts,
        object $leads,
        object $accounts
    ): void {
        $emailsData = [
            ['subject' => 'Introduction to NavCRM — Let\'s Connect', 'direction' => 'outbound'],
            ['subject' => 'Re: Demo Request — Confirming Your Slot',  'direction' => 'outbound'],
            ['subject' => 'Your Personalised Proposal is Ready',      'direction' => 'outbound'],
            ['subject' => 'Quick follow-up from our call',            'direction' => 'outbound'],
            ['subject' => 'Inbound: I\'m interested in your product', 'direction' => 'inbound'],
            ['subject' => 'Can we schedule a demo this week?',        'direction' => 'inbound'],
            ['subject' => 'NavCRM Onboarding Guide — Getting Started','direction' => 'outbound'],
            ['subject' => 'Re: Pricing — What are your options?',     'direction' => 'inbound'],
            ['subject' => 'Contract attached — please review',        'direction' => 'outbound'],
            ['subject' => 'Thank you for the meeting today',          'direction' => 'outbound'],
            ['subject' => 'Following up on the proposal',             'direction' => 'outbound'],
            ['subject' => 'Are you still interested in NavCRM?',      'direction' => 'outbound'],
            ['subject' => 'Re: Support issue — ticket #1042',         'direction' => 'inbound'],
            ['subject' => 'Your renewal is coming up in 30 days',     'direction' => 'outbound'],
            ['subject' => 'Exciting new features just launched!',     'direction' => 'outbound'],
            ['subject' => 'We would love your feedback',              'direction' => 'outbound'],
            ['subject' => 'Re: Can we discuss the enterprise plan?',  'direction' => 'inbound'],
            ['subject' => 'Inbound referral introduction',            'direction' => 'inbound'],
            ['subject' => 'Q1 Business Review — Agenda Inside',       'direction' => 'outbound'],
            ['subject' => 'Special offer ending Friday!',             'direction' => 'outbound'],
            ['subject' => 'Re: Integration with Salesforce',          'direction' => 'inbound'],
            ['subject' => 'Invite: CRM Best Practices Webinar',       'direction' => 'outbound'],
            ['subject' => 'Account setup confirmation',               'direction' => 'outbound'],
            ['subject' => 'Re: Need help migrating data',             'direction' => 'inbound'],
            ['subject' => 'End of quarter — Last chance to sign',     'direction' => 'outbound'],
        ];

        $statuses = ['sent', 'sent', 'opened', 'opened', 'clicked', 'received', 'bounced'];
        $sources  = ['gmail', 'gmail', 'outlook', 'bcc_dropbox', 'manual'];

        // Mix contacts, leads, and accounts as emailable targets
        $emailableTargets = [];

        foreach ($contacts->take(12) as $c) {
            $emailableTargets[] = ['type' => Contact::class, 'id' => $c->id, 'email' => $c->email];
        }
        foreach ($leads->take(8) as $l) {
            $emailableTargets[] = ['type' => Lead::class, 'id' => $l->id, 'email' => $l->email];
        }
        foreach ($accounts->take(5) as $a) {
            $emailableTargets[] = ['type' => Account::class, 'id' => $a->id, 'email' => $a->email];
        }

        if (empty($emailableTargets)) {
            $this->command->warn("  → {$tenant->name}: No targets found for email logs, skipping.");
            return;
        }

        $tenantDomain = Str::slug($tenant->name) . '.com';

        foreach ($emailsData as $data) {
            $user    = $users->random();
            $target  = $emailableTargets[array_rand($emailableTargets)];
            $status  = $statuses[array_rand($statuses)];
            $source  = $sources[array_rand($sources)];
            $sentAt  = now()->subDays(rand(0, 60))->subHours(rand(0, 12));

            $isOutbound = $data['direction'] === 'outbound';
            $fromEmail  = $isOutbound ? $user->email : ($target['email'] ?? fake()->safeEmail());
            $toEmail    = $isOutbound ? ($target['email'] ?? fake()->safeEmail()) : "sales@{$tenantDomain}";

            $openedAt = null;
            if (in_array($status, ['opened', 'clicked']) && $isOutbound) {
                $openedAt = $sentAt->copy()->addHours(rand(1, 48));
            }

            EmailLog::create([
                'tenant_id'      => $tenant->id,
                'subject'        => $data['subject'],
                'body'           => fake()->optional(0.8)->paragraphs(rand(1, 3), true),
                'direction'      => $data['direction'],
                'from_email'     => $fromEmail,
                'to_email'       => $toEmail,
                'cc_emails'      => fake()->optional(0.2)->randomElement([
                    [$user->email],
                    ["manager@{$tenantDomain}"],
                ]),
                'status'         => $status,
                'source'         => $source,
                'message_id'     => '<' . Str::uuid() . '@' . $tenantDomain . '>',
                'emailable_type' => $target['type'],
                'emailable_id'   => $target['id'],
                'user_id'        => $user->id,
                'sent_at'        => $sentAt,
                'opened_at'      => $openedAt,
            ]);
        }

        $this->command->line("  → {$tenant->name}: Created " . count($emailsData) . " email logs.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function buildMorphTargets(object $contacts, object $accounts, object $opportunities): array
    {
        $targets = [];

        foreach ($contacts->take(10) as $c) {
            $targets[] = ['type' => Contact::class, 'id' => $c->id];
        }
        foreach ($accounts->take(6) as $a) {
            $targets[] = ['type' => Account::class, 'id' => $a->id];
        }
        foreach ($opportunities->take(6) as $o) {
            $targets[] = ['type' => Opportunity::class, 'id' => $o->id];
        }

        // Fallback if all collections are empty
        if (empty($targets)) {
            $targets[] = ['type' => null, 'id' => null];
        }

        return $targets;
    }
}
