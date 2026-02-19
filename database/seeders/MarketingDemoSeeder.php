<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignTargetList;
use App\Models\Contact;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Models\LandingPage;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WebForm;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketingDemoSeeder extends Seeder
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

            $admin    = $users->firstWhere('is_active', true) ?? $users->first();
            $contacts = Contact::where('tenant_id', $tenant->id)->get();

            $this->command->info("Seeding Marketing data for tenant: {$tenant->name}");

            $templates = $this->createEmailTemplates($tenant, $admin);
            $webForms  = $this->createWebForms($tenant, $admin, $users);
            $this->createLandingPages($tenant, $admin, $webForms);
            $this->createCampaigns($tenant, $admin, $users, $contacts, $templates);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Email Templates
    // ─────────────────────────────────────────────────────────────────────────

    private function createEmailTemplates(object $tenant, object $admin): array
    {
        $templatesData = [
            [
                'name'    => 'Product Launch Announcement',
                'subject' => 'Introducing Our Latest Innovation',
                'body'    => "<h1>Exciting News!</h1>\n<p>Hi {{first_name}},</p>\n<p>We're thrilled to announce the launch of our newest product. As a valued customer you get early access — click below to learn more.</p>\n<p><a href='{{cta_url}}'>Explore Now →</a></p>\n<p>Best,<br>The {{company_name}} Team</p>",
            ],
            [
                'name'    => 'Monthly Newsletter',
                'subject' => '{{month}} Update from {{company_name}}',
                'body'    => "<h1>{{month}} Newsletter</h1>\n<p>Hi {{first_name}},</p>\n<p>Here's a round-up of what happened at {{company_name}} this month:</p>\n<ul>\n  <li>New feature spotlight</li>\n  <li>Customer success story</li>\n  <li>Upcoming events</li>\n</ul>\n<p>Stay tuned for more updates!</p>",
            ],
            [
                'name'    => 'Webinar Invitation',
                'subject' => "You're Invited: Free Live Webinar",
                'body'    => "<h1>Join Us Live</h1>\n<p>Dear {{first_name}},</p>\n<p>We're hosting an exclusive webinar and you're invited!</p>\n<p><strong>Date:</strong> {{event_date}}<br><strong>Time:</strong> 2:00 PM EST</p>\n<p>Seats are limited — <a href='{{register_url}}'>Register now</a>.</p>",
            ],
            [
                'name'    => 'Re-engagement Email',
                'subject' => "We miss you, {{first_name}}!",
                'body'    => "<h1>It's Been a While</h1>\n<p>Hi {{first_name}},</p>\n<p>We noticed you haven't logged in recently. We've added a lot since your last visit!</p>\n<p>Come back and use code <strong>WELCOME20</strong> for 20% off your next renewal.</p>\n<p><a href='{{login_url}}'>Log In Now</a></p>",
            ],
            [
                'name'    => 'End-of-Quarter Offer',
                'subject' => 'Last Chance: Q{{quarter}} Special Offer Expires Tonight',
                'body'    => "<h1>Don't Miss Out</h1>\n<p>Hi {{first_name}},</p>\n<p>Our Q{{quarter}} promotion ends at midnight tonight. Lock in your discounted rate before it's gone.</p>\n<p><a href='{{offer_url}}'>Claim Your Discount</a></p>",
            ],
        ];

        $templates = [];
        foreach ($templatesData as $data) {
            $templates[] = EmailTemplate::create([
                'tenant_id'  => $tenant->id,
                'name'       => $data['name'],
                'subject'    => $data['subject'],
                'body'       => $data['body'],
                'is_active'  => true,
                'created_by' => $admin->id,
            ]);
        }

        $this->command->line("  → {$tenant->name}: Created " . count($templates) . " email templates.");
        return $templates;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Campaigns + Target Lists + Email Campaigns
    // ─────────────────────────────────────────────────────────────────────────

    private function createCampaigns(
        object $tenant,
        object $admin,
        object $users,
        object $contacts,
        array  $templates
    ): void {
        $campaignsData = [
            [
                'name'           => 'Q1 Product Launch',
                'type'           => 'email',
                'status'         => 'active',
                'description'    => 'Launch campaign for our Q1 product release targeting existing customers and warm prospects.',
                'start_date'     => now()->subDays(15),
                'end_date'       => now()->addDays(30),
                'planned_budget' => 5000.00,
                'actual_budget'  => 3200.00,
                'target_revenue' => 50000.00,
                'actual_revenue' => 18500.00,
            ],
            [
                'name'           => 'Spring Webinar Series',
                'type'           => 'webinar',
                'status'         => 'active',
                'description'    => 'Three-part webinar series covering industry trends, best practices, and live Q&A sessions.',
                'start_date'     => now()->subDays(5),
                'end_date'       => now()->addDays(45),
                'planned_budget' => 2500.00,
                'actual_budget'  => 1800.00,
                'target_revenue' => 25000.00,
                'actual_revenue' => 8500.00,
            ],
            [
                'name'           => 'Annual User Conference',
                'type'           => 'event',
                'status'         => 'draft',
                'description'    => 'Annual flagship event bringing together our entire customer base for networking and product announcements.',
                'start_date'     => now()->addDays(60),
                'end_date'       => now()->addDays(62),
                'planned_budget' => 25000.00,
                'actual_budget'  => 0.00,
                'target_revenue' => 100000.00,
                'actual_revenue' => 0.00,
            ],
            [
                'name'           => 'Retargeting Digital Ads — Q2',
                'type'           => 'digital_ads',
                'status'         => 'paused',
                'description'    => 'Paid retargeting campaign for website visitors who viewed the pricing page but did not convert.',
                'start_date'     => now()->subDays(30),
                'end_date'       => now()->addDays(15),
                'planned_budget' => 8000.00,
                'actual_budget'  => 5600.00,
                'target_revenue' => 40000.00,
                'actual_revenue' => 12000.00,
            ],
            [
                'name'           => 'End-of-Quarter Push',
                'type'           => 'email',
                'status'         => 'completed',
                'description'    => 'Last-mile email campaign to close deals before quarter end with a limited-time discount.',
                'start_date'     => now()->subDays(45),
                'end_date'       => now()->subDays(5),
                'planned_budget' => 1500.00,
                'actual_budget'  => 1350.00,
                'target_revenue' => 30000.00,
                'actual_revenue' => 28500.00,
            ],
            [
                'name'           => 'Direct Mail Holiday Campaign',
                'type'           => 'direct_mail',
                'status'         => 'completed',
                'description'    => 'Printed holiday cards and a special gift sent to top 50 customers.',
                'start_date'     => now()->subDays(90),
                'end_date'       => now()->subDays(70),
                'planned_budget' => 3000.00,
                'actual_budget'  => 2950.00,
                'target_revenue' => 20000.00,
                'actual_revenue' => 22000.00,
            ],
        ];

        $listNamePool = [
            'Warm Leads', 'Existing Customers', 'Trial Users',
            'Enterprise Prospects', 'Re-engagement', 'Newsletter Subscribers',
            'Conference Attendees', 'Website Visitors',
        ];

        foreach ($campaignsData as $data) {
            $owner = $users->random();

            $campaign = Campaign::create([
                'tenant_id'      => $tenant->id,
                'name'           => $data['name'],
                'type'           => $data['type'],
                'status'         => $data['status'],
                'description'    => $data['description'],
                'start_date'     => $data['start_date'],
                'end_date'       => $data['end_date'],
                'planned_budget' => $data['planned_budget'],
                'actual_budget'  => $data['actual_budget'],
                'target_revenue' => $data['target_revenue'],
                'actual_revenue' => $data['actual_revenue'],
                'owner_id'       => $owner->id,
                'created_by'     => $admin->id,
            ]);

            // 1-2 target lists with random contacts
            $numLists = rand(1, 2);
            for ($i = 0; $i < $numLists; $i++) {
                $targetList = CampaignTargetList::create([
                    'tenant_id'   => $tenant->id,
                    'campaign_id' => $campaign->id,
                    'name'        => $listNamePool[array_rand($listNamePool)] . ($i > 0 ? ' B' : ''),
                    'description' => 'Segmented contact list for ' . $campaign->name,
                ]);

                if ($contacts->isNotEmpty()) {
                    $slice = $contacts->random(min(rand(4, 10), $contacts->count()));
                    $targetList->contacts()->attach($slice->pluck('id')->toArray());
                }
            }

            // Email campaigns for email-type campaigns only
            if ($data['type'] === 'email' && ! empty($templates)) {
                $template    = $templates[array_rand($templates)];
                $isSent      = in_array($data['status'], ['completed', 'active']);
                $totalSent   = $isSent ? rand(500, 5000)  : 0;
                $totalOpens  = $isSent ? rand(60, (int)($totalSent * 0.55))  : 0;
                $totalClicks = $isSent ? rand(10, (int)($totalOpens * 0.40)) : 0;

                EmailCampaign::create([
                    'tenant_id'          => $tenant->id,
                    'campaign_id'        => $campaign->id,
                    'email_template_id'  => $template->id,
                    'name'               => $campaign->name . ' — Email Blast',
                    'status'             => $isSent ? 'sent' : 'draft',
                    'from_name'          => $tenant->name,
                    'from_email'         => 'marketing@' . Str::slug($tenant->name) . '.com',
                    'subject'            => $template->subject,
                    'subject_a'          => $template->subject,
                    'subject_b'          => 'Limited Time: ' . $template->subject,
                    'scheduled_at'       => $isSent
                        ? now()->subDays(rand(5, 20))
                        : now()->addDays(rand(2, 7)),
                    'sent_at'            => $isSent ? now()->subDays(rand(1, 15)) : null,
                    'winning_variant'    => $isSent ? fake()->randomElement(['a', 'b', null]) : null,
                    'total_sent'         => $totalSent,
                    'total_opens'        => $totalOpens,
                    'total_clicks'       => $totalClicks,
                    'total_bounces'      => $isSent ? rand(5, 80) : 0,
                    'total_unsubscribes' => $isSent ? rand(2, 30) : 0,
                    'owner_id'           => $owner->id,
                    'created_by'         => $admin->id,
                ]);
            }
        }

        $this->command->line("  → {$tenant->name}: Created " . count($campaignsData) . " campaigns.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Web Forms
    // ─────────────────────────────────────────────────────────────────────────

    private function createWebForms(object $tenant, object $admin, object $users): array
    {
        $formsData = [
            [
                'name'               => 'Contact Us Form',
                'description'        => 'General enquiry form embedded on the Contact page.',
                'submit_button_text' => 'Send Message',
                'success_message'    => "Thank you! We'll be in touch within 24 hours.",
                'total_submissions'  => rand(40, 200),
                'fields'             => [
                    ['type' => 'text',     'label' => 'First Name',    'name' => 'first_name', 'required' => true],
                    ['type' => 'text',     'label' => 'Last Name',     'name' => 'last_name',  'required' => true],
                    ['type' => 'email',    'label' => 'Email Address', 'name' => 'email',      'required' => true],
                    ['type' => 'phone',    'label' => 'Phone Number',  'name' => 'phone',      'required' => false],
                    ['type' => 'textarea', 'label' => 'Message',       'name' => 'message',    'required' => true],
                ],
            ],
            [
                'name'               => 'Demo Request Form',
                'description'        => 'Capture leads requesting a personalised product demo.',
                'submit_button_text' => 'Request My Demo',
                'success_message'    => "Great! Our team will contact you within 2 business hours to schedule your demo.",
                'total_submissions'  => rand(15, 80),
                'fields'             => [
                    ['type' => 'text',     'label' => 'Full Name',       'name' => 'full_name', 'required' => true],
                    ['type' => 'email',    'label' => 'Work Email',       'name' => 'email',     'required' => true],
                    ['type' => 'text',     'label' => 'Company Name',     'name' => 'company',   'required' => true],
                    ['type' => 'select',   'label' => 'Company Size',     'name' => 'size',      'required' => false,
                     'options' => ['1–10', '11–50', '51–200', '201–500', '500+']],
                    ['type' => 'textarea', 'label' => 'How can we help?', 'name' => 'message',   'required' => false],
                ],
            ],
            [
                'name'               => 'Newsletter Signup',
                'description'        => 'Simple newsletter subscription widget for the homepage.',
                'submit_button_text' => 'Subscribe',
                'success_message'    => "You're subscribed! Check your inbox for a confirmation email.",
                'total_submissions'  => rand(80, 400),
                'fields'             => [
                    ['type' => 'text',     'label' => 'First Name',    'name' => 'first_name', 'required' => true],
                    ['type' => 'email',    'label' => 'Email Address', 'name' => 'email',      'required' => true],
                    ['type' => 'checkbox', 'label' => 'I agree to receive marketing emails', 'name' => 'consent', 'required' => true],
                ],
            ],
        ];

        $webForms = [];
        foreach ($formsData as $data) {
            $webForms[] = WebForm::create([
                'tenant_id'           => $tenant->id,
                'name'                => $data['name'],
                'description'         => $data['description'],
                'fields'              => json_encode($data['fields']),
                'submit_button_text'  => $data['submit_button_text'],
                'success_message'     => $data['success_message'],
                'redirect_url'        => null,
                'assign_to_user_id'   => $users->random()->id,
                'assign_by_geography' => false,
                'is_active'           => true,
                'total_submissions'   => $data['total_submissions'],
                'created_by'          => $admin->id,
            ]);
        }

        $this->command->line("  → {$tenant->name}: Created " . count($webForms) . " web forms.");
        return $webForms;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Landing Pages
    // ─────────────────────────────────────────────────────────────────────────

    private function createLandingPages(object $tenant, object $admin, array $webForms): void
    {
        $tenantSlug = Str::slug($tenant->name);

        $pagesData = [
            [
                'name'             => 'Free Demo Landing Page',
                'slug'             => "free-demo-{$tenantSlug}",
                'title'            => 'See NavCRM In Action — Book Your Free Demo',
                'description'      => 'Watch a personalised product demo and discover how NavCRM can transform your sales process.',
                'content'          => "<h2>Why NavCRM?</h2><ul><li>Close deals 30% faster</li><li>Centralise all customer data</li><li>Automate follow-ups</li></ul><p>No credit card required. 14-day free trial included.</p>",
                'meta_title'       => 'Free Product Demo | NavCRM',
                'meta_description' => 'Book your free, personalised NavCRM demo today. No credit card required.',
                'is_active'        => true,
                'page_views'       => rand(300, 2500),
                'web_form_id'      => isset($webForms[1]) ? $webForms[1]->id : null,
            ],
            [
                'name'             => 'Newsletter Signup Page',
                'slug'             => "newsletter-{$tenantSlug}",
                'title'            => 'Stay Ahead — Subscribe to Our CRM Newsletter',
                'description'      => 'Get weekly insights, actionable tips, and industry news delivered straight to your inbox.',
                'content'          => "<h2>What You'll Get</h2><ul><li>Weekly CRM tips and tricks</li><li>Industry trend reports</li><li>Exclusive subscriber-only offers</li></ul>",
                'meta_title'       => 'CRM Newsletter | NavCRM',
                'meta_description' => 'Subscribe to the NavCRM newsletter for weekly CRM tips and industry insights.',
                'is_active'        => true,
                'page_views'       => rand(100, 900),
                'web_form_id'      => isset($webForms[2]) ? $webForms[2]->id : null,
            ],
            [
                'name'             => 'Webinar Registration Page',
                'slug'             => "webinar-reg-{$tenantSlug}",
                'title'            => 'Join Our Upcoming Live Webinar — Register Now',
                'description'      => 'Learn actionable growth strategies from industry experts in our free live webinar.',
                'content'          => "<h2>What You'll Learn</h2><ul><li>How to double your pipeline in 90 days</li><li>Automating your follow-up process</li><li>Top CRM integrations for 2026</li></ul><p>Live Q&A included. Replay available for registered attendees.</p>",
                'meta_title'       => 'Free Webinar Registration | NavCRM',
                'meta_description' => 'Reserve your spot for our free live webinar. Limited seats available.',
                'is_active'        => false,
                'page_views'       => rand(50, 450),
                'web_form_id'      => null,
            ],
        ];

        foreach ($pagesData as $data) {
            LandingPage::create([
                'tenant_id'        => $tenant->id,
                'name'             => $data['name'],
                'slug'             => $data['slug'],
                'title'            => $data['title'],
                'description'      => $data['description'],
                'content'          => $data['content'],
                'meta_title'       => $data['meta_title'],
                'meta_description' => $data['meta_description'],
                'is_active'        => $data['is_active'],
                'page_views'       => $data['page_views'],
                'web_form_id'      => $data['web_form_id'],
                'created_by'       => $admin->id,
            ]);
        }

        $this->command->line("  → {$tenant->name}: Created " . count($pagesData) . " landing pages.");
    }
}
