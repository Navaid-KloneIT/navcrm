<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = \App\Models\Tenant::first();
        if (! $tenant) {
            return;
        }

        $users       = User::where('tenant_id', $tenant->id)->get();
        $accounts    = Account::where('tenant_id', $tenant->id)->take(3)->get();
        $contacts    = Contact::where('tenant_id', $tenant->id)->take(3)->get();
        $opportunity = Opportunity::where('tenant_id', $tenant->id)->first();

        if ($users->isEmpty()) {
            return;
        }

        $manager   = $users->first();
        $developer = $users->count() > 1 ? $users->get(1) : $manager;
        $designer  = $users->count() > 2 ? $users->get(2) : $manager;

        // ── Project 1: From Opportunity ────────────────────────────────────
        $project1 = Project::create([
            'tenant_id'           => $tenant->id,
            'opportunity_id'      => $opportunity?->id,
            'account_id'          => $accounts->get(0)?->id,
            'contact_id'          => $contacts->get(0)?->id,
            'manager_id'          => $manager->id,
            'created_by'          => $manager->id,
            'project_number'      => 'PRJ-00001',
            'name'                => 'CRM Platform Implementation',
            'description'         => 'Full implementation of the CRM platform including data migration, user training, and go-live support.',
            'status'              => 'active',
            'start_date'          => now()->subDays(20),
            'due_date'            => now()->addDays(40),
            'budget'              => 35000.00,
            'currency'            => 'USD',
            'is_from_opportunity' => true,
        ]);

        $project1->members()->attach([
            $manager->id   => ['role' => 'Project Manager', 'allocated_hours' => 40],
            $developer->id => ['role' => 'Lead Developer',  'allocated_hours' => 80],
        ]);
        if ($designer->id !== $developer->id) {
            $project1->members()->attach([$designer->id => ['role' => 'UI Designer', 'allocated_hours' => 30]]);
        }

        $milestones1 = [
            ['title' => 'Requirements & Discovery',       'days_offset' => -15, 'status' => 'completed'],
            ['title' => 'System Architecture Design',     'days_offset' => -5,  'status' => 'completed'],
            ['title' => 'Core Module Development',        'days_offset' => 20,  'status' => 'in_progress'],
            ['title' => 'Data Migration',                 'days_offset' => 30,  'status' => 'pending'],
            ['title' => 'User Acceptance Testing (UAT)',  'days_offset' => 38,  'status' => 'pending'],
            ['title' => 'Go-Live & Handover',             'days_offset' => 42,  'status' => 'pending'],
        ];

        foreach ($milestones1 as $i => $ms) {
            ProjectMilestone::create([
                'project_id'   => $project1->id,
                'created_by'   => $manager->id,
                'title'        => $ms['title'],
                'due_date'     => now()->addDays($ms['days_offset']),
                'status'       => $ms['status'],
                'sort_order'   => $i,
                'completed_at' => $ms['status'] === 'completed' ? now()->subDays(rand(1, 10)) : null,
            ]);
        }

        // Timesheets for project 1
        $timesheetData1 = [
            ['user' => $manager,   'hours' => 3.0, 'billable' => true,  'desc' => 'Weekly status meeting and client sync'],
            ['user' => $developer, 'hours' => 8.0, 'billable' => true,  'desc' => 'Contact module development'],
            ['user' => $developer, 'hours' => 6.5, 'billable' => true,  'desc' => 'Pipeline stage feature implementation'],
            ['user' => $designer,  'hours' => 5.0, 'billable' => true,  'desc' => 'Dashboard wireframes and mockups'],
            ['user' => $manager,   'hours' => 2.0, 'billable' => false, 'desc' => 'Internal team standup'],
            ['user' => $developer, 'hours' => 4.0, 'billable' => true,  'desc' => 'API endpoint testing and bug fixes'],
        ];

        foreach ($timesheetData1 as $i => $ts) {
            Timesheet::create([
                'tenant_id'    => $tenant->id,
                'project_id'   => $project1->id,
                'user_id'      => $ts['user']->id,
                'created_by'   => $ts['user']->id,
                'date'         => now()->subDays(rand(1, 18)),
                'hours'        => $ts['hours'],
                'description'  => $ts['desc'],
                'is_billable'  => $ts['billable'],
                'billable_rate'=> $ts['billable'] ? 120.00 : null,
            ]);
        }

        // ── Project 2: Website Redesign ────────────────────────────────────
        $project2 = Project::create([
            'tenant_id'      => $tenant->id,
            'account_id'     => $accounts->get(1)?->id,
            'contact_id'     => $contacts->get(1)?->id,
            'manager_id'     => $manager->id,
            'created_by'     => $manager->id,
            'project_number' => 'PRJ-00002',
            'name'           => 'Corporate Website Redesign',
            'description'    => 'Full redesign of corporate website with new brand guidelines, improved UX, and mobile-first approach.',
            'status'         => 'planning',
            'start_date'     => now()->addDays(5),
            'due_date'       => now()->addDays(60),
            'budget'         => 12000.00,
            'currency'       => 'USD',
        ]);

        $project2->members()->attach([
            $designer->id  => ['role' => 'Lead Designer', 'allocated_hours' => 60],
            $developer->id => ['role' => 'Frontend Dev',  'allocated_hours' => 40],
        ]);

        $milestones2 = [
            ['title' => 'Brand Audit & Research',   'days_offset' => 10, 'status' => 'pending'],
            ['title' => 'Design Concepts',          'days_offset' => 25, 'status' => 'pending'],
            ['title' => 'Frontend Development',     'days_offset' => 45, 'status' => 'pending'],
            ['title' => 'Content Migration',        'days_offset' => 55, 'status' => 'pending'],
            ['title' => 'Launch',                   'days_offset' => 62, 'status' => 'pending'],
        ];

        foreach ($milestones2 as $i => $ms) {
            ProjectMilestone::create([
                'project_id' => $project2->id,
                'created_by' => $manager->id,
                'title'      => $ms['title'],
                'due_date'   => now()->addDays($ms['days_offset']),
                'status'     => $ms['status'],
                'sort_order' => $i,
            ]);
        }

        // ── Project 3: Completed ───────────────────────────────────────────
        $project3 = Project::create([
            'tenant_id'      => $tenant->id,
            'account_id'     => $accounts->get(2)?->id,
            'manager_id'     => $manager->id,
            'created_by'     => $manager->id,
            'project_number' => 'PRJ-00003',
            'name'           => 'Mobile App MVP',
            'description'    => 'Development of the minimum viable product for the client mobile application.',
            'status'         => 'completed',
            'start_date'     => now()->subDays(90),
            'due_date'       => now()->subDays(10),
            'budget'         => 28000.00,
            'currency'       => 'USD',
            'completed_at'   => now()->subDays(8),
        ]);

        foreach (['Discovery', 'Design', 'Development', 'Testing', 'Launch'] as $i => $title) {
            ProjectMilestone::create([
                'project_id'   => $project3->id,
                'created_by'   => $manager->id,
                'title'        => $title,
                'due_date'     => now()->subDays(70 - $i * 15),
                'status'       => 'completed',
                'sort_order'   => $i,
                'completed_at' => now()->subDays(70 - $i * 15 + 2),
            ]);
        }

        // Timesheets for project 3
        for ($i = 0; $i < 8; $i++) {
            Timesheet::create([
                'tenant_id'    => $tenant->id,
                'project_id'   => $project3->id,
                'user_id'      => $users->random()->id,
                'created_by'   => $manager->id,
                'date'         => now()->subDays(rand(15, 85)),
                'hours'        => round(rand(2, 8) + 0.5 * rand(0, 1), 1),
                'description'  => 'Development and testing work',
                'is_billable'  => (bool) rand(0, 1),
                'billable_rate'=> 95.00,
            ]);
        }
    }
}
