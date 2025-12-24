<?php

namespace Database\Factories;

use App\Models\Container;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContainerFactory extends Factory
{
    protected $model = Container::class;

    public function definition(): array
    {
        $capacity = $this->faker->randomFloat(2, 500, 5000);
        $usedCapacity = $this->faker->randomFloat(2, 0, $capacity);

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->randomElement(['Cuba', 'Depósito', 'Contenedor', 'Tanque', 'Silo']) . ' ' . $this->faker->numberBetween(1, 100),
            'description' => $this->faker->optional()->sentence(),
            'serial_number' => 'CONT-' . $this->faker->unique()->numberBetween(1000, 9999),
            'quantity' => $this->faker->numberBetween(1, 10),
            'capacity' => $capacity,
            'used_capacity' => $usedCapacity,
            'purchase_date' => $this->faker->optional()->dateTimeBetween('-2 years', 'now'),
            'next_maintenance_date' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
            'archived' => false,
            'unit_of_measurement_id' => 1, // kg por defecto
            'type_id' => 1, // Tipo por defecto
            'material_id' => 1, // Material por defecto
        ];
    }

    /**
     * Indicate that the container is empty
     */
    public function empty(): static
    {
        return $this->state(fn (array $attributes) => [
            'used_capacity' => 0,
        ]);
    }

    /**
     * Indicate that the container is full
     */
    public function full(): static
    {
        return $this->state(fn (array $attributes) => [
            'used_capacity' => $attributes['capacity'] ?? $this->faker->randomFloat(2, 500, 5000),
        ]);
    }

    /**
     * Indicate that the container is archived
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'archived' => true,
        ]);
    }
}

