<?php

namespace Tests\Feature\Contact;

use App\Models\Contact;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ContactCrudTest extends TestCase
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

    public function test_can_list_contacts(): void
    {
        Sanctum::actingAs($this->user);

        Contact::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/contacts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta',
                'links',
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_can_create_contact(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/contacts', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'phone' => '555-1234',
            'job_title' => 'Manager',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('contact.first_name', 'Jane')
            ->assertJsonPath('contact.last_name', 'Doe')
            ->assertJsonPath('contact.email', 'jane@example.com');

        $this->assertDatabaseHas('contacts', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_can_show_contact(): void
    {
        Sanctum::actingAs($this->user);

        $contact = Contact::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/contacts/{$contact->id}");

        $response->assertStatus(200)
            ->assertJsonPath('contact.id', $contact->id)
            ->assertJsonPath('contact.first_name', $contact->first_name);
    }

    public function test_can_update_contact(): void
    {
        Sanctum::actingAs($this->user);

        $contact = Contact::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->putJson("/api/contacts/{$contact->id}", [
            'first_name' => 'Updated',
            'last_name' => 'Name',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('contact.first_name', 'Updated')
            ->assertJsonPath('contact.last_name', 'Name');

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
        ]);
    }

    public function test_can_delete_contact(): void
    {
        Sanctum::actingAs($this->user);

        $contact = Contact::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/contacts/{$contact->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_contacts_are_tenant_scoped(): void
    {
        Sanctum::actingAs($this->user);

        // Create contact for the other tenant
        $otherContact = Contact::factory()->create([
            'tenant_id' => $this->otherTenant->id,
            'created_by' => $this->otherUser->id,
        ]);

        // Create contact for our tenant
        $ourContact = Contact::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/contacts');

        $response->assertStatus(200);

        $contactIds = collect($response->json('data'))->pluck('id')->toArray();

        $this->assertContains($ourContact->id, $contactIds);
        $this->assertNotContains($otherContact->id, $contactIds);
    }
}
