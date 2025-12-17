<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->numberBetween(2020, now()->year);
        
        return [
            'name' => "Campaña {$year}",
            'year' => $year,
            'viticulturist_id' => User::factory(),
            'start_date' => now()->setYear($year)->startOfYear(),
            'end_date' => now()->setYear($year)->endOfYear(),
            'active' => false,
            'description' => fake()->sentence(),
        ];
    }

    /**
     * Indicate that the campaign belongs to a specific viticulturist.
     */
    public function forViticulturist(User $viticulturist): static
    {
        return $this->state(fn (array $attributes) => [
            'viticulturist_id' => $viticulturist->id,
        ]);
    }

    /**
     * Indicate that the campaign is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
        ]);
    }
}
