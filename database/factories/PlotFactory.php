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
        // Obtener los primeros registros existentes (creados por seeders)
        // Si no existen, usar valores por defecto que se crearán en el test
        $autonomousCommunity = \App\Models\AutonomousCommunity::first();
        $province = \App\Models\Province::first();
        $municipality = \App\Models\Municipality::first();

        return [
            'name' => fake()->words(3, true) . ' Parcela',
            'description' => fake()->sentence(),
            'winery_id' => User::factory(),
            'viticulturist_id' => User::factory(),
            'autonomous_community_id' => $autonomousCommunity?->id ?? 1,
            'province_id' => $province?->id ?? 1,
            'municipality_id' => $municipality?->id ?? 1,
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
