<?php

namespace Database\Factories;

use App\Models\Plot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plot>
 */
class PlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $autonomousCommunity = \App\Models\AutonomousCommunity::firstOrCreate(
            ['code' => 'TEST'],
            ['name' => 'Comunidad Test'],
        );
        $province = \App\Models\Province::firstOrCreate(
            ['code' => 'TEST'],
            ['name' => 'Provincia Test', 'autonomous_community_id' => $autonomousCommunity->id],
        );
        $municipality = \App\Models\Municipality::firstOrCreate(
            ['code' => 'TEST'],
            ['name' => 'Municipio Test', 'province_id' => $province->id],
        );

        return [
            'name' => fake()->words(3, true) . ' Parcela',
            'description' => fake()->sentence(),
            'viticulturist_id' => User::factory(),
            'autonomous_community_id' => $autonomousCommunity->id,
            'province_id' => $province->id,
            'municipality_id' => $municipality->id,
            'area' => fake()->randomFloat(3, 0.1, 100),
            'active' => true,
        ];
    }

    /**
     * Indicate that the plot belongs to a specific viticulturist.
     */
    public function forViticulturist(User $viticulturist): static
    {
        return $this->state(fn (array $attributes) => [
            'viticulturist_id' => $viticulturist->id,
        ]);
    }
}
