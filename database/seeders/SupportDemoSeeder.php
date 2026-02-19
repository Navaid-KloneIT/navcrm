<?php

namespace Database\Seeders;

use App\Enums\TicketChannel;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Contact;
use App\Models\KbArticle;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SupportDemoSeeder extends Seeder
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

            $contacts = Contact::where('tenant_id', $tenant->id)->get();

            if ($contacts->isEmpty()) {
                $this->command->warn("No contacts found for tenant [{$tenant->name}], skipping tickets.");
            }

            $admin = $users->firstWhere('is_active', true) ?? $users->first();

            $this->command->info("Seeding Support data for tenant: {$tenant->name}");

            $this->createKbArticles($tenant, $admin);
            $this->enablePortalContacts($contacts);

            if ($contacts->isNotEmpty()) {
                $this->createTickets($tenant, $admin, $users, $contacts);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Knowledge Base Articles
    // ─────────────────────────────────────────────────────────────────────────

    private function createKbArticles(object $tenant, object $admin): void
    {
        $articlesData = [

            // ── Public & Published ───────────────────────────────────────────

            [
                'title'        => 'How to Reset Your Password',
                'category'     => 'Account',
                'is_public'    => true,
                'is_published' => true,
                'view_count'   => rand(150, 600),
                'body'         => <<<'MD'
## Resetting Your Password

If you've forgotten your password, follow these steps:

1. Go to the **Login** page.
2. Click **Forgot Password** below the sign-in form.
3. Enter your registered email address and click **Send Reset Link**.
4. Check your inbox for an email from NavCRM (check spam if it doesn't arrive).
5. Click the link in the email — it expires after **60 minutes**.
6. Choose a new password and confirm it.

> **Tip:** Use at least 8 characters with a mix of letters, numbers, and symbols for a strong password.

If you still can't log in after resetting, contact our support team.
MD,
            ],

            [
                'title'        => 'Getting Started with NavCRM',
                'category'     => 'Getting Started',
                'is_public'    => true,
                'is_published' => true,
                'view_count'   => rand(300, 900),
                'body'         => <<<'MD'
## Welcome to NavCRM!

This guide walks you through the first steps to get up and running.

### Step 1: Complete Your Profile
Go to **Settings → My Profile** to add your name, phone number, and time zone.

### Step 2: Add Your First Contact
Navigate to **Contacts → New Contact** and fill in the contact details. You can link a contact to an Account (Company) at any time.

### Step 3: Create a Lead
Go to **Leads → New Lead** to start tracking an inbound prospect. You can convert a lead to a Contact + Account with one click when they're qualified.

### Step 4: Create a Deal
Head to **Opportunities → New Opportunity** to start tracking a sales deal through your pipeline stages.

### Need Help?
Submit a support ticket from this portal at any time — we're here to help.
MD,
            ],

            [
                'title'        => 'How to Export Your Data to CSV',
                'category'     => 'Data Management',
                'is_public'    => true,
                'is_published' => true,
                'view_count'   => rand(80, 350),
                'body'         => <<<'MD'
## Exporting Data from NavCRM

You can export Contacts, Leads, and Opportunities as CSV files at any time.

### Steps to Export

1. Navigate to the module you want to export (e.g., **Contacts**).
2. Apply any filters to narrow the results (optional).
3. Click the **Export** button in the top-right corner of the list.
4. Select the columns/fields you want to include.
5. Click **Download CSV** — your browser will save the file.

### Supported Export Formats

| Module        | CSV | PDF |
|---------------|-----|-----|
| Contacts      | ✓   |     |
| Leads         | ✓   |     |
| Opportunities | ✓   |     |
| Quotes        | ✓   | ✓   |

> **Note:** Exports are limited to your tenant's data only.
MD,
            ],

            [
                'title'        => 'Understanding SLA Timers on Tickets',
                'category'     => 'Support',
                'is_public'    => true,
                'is_published' => true,
                'view_count'   => rand(60, 250),
                'body'         => <<<'MD'
## What is an SLA?

A **Service Level Agreement (SLA)** defines the maximum time our team has to provide a first response to your support ticket.

### Priority Levels & Response Times

| Priority | First Response Target |
|----------|-----------------------|
| Critical | 4 hours               |
| High     | 8 hours               |
| Medium   | 24 hours              |
| Low      | 72 hours              |

### SLA Status Indicators in the Portal

- **No badge** — Your ticket is within the SLA window.
- **Orange warning** — Less than 2 hours remain on the SLA clock.
- **Red "Breached"** — The SLA deadline has passed. Our team is already aware and working urgently.

If your ticket has an SLA breach badge, please be assured it has been flagged for immediate attention.
MD,
            ],

            [
                'title'        => 'Billing & Subscription FAQ',
                'category'     => 'Billing',
                'is_public'    => true,
                'is_published' => true,
                'view_count'   => rand(100, 450),
                'body'         => <<<'MD'
## Frequently Asked Billing Questions

**Q: When will I be charged?**
A: Subscriptions are billed monthly or annually on the same date as your first purchase.

**Q: Can I get a refund?**
A: We offer a 14-day money-back guarantee on all new subscriptions. Contact support within 14 days of your purchase.

**Q: How do I update my payment method?**
A: Go to **Settings → Billing** and click **Update Payment Method**. Your changes take effect immediately.

**Q: Can I upgrade my plan mid-cycle?**
A: Yes. Upgrades are prorated — you'll only be charged for the remaining days in your billing cycle.

**Q: What happens if my payment fails?**
A: We'll retry the charge twice over 3 days and notify you each time. If all retries fail, your account will be downgraded to the free plan.
MD,
            ],

            // ── Public & Draft ───────────────────────────────────────────────

            [
                'title'        => 'Using the NavCRM Mobile App',
                'category'     => 'Getting Started',
                'is_public'    => true,
                'is_published' => false,
                'view_count'   => rand(0, 30),
                'body'         => <<<'MD'
## NavCRM Mobile App *(Draft)*

The NavCRM mobile app is available for **iOS** (App Store) and **Android** (Google Play).

> This article is being updated. Check back soon for the full guide.

### Topics Covered
- Downloading and installing the app
- Logging in with your NavCRM account
- Viewing and creating contacts on the go
- Logging calls and activities
MD,
            ],

            // ── Internal & Published ─────────────────────────────────────────

            [
                'title'        => 'Agent Escalation Procedure',
                'category'     => 'Support',
                'is_public'    => false,
                'is_published' => true,
                'view_count'   => rand(20, 80),
                'body'         => <<<'MD'
## Internal Escalation Procedure

> **For Support Agents Only** — This article is not visible to customers.

### When to Escalate

- Ticket has **breached its SLA**.
- Customer is requesting a refund **over $500**.
- Issue requires **Engineering** team involvement (bugs, data loss, performance).
- Ticket belongs to a **VIP / Enterprise** account.
- Customer is threatening **legal action or chargeback**.

### How to Escalate

1. Change ticket status to **Escalated**.
2. Add an **Internal Note** with a clear summary of the issue and why it's being escalated.
3. Assign the ticket to the on-call **Senior Agent** or **Manager**.
4. Post in the `#support-escalations` Slack channel with the ticket number.
5. Follow up within 1 hour to confirm the escalation was received.

### Escalation Contacts

| Level      | Contact       | Hours          |
|------------|---------------|----------------|
| Tier 2     | Senior Agent  | Business hours |
| Tier 3     | Engineering   | Slack + email  |
| Executive  | VP Operations | VIP only       |
MD,
            ],

            [
                'title'        => 'Common Issues & Quick Fixes',
                'category'     => 'Technical',
                'is_public'    => false,
                'is_published' => true,
                'view_count'   => rand(30, 100),
                'body'         => <<<'MD'
## Internal Troubleshooting Cheatsheet

> **For Support Agents Only.**

### Login Issues

| Symptom                         | Fix                                                                 |
|---------------------------------|---------------------------------------------------------------------|
| Correct password, can't log in  | Check user `is_active` flag in Settings → Users. Reset if needed.  |
| "Session expired" on every page | Clear browser cookies. Check session config in `.env`.             |
| 2FA code not working            | Verify server time is synced (NTP). Ask user to re-scan QR code.  |

### Data Issues

| Symptom                         | Fix                                                                 |
|---------------------------------|---------------------------------------------------------------------|
| Contact not appearing in list   | Check tenant_id matches and soft_delete is null.                   |
| Changes not visible to others   | Confirm tenant scope. Ask user to hard-reload (Ctrl+Shift+R).      |
| Duplicate records after import  | Use the de-dupe tool in Settings → Data Management.               |

### Email Campaign Issues

| Symptom                         | Fix                                                                 |
|---------------------------------|---------------------------------------------------------------------|
| Stuck in "Scheduled" status     | Check mail queue (Horizon). Verify SMTP creds in `.env`.           |
| Low open rates                  | Check spam score. Confirm SPF/DKIM/DMARC DNS records are correct.  |
MD,
            ],
        ];

        foreach ($articlesData as $data) {
            KbArticle::create([
                'tenant_id'    => $tenant->id,
                'title'        => $data['title'],
                'slug'         => Str::slug($data['title']) . '-' . Str::random(4),
                'category'     => $data['category'],
                'body'         => trim($data['body']),
                'is_public'    => $data['is_public'],
                'is_published' => $data['is_published'],
                'author_id'    => $admin->id,
                'view_count'   => $data['view_count'],
            ]);
        }

        $this->command->line("  → {$tenant->name}: Created " . count($articlesData) . " KB articles.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Portal Access — enable for first 5 contacts
    // ─────────────────────────────────────────────────────────────────────────

    private function enablePortalContacts(object $contacts): void
    {
        $portalContacts = $contacts->take(5);

        foreach ($portalContacts as $contact) {
            $contact->update([
                'portal_password' => Hash::make('portal123'),
                'portal_active'   => true,
            ]);
        }

        $this->command->line("  → Enabled portal access for {$portalContacts->count()} contacts (password: portal123).");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Tickets
    // ─────────────────────────────────────────────────────────────────────────

    private function createTickets(object $tenant, object $admin, object $users, object $contacts): void
    {
        $ticketsData = [
            // Open tickets
            ['subject' => 'Cannot log in to my account',                  'priority' => 'high',     'status' => 'open',        'channel' => 'portal'],
            ['subject' => 'Request to upgrade subscription plan',          'priority' => 'medium',   'status' => 'open',        'channel' => 'portal'],
            ['subject' => 'Email notifications are not being received',    'priority' => 'medium',   'status' => 'open',        'channel' => 'portal'],
            ['subject' => 'Forgot to cancel before annual renewal',        'priority' => 'high',     'status' => 'open',        'channel' => 'email'],
            ['subject' => 'Missing contacts after data migration',         'priority' => 'critical', 'status' => 'open',        'channel' => 'email'],

            // In Progress
            ['subject' => 'Invoice #1042 amount is incorrect',             'priority' => 'medium',   'status' => 'in_progress', 'channel' => 'email'],
            ['subject' => 'Dashboard charts not loading in Firefox',       'priority' => 'high',     'status' => 'in_progress', 'channel' => 'phone'],
            ['subject' => 'System is very slow during peak hours',         'priority' => 'critical', 'status' => 'in_progress', 'channel' => 'phone'],

            // Escalated
            ['subject' => 'API integration not returning data since v2.1', 'priority' => 'critical', 'status' => 'escalated',   'channel' => 'email'],
            ['subject' => 'Bulk CSV import failing for files over 5MB',    'priority' => 'high',     'status' => 'escalated',   'channel' => 'email'],

            // Resolved
            ['subject' => 'How do I export all contacts to CSV?',          'priority' => 'low',      'status' => 'resolved',    'channel' => 'portal'],
            ['subject' => 'Add a second admin user to my account',         'priority' => 'low',      'status' => 'resolved',    'channel' => 'portal'],
            ['subject' => 'Campaign statistics showing zero open rate',    'priority' => 'medium',   'status' => 'resolved',    'channel' => 'email'],

            // Closed
            ['subject' => 'Feature request: custom pipeline stage names',  'priority' => 'low',      'status' => 'closed',      'channel' => 'portal'],
            ['subject' => 'Password reset email not arriving',             'priority' => 'medium',   'status' => 'closed',      'channel' => 'email'],
        ];

        // Determine next ticket sequence number safely
        $lastTicket = Ticket::latest('id')->value('ticket_number');
        $seq        = $lastTicket ? ((int) substr($lastTicket, 3)) + 1 : 1;

        foreach ($ticketsData as $data) {
            $priority  = TicketPriority::from($data['priority']);
            $status    = TicketStatus::from($data['status']);
            $contact   = $contacts->random();
            $assignee  = $users->random();
            $createdAt = Carbon::now()->subDays(rand(1, 28))->subHours(rand(0, 12));
            $slaHours  = $priority->slaHours();

            // Timestamps for resolved / closed tickets
            $resolvedAt = null;
            $closedAt   = null;
            if (in_array($status, [TicketStatus::Resolved, TicketStatus::Closed])) {
                $resolvedAt = $createdAt->copy()->addHours(rand(1, max(1, (int)($slaHours * 0.8))));
            }
            if ($status === TicketStatus::Closed) {
                $closedAt = $resolvedAt->copy()->addHours(rand(2, 48));
            }

            $ticket = Ticket::create([
                'tenant_id'         => $tenant->id,
                'ticket_number'     => 'TK-' . str_pad($seq++, 5, '0', STR_PAD_LEFT),
                'subject'           => $data['subject'],
                'description'       => fake()->paragraph(rand(2, 4)),
                'status'            => $status->value,
                'priority'          => $priority->value,
                'channel'           => $data['channel'],
                'contact_id'        => $contact->id,
                'assigned_to'       => $assignee->id,
                'created_by'        => $admin->id,
                'sla_due_at'        => $createdAt->copy()->addHours($slaHours),
                'first_response_at' => $createdAt->copy()->addMinutes(rand(8, 90)),
                'resolved_at'       => $resolvedAt,
                'closed_at'         => $closedAt,
                'created_at'        => $createdAt,
                'updated_at'        => $resolvedAt ?? $createdAt->copy()->addHours(rand(1, 5)),
            ]);

            $this->addTicketComments($ticket, $assignee, $contact, $createdAt->copy());
        }

        $this->command->line("  → {$tenant->name}: Created " . count($ticketsData) . " tickets.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Ticket Comments
    // ─────────────────────────────────────────────────────────────────────────

    private function addTicketComments(Ticket $ticket, object $assignee, object $contact, Carbon $createdAt): void
    {
        $commentAt = $createdAt->copy()->addMinutes(rand(10, 60));

        // ── 1. Agent acknowledgement ──────────────────────────────────────────
        $responseTime = match ($ticket->priority->value) {
            'critical' => '1 hour',
            'high'     => '4 hours',
            'medium'   => '8 hours',
            default    => '24 hours',
        };

        TicketComment::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $assignee->id,
            'contact_id'  => null,
            'body'        => "Hi {$contact->first_name}, thank you for reaching out. I've picked up your ticket and am looking into it right now. I'll have an update for you within {$responseTime}.",
            'is_internal' => false,
            'created_at'  => $commentAt,
            'updated_at'  => $commentAt,
        ]);

        $commentAt->addMinutes(rand(20, 90));

        // ── 2. Optional internal note ─────────────────────────────────────────
        if (rand(0, 1)) {
            $teams = ['engineering', 'billing', 'account management', 'infrastructure'];
            TicketComment::create([
                'ticket_id'   => $ticket->id,
                'user_id'     => $assignee->id,
                'contact_id'  => null,
                'body'        => 'Internal note: ' . fake()->sentence() . ' Checking with the ' . $teams[array_rand($teams)] . ' team before responding.',
                'is_internal' => true,
                'created_at'  => $commentAt,
                'updated_at'  => $commentAt,
            ]);
            $commentAt->addMinutes(rand(15, 45));
        }

        // ── 3. Customer follow-up ─────────────────────────────────────────────
        if (rand(0, 1)) {
            $customerReplies = [
                "Thanks for the quick response! Just to add more context: " . fake()->sentence(),
                "I tried what you suggested but the issue is still happening. " . fake()->sentence(),
                "Appreciate the help! Any update on when this will be resolved?",
                "This is still occurring. I also noticed that " . fake()->sentence(),
                "No rush, just following up — is there any progress on this?",
            ];

            TicketComment::create([
                'ticket_id'   => $ticket->id,
                'user_id'     => null,
                'contact_id'  => $contact->id,
                'body'        => $customerReplies[array_rand($customerReplies)],
                'is_internal' => false,
                'created_at'  => $commentAt,
                'updated_at'  => $commentAt,
            ]);

            $commentAt->addMinutes(rand(30, 120));
        }

        // ── 4. Resolution comment for resolved/closed tickets ─────────────────
        if (in_array($ticket->status->value, ['resolved', 'closed'])) {
            $resolutionReplies = [
                "Great news — I've resolved the issue! " . fake()->sentence() . " Please don't hesitate to reach out if you need anything else.",
                "This has been fixed on our end. " . fake()->sentence() . " Let us know if the issue reappears.",
                "Issue resolved. The root cause was " . fake()->sentence() . " I've marked this ticket as resolved. Feel free to reopen it if needed.",
                "All sorted! " . fake()->sentence() . " Happy to help if you have any follow-up questions.",
            ];

            TicketComment::create([
                'ticket_id'   => $ticket->id,
                'user_id'     => $assignee->id,
                'contact_id'  => null,
                'body'        => $resolutionReplies[array_rand($resolutionReplies)],
                'is_internal' => false,
                'created_at'  => $commentAt,
                'updated_at'  => $commentAt,
            ]);
        }
    }
}
