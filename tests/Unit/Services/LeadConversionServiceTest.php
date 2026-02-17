<?php

namespace Tests\Unit\Services;

use App\Models\Account;
use App\Models\Lead;
use App\Models\Tag;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LeadConversionService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LeadConversionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LeadConversionService $service;
    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->service = new LeadConversionService();

        $this->tenant = Tenant::factory()->create(['is_active' => true]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);
        $this->user->assignRole('admin');

        Sanctum::actingAs($this->user);
    }

    public function test_converts_lead_to_contact_and_account(): void
    {
        $lead = Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john@leadtest.com',
            'phone' => '555-0001',
            'company_name' => 'Smith Industries',
            'job_title' => 'CEO',
            'website' => 'https://smith.com',
            'is_converted' => false,
        ]);

        $result = $this->service->convert($lead, ['create_account' => true]);

        // Verify contact was created
        $this->assertNotNull($result['contact']);
        $this->assertEquals('John', $result['contact']->first_name);
        $this->assertEquals('Smith', $result['contact']->last_name);
        $this->assertEquals('john@leadtest.com', $result['contact']->email);
        $this->assertEquals('CEO', $result['contact']->job_title);
        $this->assertEquals($this->tenant->id, $result['contact']->tenant_id);

        // Verify account was created
        $this->assertNotNull($result['account']);
        $this->assertEquals('Smith Industries', $result['account']->name);
        $this->assertEquals($this->tenant->id, $result['account']->tenant_id);

        // Verify contact is attached to account
        $this->assertTrue($result['account']->contacts->contains($result['contact']));

        // Verify lead is marked as converted
        $this->assertTrue($result['lead']->is_converted);
        $this->assertNotNull($result['lead']->converted_at);
        $this->assertEquals($result['contact']->id, $result['lead']->converted_contact_id);
        $this->assertEquals($result['account']->id, $result['lead']->converted_account_id);
        $this->assertEquals('converted', $result['lead']->status->value);
    }

    public function test_conversion_copies_tags(): void
    {
        $lead = Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'company_name' => 'Tagged Company',
            'is_converted' => false,
        ]);

        $tag1 = Tag::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Important',
            'color' => '#FF0000',
        ]);

        $tag2 = Tag::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Priority',
            'color' => '#00FF00',
        ]);

        $lead->tags()->attach([$tag1->id, $tag2->id]);

        $result = $this->service->convert($lead, ['create_account' => true]);

        // Verify tags were copied to the contact
        $contactTags = $result['contact']->tags()->pluck('tags.id')->toArray();
        $this->assertContains($tag1->id, $contactTags);
        $this->assertContains($tag2->id, $contactTags);
    }

    public function test_conversion_with_existing_account(): void
    {
        $existingAccount = Account::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Existing Account',
            'created_by' => $this->user->id,
        ]);

        $lead = Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'company_name' => 'Some Company',
            'is_converted' => false,
        ]);

        $result = $this->service->convert($lead, [
            'create_account' => false,
            'existing_account_id' => $existingAccount->id,
        ]);

        // Verify contact was created
        $this->assertNotNull($result['contact']);

        // Verify the existing account was used (not a new one)
        $this->assertEquals($existingAccount->id, $result['account']->id);
        $this->assertEquals('Existing Account', $result['account']->name);

        // Verify contact is attached to existing account
        $this->assertTrue($result['account']->contacts->contains($result['contact']));

        // Verify lead is converted and linked to existing account
        $this->assertTrue($result['lead']->is_converted);
        $this->assertEquals($existingAccount->id, $result['lead']->converted_account_id);
    }
}
