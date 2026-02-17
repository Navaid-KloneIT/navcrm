<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\PipelineStage;
use App\Models\PriceBook;
use App\Models\Product;
use App\Models\Quote;
use App\Models\SalesTarget;
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

            // =====================================================
            // SFA Demo Data
            // =====================================================

            // Create pipeline stages
            $stagesData = [
                ['name' => 'Prospecting', 'position' => 1, 'probability' => 10, 'is_won' => false, 'is_lost' => false, 'color' => '#6B7280'],
                ['name' => 'Qualification', 'position' => 2, 'probability' => 25, 'is_won' => false, 'is_lost' => false, 'color' => '#3B82F6'],
                ['name' => 'Proposal', 'position' => 3, 'probability' => 50, 'is_won' => false, 'is_lost' => false, 'color' => '#F59E0B'],
                ['name' => 'Negotiation', 'position' => 4, 'probability' => 75, 'is_won' => false, 'is_lost' => false, 'color' => '#8B5CF6'],
                ['name' => 'Closed Won', 'position' => 5, 'probability' => 100, 'is_won' => true, 'is_lost' => false, 'color' => '#10B981'],
                ['name' => 'Closed Lost', 'position' => 6, 'probability' => 0, 'is_won' => false, 'is_lost' => true, 'color' => '#EF4444'],
            ];

            $stages = [];
            foreach ($stagesData as $stageData) {
                $stages[] = PipelineStage::create(array_merge($stageData, [
                    'tenant_id' => $tenant->id,
                ]));
            }

            // Create products
            $productsData = [
                ['name' => 'CRM Basic License', 'sku' => 'CRM-BASIC', 'unit_price' => 29.99, 'cost_price' => 5.00, 'unit' => 'month', 'category' => 'Software'],
                ['name' => 'CRM Pro License', 'sku' => 'CRM-PRO', 'unit_price' => 79.99, 'cost_price' => 12.00, 'unit' => 'month', 'category' => 'Software'],
                ['name' => 'CRM Enterprise License', 'sku' => 'CRM-ENT', 'unit_price' => 199.99, 'cost_price' => 30.00, 'unit' => 'month', 'category' => 'Software'],
                ['name' => 'Implementation Service', 'sku' => 'SVC-IMPL', 'unit_price' => 150.00, 'cost_price' => 75.00, 'unit' => 'hour', 'category' => 'Services'],
                ['name' => 'Training Session', 'sku' => 'SVC-TRAIN', 'unit_price' => 500.00, 'cost_price' => 200.00, 'unit' => 'each', 'category' => 'Services'],
                ['name' => 'Custom Integration', 'sku' => 'SVC-INTEG', 'unit_price' => 2500.00, 'cost_price' => 1000.00, 'unit' => 'each', 'category' => 'Services'],
                ['name' => 'Annual Support Plan', 'sku' => 'SUP-ANN', 'unit_price' => 1200.00, 'cost_price' => 400.00, 'unit' => 'year', 'category' => 'Support'],
                ['name' => 'Data Migration', 'sku' => 'SVC-MIGR', 'unit_price' => 3000.00, 'cost_price' => 1500.00, 'unit' => 'each', 'category' => 'Services'],
            ];

            $products = [];
            foreach ($productsData as $productData) {
                $products[] = Product::create(array_merge($productData, [
                    'tenant_id' => $tenant->id,
                    'currency' => 'USD',
                    'is_active' => true,
                    'description' => fake()->sentence(),
                ]));
            }

            // Create default price book
            $priceBook = PriceBook::create([
                'tenant_id' => $tenant->id,
                'name' => 'Standard Price Book',
                'description' => 'Default pricing for all customers',
                'is_default' => true,
                'is_active' => true,
            ]);

            foreach ($products as $product) {
                $priceBook->entries()->create([
                    'product_id' => $product->id,
                    'unit_price' => $product->unit_price,
                    'min_quantity' => 1,
                    'is_active' => true,
                ]);
            }

            // Create opportunities
            $dealNames = [
                'Enterprise Platform Upgrade', 'Annual License Renewal', 'New Department Rollout',
                'Data Migration Project', 'Custom Integration Suite', 'Training Program',
                'Premium Support Contract', 'Pilot Program', 'Multi-Year Agreement',
                'Regional Expansion', 'API Integration', 'Cloud Migration',
            ];

            $opportunities = [];
            for ($i = 0; $i < 12; $i++) {
                $stageIndex = $i < 3 ? 0 : ($i < 5 ? 1 : ($i < 7 ? 2 : ($i < 9 ? 3 : ($i < 11 ? 4 : 5))));
                $stage = $stages[$stageIndex];
                $ownerIndex = array_rand($allUsers);
                $amount = fake()->randomFloat(2, 5000, 150000);

                $opportunity = Opportunity::create([
                    'tenant_id' => $tenant->id,
                    'name' => $dealNames[$i],
                    'amount' => $amount,
                    'currency' => 'USD',
                    'close_date' => now()->addDays(rand(-30, 90)),
                    'probability' => $stage->probability,
                    'pipeline_stage_id' => $stage->id,
                    'account_id' => $accounts[array_rand($accounts)]->id,
                    'contact_id' => $contacts[array_rand($contacts)]->id,
                    'description' => fake()->paragraph(),
                    'next_steps' => fake()->sentence(),
                    'competitor' => fake()->randomElement(['Competitor A', 'Competitor B', 'Competitor C', null]),
                    'source' => fake()->randomElement(['web', 'referral', 'partner', 'outbound', 'inbound']),
                    'owner_id' => $allUsers[$ownerIndex]->id,
                    'created_by' => $admin->id,
                    'won_at' => $stage->is_won ? now()->subDays(rand(1, 30)) : null,
                    'lost_at' => $stage->is_lost ? now()->subDays(rand(1, 30)) : null,
                    'lost_reason' => $stage->is_lost ? fake()->sentence() : null,
                ]);

                $opportunities[] = $opportunity;

                // Add team members to some opportunities
                if ($i % 3 === 0) {
                    $opportunity->teamMembers()->attach($allUsers[$ownerIndex]->id, [
                        'role' => 'owner',
                        'split_percentage' => 70,
                    ]);
                    $otherUser = $salesUsers[array_rand($salesUsers)];
                    if ($otherUser->id !== $allUsers[$ownerIndex]->id) {
                        $opportunity->teamMembers()->attach($otherUser->id, [
                            'role' => 'support',
                            'split_percentage' => 30,
                        ]);
                    }
                }
            }

            // Create some quotes
            $quoteNumber = 1001;
            for ($i = 0; $i < 5; $i++) {
                $opp = $opportunities[$i];
                $statuses = ['draft', 'sent', 'accepted', 'rejected'];

                $quote = Quote::create([
                    'tenant_id' => $tenant->id,
                    'quote_number' => 'Q-' . str_pad($quoteNumber++, 5, '0', STR_PAD_LEFT),
                    'opportunity_id' => $opp->id,
                    'account_id' => $opp->account_id,
                    'contact_id' => $opp->contact_id,
                    'status' => $statuses[array_rand($statuses)],
                    'valid_until' => now()->addDays(30),
                    'discount_type' => 'percentage',
                    'discount_value' => fake()->randomElement([0, 5, 10, 15]),
                    'tax_rate' => 8.25,
                    'notes' => fake()->sentence(),
                    'terms' => 'Net 30. All prices in USD.',
                    'prepared_by' => $admin->id,
                ]);

                // Add line items
                $numItems = rand(2, 4);
                $subtotal = 0;
                for ($j = 0; $j < $numItems; $j++) {
                    $product = $products[array_rand($products)];
                    $qty = rand(1, 20);
                    $discount = fake()->randomElement([0, 5, 10]);
                    $lineSubtotal = round($qty * (float) $product->unit_price * (1 - $discount / 100), 2);
                    $subtotal += $lineSubtotal;

                    $quote->lineItems()->create([
                        'product_id' => $product->id,
                        'description' => $product->name,
                        'quantity' => $qty,
                        'unit_price' => $product->unit_price,
                        'discount_percent' => $discount,
                        'subtotal' => $lineSubtotal,
                        'sort_order' => $j,
                    ]);
                }

                $discountAmount = $quote->discount_type === 'percentage'
                    ? round($subtotal * ($quote->discount_value / 100), 2)
                    : round((float) $quote->discount_value, 2);
                $afterDiscount = $subtotal - $discountAmount;
                $taxAmount = round($afterDiscount * ($quote->tax_rate / 100), 2);

                $quote->update([
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'total' => $afterDiscount + $taxAmount,
                ]);
            }

            // Create sales targets
            $currentQuarterStart = now()->startOfQuarter();
            $currentQuarterEnd = now()->endOfQuarter();

            foreach ($salesUsers as $salesUser) {
                SalesTarget::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $salesUser->id,
                    'period_type' => 'quarterly',
                    'period_start' => $currentQuarterStart,
                    'period_end' => $currentQuarterEnd,
                    'target_amount' => fake()->randomFloat(2, 50000, 200000),
                    'currency' => 'USD',
                ]);
            }

            // Team target
            SalesTarget::create([
                'tenant_id' => $tenant->id,
                'user_id' => null,
                'period_type' => 'quarterly',
                'period_start' => $currentQuarterStart,
                'period_end' => $currentQuarterEnd,
                'target_amount' => 500000,
                'currency' => 'USD',
                'category' => 'Team',
            ]);
        }
    }
}
