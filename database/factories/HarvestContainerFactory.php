<?php

namespace Database\Factories;

use App\Models\HarvestContainer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HarvestContainerFactory extends Factory
{
    protected $model = HarvestContainer::class;

    public function definition(): array
    {
        return [
            'harvest_id' => null, // Will be set by relationship
            'container_type' => $this->faker->randomElement(['caja', 'pallet', 'contenedor', 'saco', 'cuba', 'other']),
            'container_number' => $this->faker->numberBetween(1, 100),
            'quantity' => $this->faker->numberBetween(1, 50),
            'weight' => $this->faker->randomFloat(2, 100, 5000),
            'weight_per_unit' => null,
            'location' => $this->faker->optional()->word(),
            'status' => $this->faker->randomElement(['filled', 'in_transit', 'delivered', 'stored', 'empty']),
            'filled_date' => now(),
            'delivery_date' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
