<?php

namespace Database\Factories;

use App\Models\Machinery;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Machinery>
 */
class MachineryFactory extends Factory
{
    protected $model = Machinery::class;

    public function definition(): array
    {
        $types = ['tractor', 'pulverizador', 'vendimiadora', 'remolque', 'despalilladora'];

        return [
            'name'             => fake()->words(2, true) . ' ' . fake()->randomElement($types),
            'type'             => fake()->randomElement($types),
            'brand'            => fake()->company(),
            'model'            => strtoupper(fake()->bothify('??###')),
            'serial_number'    => fake()->optional()->bothify('SN-######'),
            'year'             => fake()->numberBetween(2000, 2024),
            'purchase_date'    => fake()->optional()->date(),
            'purchase_price'   => fake()->optional()->randomFloat(2, 5000, 150000),
            'current_value'    => fake()->optional()->randomFloat(2, 1000, 100000),
            'roma_registration'=> fake()->optional()->bothify('ROMA-####'),
            'is_rented'        => false,
            'capacity'         => fake()->optional()->numerify('### L'),
            'last_revision_date' => fake()->optional()->date(),
            'notes'            => fake()->optional()->sentence(),
            'viticulturist_id' => User::factory(),
            'active'           => true,
        ];
    }
}
