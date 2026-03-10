<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        $startsAt = now()->subDays(fake()->numberBetween(1, 30));

        return [
            'user_id'    => User::factory(),
            'plan_type'  => fake()->randomElement(['monthly', 'yearly']),
            'amount'     => fake()->randomElement([9.99, 19.99, 99.99]),
            'status'     => 'active',
            'starts_at'  => $startsAt,
            'ends_at'    => $startsAt->copy()->addDays(30),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'  => 'active',
            'ends_at' => now()->addDays(30),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'  => 'expired',
            'ends_at' => now()->subDay(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}
