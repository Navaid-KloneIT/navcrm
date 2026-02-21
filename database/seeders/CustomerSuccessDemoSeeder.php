<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Contact;
use App\Models\HealthScore;
use App\Models\OnboardingPipeline;
use App\Models\OnboardingStep;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CustomerSuccessDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        if (! $tenant) {
            return;
        }

        $users    = User::where('tenant_id', $tenant->id)->get();
        $accounts = Account::where('tenant_id', $tenant->id)->take(5)->get();
        $contacts = Contact::where('tenant_id', $tenant->id)->take(5)->get();
        $tickets  = Ticket::where('tenant_id', $tenant->id)->take(3)->get();

        if ($users->isEmpty() || $accounts->isEmpty()) {
            return;
        }

        $csm = $users->first();

        // ── Onboarding Pipelines ─────────────────────────────────────────

        // Pipeline 1: Completed
        $pipeline1 = OnboardingPipeline::create([
            'tenant_id'       => $tenant->id,
            'account_id'      => $accounts[0]->id,
            'contact_id'      => $contacts->first()?->id,
            'assigned_to'     => $csm->id,
            'created_by'      => $csm->id,
            'pipeline_number' => 'OB-00001',
            'name'            => 'Enterprise Onboarding — ' . $accounts[0]->name,
            'description'     => 'Full onboarding programme: platform setup, data migration, and team training.',
            'status'          => 'completed',
            'started_at'      => now()->subDays(45),
            'completed_at'    => now()->subDays(5),
            'due_date'        => now()->subDays(3),
        ]);

        $steps1 = [
            'Welcome call & kickoff meeting',
            'Account configuration & branding',
            'Data import & migration',
            'Admin training session',
            'End-user training session',
            'Go-live sign-off',
        ];
        foreach ($steps1 as $i => $title) {
            OnboardingStep::create([
                'onboarding_pipeline_id' => $pipeline1->id,
                'title'                  => $title,
                'sort_order'             => $i,
                'is_completed'           => true,
                'completed_at'           => now()->subDays(45 - ($i * 7)),
                'completed_by'           => $csm->id,
                'due_date'               => now()->subDays(40 - ($i * 7)),
            ]);
        }

        // Pipeline 2: In Progress
        $pipeline2 = OnboardingPipeline::create([
            'tenant_id'       => $tenant->id,
            'account_id'      => $accounts->count() > 1 ? $accounts[1]->id : $accounts[0]->id,
            'contact_id'      => $contacts->count() > 1 ? $contacts[1]->id : null,
            'assigned_to'     => $csm->id,
            'created_by'      => $csm->id,
            'pipeline_number' => 'OB-00002',
            'name'            => 'Standard Onboarding — ' . ($accounts->count() > 1 ? $accounts[1]->name : $accounts[0]->name),
            'description'     => 'Standard onboarding for mid-market client.',
            'status'          => 'in_progress',
            'started_at'      => now()->subDays(14),
            'due_date'        => now()->addDays(14),
        ]);

        $steps2 = [
            ['title' => 'Welcome call & kickoff', 'done' => true],
            ['title' => 'Account setup & configuration', 'done' => true],
            ['title' => 'User training session', 'done' => true],
            ['title' => 'Integration setup', 'done' => false],
            ['title' => 'Go-live & review', 'done' => false],
        ];
        foreach ($steps2 as $i => $s) {
            OnboardingStep::create([
                'onboarding_pipeline_id' => $pipeline2->id,
                'title'                  => $s['title'],
                'sort_order'             => $i,
                'is_completed'           => $s['done'],
                'completed_at'           => $s['done'] ? now()->subDays(14 - ($i * 3)) : null,
                'completed_by'           => $s['done'] ? $csm->id : null,
                'due_date'               => now()->subDays(7 - ($i * 4)),
            ]);
        }

        // Pipeline 3: Not Started
        $pipeline3 = OnboardingPipeline::create([
            'tenant_id'       => $tenant->id,
            'account_id'      => $accounts->count() > 2 ? $accounts[2]->id : $accounts[0]->id,
            'assigned_to'     => $users->count() > 1 ? $users[1]->id : $csm->id,
            'created_by'      => $csm->id,
            'pipeline_number' => 'OB-00003',
            'name'            => 'Quick Start — ' . ($accounts->count() > 2 ? $accounts[2]->name : $accounts[0]->name),
            'description'     => 'Lightweight onboarding for self-service client.',
            'status'          => 'not_started',
            'due_date'        => now()->addDays(30),
        ]);

        $steps3 = ['Send welcome pack', 'Schedule kickoff call', 'Platform walkthrough', 'First review'];
        foreach ($steps3 as $i => $title) {
            OnboardingStep::create([
                'onboarding_pipeline_id' => $pipeline3->id,
                'title'                  => $title,
                'sort_order'             => $i,
                'due_date'               => now()->addDays(7 + ($i * 5)),
            ]);
        }

        // ── Health Scores ────────────────────────────────────────────────

        $scoreProfiles = [
            ['overall' => 88, 'login' => 100, 'ticket' => 85, 'payment' => 80],
            ['overall' => 52, 'login' => 60,  'ticket' => 40, 'payment' => 55],
            ['overall' => 25, 'login' => 10,  'ticket' => 15, 'payment' => 45],
            ['overall' => 92, 'login' => 80,  'ticket' => 100, 'payment' => 95],
            ['overall' => 45, 'login' => 40,  'ticket' => 65, 'payment' => 30],
        ];

        foreach ($accounts as $idx => $account) {
            $profile = $scoreProfiles[$idx] ?? $scoreProfiles[0];

            // Create 3 historical scores per account
            for ($h = 2; $h >= 0; $h--) {
                $jitter = rand(-5, 5);
                HealthScore::create([
                    'tenant_id'     => $tenant->id,
                    'account_id'    => $account->id,
                    'overall_score'  => max(0, min(100, $profile['overall'] + $jitter)),
                    'login_score'    => max(0, min(100, $profile['login'] + rand(-5, 5))),
                    'ticket_score'   => max(0, min(100, $profile['ticket'] + rand(-5, 5))),
                    'payment_score'  => max(0, min(100, $profile['payment'] + rand(-5, 5))),
                    'factors'        => [
                        'login'    => ['activity_count_30d' => rand(0, 20), 'detail' => rand(0, 20) . ' activities in last 30 days'],
                        'tickets'  => ['open_count_90d' => rand(0, 6), 'breached_count' => rand(0, 2), 'detail' => rand(0, 6) . ' open tickets in last 90 days'],
                        'payments' => ['total_invoices_6m' => rand(1, 8), 'paid_on_time' => rand(0, 8), 'overdue_count' => rand(0, 3), 'outstanding_overdue' => 0, 'detail' => 'Invoice payment analysis'],
                    ],
                    'calculated_at' => now()->subDays($h * 30),
                ]);
            }
        }

        // ── Surveys ──────────────────────────────────────────────────────

        // NPS Survey 1: Active with responses
        $npsSurvey1 = Survey::create([
            'tenant_id'     => $tenant->id,
            'survey_number' => 'SV-00001',
            'type'          => 'nps',
            'name'          => 'Q1 2026 NPS Survey',
            'description'   => 'Quarterly Net Promoter Score survey for all active clients.',
            'status'        => 'active',
            'created_by'    => $csm->id,
            'token'         => Str::random(64),
        ]);

        $npsScores = [10, 9, 9, 8, 7, 10, 3, 9, 8, 6];
        $npsComments = [
            'Excellent platform, highly recommend!',
            'Great support team.',
            'Very useful for our sales process.',
            'Good but could improve reporting.',
            'Decent product overall.',
            'Love the CRM features!',
            'Too many bugs lately.',
            'Best CRM we have used.',
            'Solid product.',
            'Support response times are slow.',
        ];
        foreach ($npsScores as $i => $score) {
            SurveyResponse::create([
                'survey_id'    => $npsSurvey1->id,
                'tenant_id'    => $tenant->id,
                'contact_id'   => $contacts->count() > $i ? $contacts[$i % $contacts->count()]->id : null,
                'account_id'   => $accounts->count() > $i ? $accounts[$i % $accounts->count()]->id : null,
                'score'        => $score,
                'comment'      => $npsComments[$i],
                'responded_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        // CSAT Survey 1: Active, linked to a ticket
        $csatSurvey1 = Survey::create([
            'tenant_id'     => $tenant->id,
            'survey_number' => 'SV-00002',
            'type'          => 'csat',
            'name'          => 'Post-Resolution Satisfaction',
            'description'   => 'How satisfied were you with your recent support experience?',
            'status'        => 'active',
            'ticket_id'     => $tickets->first()?->id,
            'created_by'    => $csm->id,
            'token'         => Str::random(64),
        ]);

        $csatScores = [9, 8, 7, 10, 6, 8];
        $csatComments = [
            'Issue was resolved quickly.',
            'Good experience overall.',
            'Took a bit long but got fixed.',
            'Amazing support, thank you!',
            'Could have been faster.',
            'Helpful agent.',
        ];
        foreach ($csatScores as $i => $score) {
            SurveyResponse::create([
                'survey_id'    => $csatSurvey1->id,
                'tenant_id'    => $tenant->id,
                'contact_id'   => $contacts->count() > $i ? $contacts[$i % $contacts->count()]->id : null,
                'account_id'   => $accounts->count() > 0 ? $accounts[$i % $accounts->count()]->id : null,
                'score'        => $score,
                'comment'      => $csatComments[$i],
                'responded_at' => now()->subDays(rand(1, 20)),
            ]);
        }

        // NPS Survey 2: Closed (historical)
        Survey::create([
            'tenant_id'     => $tenant->id,
            'survey_number' => 'SV-00003',
            'type'          => 'nps',
            'name'          => 'Q4 2025 NPS Survey',
            'description'   => 'Previous quarter NPS survey.',
            'status'        => 'closed',
            'created_by'    => $csm->id,
            'token'         => Str::random(64),
        ]);

        // CSAT Survey 2: Draft (upcoming)
        Survey::create([
            'tenant_id'     => $tenant->id,
            'survey_number' => 'SV-00004',
            'type'          => 'csat',
            'name'          => 'Onboarding Satisfaction Survey',
            'description'   => 'Post-onboarding satisfaction feedback.',
            'status'        => 'draft',
            'created_by'    => $csm->id,
            'token'         => Str::random(64),
        ]);
    }
}
