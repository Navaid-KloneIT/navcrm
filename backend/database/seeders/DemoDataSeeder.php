<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Tag;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $guardName = 'sanctum';

        // Retrieve roles
        $adminRole = Role::where('name', 'admin')->where('guard_name', $guardName)->first();
        $managerRole = Role::where('name', 'manager')->where('guard_name', $guardName)->first();
        $salesRole = Role::where('name', 'sales')->where('guard_name', $guardName)->first();
        $viewerRole = Role::where('name', 'viewer')->where('guard_name', $guardName)->first();

        // Create 2 tenants
        $tenants = [];
        foreach (['Acme Corporation', 'Global Industries'] as $tenantName) {
            $tenants[] = Tenant::create([
                'name' => $tenantName,
                'slug' => \Illuminate\Support\Str::slug($tenantName),
                'is_active' => true,
            ]);
        }

        foreach ($tenants as $tenant) {
            // Create 5 users per tenant (1 admin, 1 manager, 2 sales, 1 viewer)
            $admin = User::create([
                'name' => "Admin {$tenant->name}",
                'email' => "admin@" . \Illuminate\Support\Str::slug($tenant->name) . ".com",
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]);
            if ($adminRole) {
                $admin->assignRole($adminRole);
            }

            $manager = User::create([
                'name' => "Manager {$tenant->name}",
                'email' => "manager@" . \Illuminate\Support\Str::slug($tenant->name) . ".com",
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]);
            if ($managerRole) {
                $manager->assignRole($managerRole);
            }

            $salesUsers = [];
            for ($i = 1; $i <= 2; $i++) {
                $salesUser = User::create([
                    'name' => "Sales Rep {$i} {$tenant->name}",
                    'email' => "sales{$i}@" . \Illuminate\Support\Str::slug($tenant->name) . ".com",
                    'password' => Hash::make('password'),
                    'tenant_id' => $tenant->id,
                    'is_active' => true,
                ]);
                if ($salesRole) {
                    $salesUser->assignRole($salesRole);
                }
                $salesUsers[] = $salesUser;
            }

            $viewer = User::create([
                'name' => "Viewer {$tenant->name}",
                'email' => "viewer@" . \Illuminate\Support\Str::slug($tenant->name) . ".com",
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]);
            if ($viewerRole) {
                $viewer->assignRole($viewerRole);
            }

            $allUsers = [$admin, $manager, $salesUsers[0], $salesUsers[1], $viewer];

            // Create some tags for this tenant
            $tags = [];
            $tagNames = [
                ['name' => 'VIP', 'color' => '#FF0000'],
                ['name' => 'Partner', 'color' => '#00FF00'],
                ['name' => 'Prospect', 'color' => '#0000FF'],
                ['name' => 'Urgent', 'color' => '#FF9900'],
                ['name' => 'Follow-up', 'color' => '#9900FF'],
            ];
            foreach ($tagNames as $tagData) {
                $tags[] = Tag::create([
                    'tenant_id' => $tenant->id,
                    'name' => $tagData['name'],
                    'color' => $tagData['color'],
                ]);
            }

            // Create 10 accounts per tenant (some with parent relationships)
            $accounts = [];
            $industries = ['Technology', 'Healthcare', 'Finance', 'Manufacturing', 'Retail', 'Education', 'Energy', 'Media', 'Real Estate', 'Consulting'];

            for ($i = 0; $i < 10; $i++) {
                $ownerIndex = array_rand($allUsers);
                $parentId = null;

                // Make some accounts children of earlier accounts
                if ($i > 3 && $i % 3 === 0 && !empty($accounts)) {
                    $parentId = $accounts[array_rand(array_slice($accounts, 0, 3))]->id;
                }

                $accounts[] = Account::create([
                    'tenant_id' => $tenant->id,
                    'name' => fake()->company(),
                    'industry' => $industries[$i],
                    'website' => fake()->url(),
                    'phone' => fake()->phoneNumber(),
                    'email' => fake()->companyEmail(),
                    'annual_revenue' => fake()->randomFloat(2, 100000, 10000000),
                    'employee_count' => fake()->numberBetween(10, 5000),
                    'description' => fake()->paragraph(),
                    'parent_id' => $parentId,
                    'owner_id' => $allUsers[$ownerIndex]->id,
                    'created_by' => $admin->id,
                ]);
            }

            // Create 20 contacts per tenant
            $contacts = [];
            $sources = ['website', 'referral', 'linkedin', 'trade_show', 'cold_call', 'email_campaign'];

            for ($i = 0; $i < 20; $i++) {
                $ownerIndex = array_rand($allUsers);

                $contact = Contact::create([
                    'tenant_id' => $tenant->id,
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->unique()->safeEmail(),
                    'phone' => fake()->phoneNumber(),
                    'mobile' => fake()->phoneNumber(),
                    'job_title' => fake()->jobTitle(),
                    'department' => fake()->randomElement(['Sales', 'Engineering', 'Marketing', 'HR', 'Finance', 'Operations']),
                    'description' => fake()->sentence(),
                    'linkedin_url' => 'https://linkedin.com/in/' . fake()->userName(),
                    'address_line_1' => fake()->streetAddress(),
                    'city' => fake()->city(),
                    'state' => fake()->state(),
                    'postal_code' => fake()->postcode(),
                    'country' => fake()->country(),
                    'source' => $sources[array_rand($sources)],
                    'owner_id' => $allUsers[$ownerIndex]->id,
                    'created_by' => $admin->id,
                ]);

                $contacts[] = $contact;

                // Assign random tags
                $randomTagIds = collect($tags)->random(rand(0, 3))->pluck('id')->toArray();
                if (!empty($randomTagIds)) {
                    $contact->tags()->attach($randomTagIds);
                }

                // Attach to a random account
                if (!empty($accounts) && rand(0, 1)) {
                    $randomAccount = $accounts[array_rand($accounts)];
                    $randomAccount->contacts()->attach($contact->id, [
                        'role' => fake()->randomElement(['decision_maker', 'stakeholder', 'influencer', 'user']),
                        'is_primary' => rand(0, 1),
                    ]);
                }
            }

            // Create 15 leads per tenant
            $statuses = ['new', 'contacted', 'qualified', 'converted', 'recycled'];
            $scores = ['hot', 'warm', 'cold'];

            for ($i = 0; $i < 15; $i++) {
                $ownerIndex = array_rand($allUsers);
                $status = $statuses[array_rand($statuses)];
                $isConverted = $status === 'converted';

                $lead = Lead::create([
                    'tenant_id' => $tenant->id,
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->unique()->safeEmail(),
                    'phone' => fake()->phoneNumber(),
                    'company_name' => fake()->company(),
                    'job_title' => fake()->jobTitle(),
                    'website' => fake()->url(),
                    'description' => fake()->sentence(),
                    'status' => $status,
                    'score' => $scores[array_rand($scores)],
                    'source' => $sources[array_rand($sources)],
                    'is_converted' => $isConverted,
                    'converted_at' => $isConverted ? now()->subDays(rand(1, 30)) : null,
                    'address_line_1' => fake()->streetAddress(),
                    'city' => fake()->city(),
                    'state' => fake()->state(),
                    'postal_code' => fake()->postcode(),
                    'country' => fake()->country(),
                    'owner_id' => $allUsers[$ownerIndex]->id,
                    'created_by' => $admin->id,
                ]);

                // Assign random tags
                $randomTagIds = collect($tags)->random(rand(0, 2))->pluck('id')->toArray();
                if (!empty($randomTagIds)) {
                    $lead->tags()->attach($randomTagIds);
                }
            }
        }
    }
}
