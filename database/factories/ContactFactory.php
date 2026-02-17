<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'mobile' => fake()->phoneNumber(),
            'job_title' => fake()->jobTitle(),
            'department' => fake()->randomElement(['Sales', 'Engineering', 'Marketing', 'HR', 'Finance', 'Operations']),
            'description' => fake()->sentence(),
            'linkedin_url' => 'https://linkedin.com/in/' . fake()->userName(),
            'twitter_handle' => '@' . fake()->userName(),
            'facebook_url' => 'https://facebook.com/' . fake()->userName(),
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => fake()->optional(0.3)->secondaryAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => fake()->country(),
            'source' => fake()->randomElement(['website', 'referral', 'linkedin', 'trade_show', 'cold_call', 'email_campaign']),
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
}
