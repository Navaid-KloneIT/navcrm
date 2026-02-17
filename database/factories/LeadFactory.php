<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'company_name' => fake()->company(),
            'job_title' => fake()->jobTitle(),
            'website' => fake()->url(),
            'description' => fake()->sentence(),
            'status' => fake()->randomElement(['new', 'contacted', 'qualified']),
            'score' => fake()->randomElement(['hot', 'warm', 'cold']),
            'source' => fake()->randomElement(['website', 'referral', 'linkedin', 'trade_show', 'cold_call', 'email_campaign']),
            'is_converted' => false,
            'converted_at' => null,
            'converted_contact_id' => null,
            'converted_account_id' => null,
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => fake()->optional(0.3)->secondaryAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => fake()->country(),
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

    public function converted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'converted',
            'is_converted' => true,
            'converted_at' => now(),
        ]);
    }

    public function hot(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => 'hot',
        ]);
    }

    public function cold(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => 'cold',
        ]);
    }
}
