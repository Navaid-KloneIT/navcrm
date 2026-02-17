<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountCrudTest extends TestCase
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

    public function test_can_list_accounts(): void
    {
        Sanctum::actingAs($this->user);

        Account::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/accounts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta',
                'links',
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_can_create_account(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/accounts', [
            'name' => 'Test Company Inc.',
            'industry' => 'Technology',
            'website' => 'https://testcompany.com',
            'phone' => '555-9876',
            'email' => 'info@testcompany.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('account.name', 'Test Company Inc.')
            ->assertJsonPath('account.industry', 'Technology');

        $this->assertDatabaseHas('accounts', [
            'name' => 'Test Company Inc.',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_can_show_account(): void
    {
        Sanctum::actingAs($this->user);

        $account = Account::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/accounts/{$account->id}");

        $response->assertStatus(200)
            ->assertJsonPath('account.id', $account->id)
            ->assertJsonPath('account.name', $account->name);
    }

    public function test_can_update_account(): void
    {
        Sanctum::actingAs($this->user);

        $account = Account::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->putJson("/api/accounts/{$account->id}", [
            'name' => 'Updated Company Name',
            'industry' => 'Healthcare',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('account.name', 'Updated Company Name')
            ->assertJsonPath('account.industry', 'Healthcare');

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'name' => 'Updated Company Name',
        ]);
    }

    public function test_can_delete_account(): void
    {
        Sanctum::actingAs($this->user);

        $account = Account::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/accounts/{$account->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('accounts', [
            'id' => $account->id,
        ]);
    }

    public function test_accounts_are_tenant_scoped(): void
    {
        Sanctum::actingAs($this->user);

        $otherAccount = Account::factory()->create([
            'tenant_id' => $this->otherTenant->id,
            'created_by' => $this->otherUser->id,
        ]);

        $ourAccount = Account::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/accounts');

        $response->assertStatus(200);

        $accountIds = collect($response->json('data'))->pluck('id')->toArray();

        $this->assertContains($ourAccount->id, $accountIds);
        $this->assertNotContains($otherAccount->id, $accountIds);
    }
}
