<?php

namespace Tests\Feature\Lead;

use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LeadConversionService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LeadCrudTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Tenant $otherTenant;
    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->tenant = Tenant::factory()->create(['is_active' => true]);
        $this->otherTenant = Tenant::factory()->create(['is_active' => true]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);
        $this->user->assignRole('admin');

        $this->otherUser = User::factory()->create([
            'tenant_id' => $this->otherTenant->id,
            'is_active' => true,
        ]);
        $this->otherUser->assignRole('admin');
    }

    public function test_can_list_leads(): void
    {
        Sanctum::actingAs($this->user);

        Lead::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/leads');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta',
                'links',
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_can_create_lead(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/leads', [
            'first_name' => 'Lead',
            'last_name' => 'Person',
            'email' => 'lead@example.com',
            'company_name' => 'Lead Company',
            'status' => 'new',
            'score' => 'warm',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('lead.first_name', 'Lead')
            ->assertJsonPath('lead.last_name', 'Person')
            ->assertJsonPath('lead.status', 'new')
            ->assertJsonPath('lead.score', 'warm');

        $this->assertDatabaseHas('leads', [
            'first_name' => 'Lead',
            'last_name' => 'Person',
            'email' => 'lead@example.com',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_can_show_lead(): void
    {
        Sanctum::actingAs($this->user);

        $lead = Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/leads/{$lead->id}");

        $response->assertStatus(200)
            ->assertJsonPath('lead.id', $lead->id)
            ->assertJsonPath('lead.first_name', $lead->first_name);
    }

    public function test_can_update_lead(): void
    {
        Sanctum::actingAs($this->user);

        $lead = Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->putJson("/api/leads/{$lead->id}", [
            'first_name' => 'Updated',
            'last_name' => 'Lead',
            'status' => 'qualified',
            'score' => 'hot',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('lead.first_name', 'Updated')
            ->assertJsonPath('lead.last_name', 'Lead')
            ->assertJsonPath('lead.status', 'qualified')
            ->assertJsonPath('lead.score', 'hot');
    }

    public function test_can_delete_lead(): void
    {
        Sanctum::actingAs($this->user);

        $lead = Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/leads/{$lead->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('leads', [
            'id' => $lead->id,
        ]);
    }

    public function test_leads_are_tenant_scoped(): void
    {
        Sanctum::actingAs($this->user);

        $otherLead = Lead::factory()->create([
            'tenant_id' => $this->otherTenant->id,
            'created_by' => $this->otherUser->id,
        ]);

        $ourLead = Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/leads');

        $response->assertStatus(200);

        $leadIds = collect($response->json('data'))->pluck('id')->toArray();

        $this->assertContains($ourLead->id, $leadIds);
        $this->assertNotContains($otherLead->id, $leadIds);
    }

    public function test_can_convert_lead(): void
    {
        Sanctum::actingAs($this->user);

        $lead = Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'company_name' => 'Conversion Company',
            'is_converted' => false,
        ]);

        $response = $this->postJson("/api/leads/{$lead->id}/convert", [
            'create_account' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'contact' => ['id', 'first_name', 'last_name'],
                'account' => ['id', 'name'],
                'lead' => ['id', 'is_converted'],
            ]);

        $this->assertTrue($response->json('lead.is_converted'));

        $this->assertDatabaseHas('contacts', [
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertDatabaseHas('accounts', [
            'name' => 'Conversion Company',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'is_converted' => true,
        ]);
    }
}
