<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['individual', 'company']);
        
        return [
            'user_id' => User::factory(),
            'client_type' => $type,
            'first_name' => $type === 'individual' ? $this->faker->firstName() : null,
            'last_name' => $type === 'individual' ? $this->faker->lastName() : null,
            'company_name' => $type === 'company' ? $this->faker->company() : null,
            'company_document' => $type === 'company' ? $this->faker->numerify('B########') : null,
            'particular_document' => $type === 'individual' ? $this->faker->numerify('########A') : null,
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'active' => true,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'client_type' => 'company',
            'first_name' => null,
            'last_name' => null,
            'company_name' => $this->faker->company(),
            'company_document' => $this->faker->numerify('B########'),
            'particular_document' => null,
        ]);
    }

    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'client_type' => 'individual',
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'company_name' => null,
            'company_document' => null,
            'particular_document' => $this->faker->numerify('########A'),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
