<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->company(),
            'industry' => fake()->randomElement(['Technology', 'Healthcare', 'Finance', 'Manufacturing', 'Retail', 'Education', 'Energy', 'Media']),
            'website' => fake()->url(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'annual_revenue' => fake()->randomFloat(2, 100000, 10000000),
            'employee_count' => fake()->numberBetween(10, 5000),
            'tax_id' => fake()->optional(0.5)->numerify('##-#######'),
            'description' => fake()->paragraph(),
            'parent_id' => null,
            'owner_id' => null,
            'created_by' => null,
        ];
    }

    public function withOwner(User $owner): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_id' => $owner->id,
            'tenant_id' => $owner->tenant_id,
        ]);
    }

    public function withCreator(User $creator): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $creator->id,
        ]);
    }

    public function withParent(Account $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'tenant_id' => $parent->tenant_id,
        ]);
    }
}
