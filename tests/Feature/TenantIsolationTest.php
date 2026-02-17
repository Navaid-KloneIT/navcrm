<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected User $userA;
    protected User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->tenantA = Tenant::factory()->create(['is_active' => true]);
        $this->tenantB = Tenant::factory()->create(['is_active' => true]);

        $this->userA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'is_active' => true,
        ]);
        $this->userA->assignRole('admin');

        $this->userB = User::factory()->create([
            'tenant_id' => $this->tenantB->id,
            'is_active' => true,
        ]);
        $this->userB->assignRole('admin');
    }

    public function test_user_cannot_access_other_tenant_contacts(): void
    {
        // Create contacts for tenant B
        $contactB = Contact::factory()->create([
            'tenant_id' => $this->tenantB->id,
            'created_by' => $this->userB->id,
        ]);

        // Create contacts for tenant A
        $contactA = Contact::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'created_by' => $this->userA->id,
        ]);

        // Act as user A
        Sanctum::actingAs($this->userA);

        // Listing should only show tenant A contacts
        $response = $this->getJson('/api/contacts');
        $response->assertStatus(200);

        $contactIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($contactA->id, $contactIds);
        $this->assertNotContains($contactB->id, $contactIds);

        // Direct access to tenant B contact should return 404 (due to tenant scope)
        $response = $this->getJson("/api/contacts/{$contactB->id}");
        $response->assertStatus(404);
    }

    public function test_user_cannot_access_other_tenant_accounts(): void
    {
        $accountB = Account::factory()->create([
            'tenant_id' => $this->tenantB->id,
            'created_by' => $this->userB->id,
        ]);

        $accountA = Account::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'created_by' => $this->userA->id,
        ]);

        Sanctum::actingAs($this->userA);

        $response = $this->getJson('/api/accounts');
        $response->assertStatus(200);

        $accountIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($accountA->id, $accountIds);
        $this->assertNotContains($accountB->id, $accountIds);

        $response = $this->getJson("/api/accounts/{$accountB->id}");
        $response->assertStatus(404);
    }

    public function test_user_cannot_access_other_tenant_leads(): void
    {
        $leadB = Lead::factory()->create([
            'tenant_id' => $this->tenantB->id,
            'created_by' => $this->userB->id,
        ]);

        $leadA = Lead::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'created_by' => $this->userA->id,
        ]);

        Sanctum::actingAs($this->userA);

        $response = $this->getJson('/api/leads');
        $response->assertStatus(200);

        $leadIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($leadA->id, $leadIds);
        $this->assertNotContains($leadB->id, $leadIds);

        $response = $this->getJson("/api/leads/{$leadB->id}");
        $response->assertStatus(404);
    }
}
