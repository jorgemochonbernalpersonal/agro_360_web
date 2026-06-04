<?php

namespace Database\Factories;

use App\Models\Subcontracting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubcontractingFactory extends Factory
{
    protected $model = Subcontracting::class;

    public function definition(): array
    {
        return [
            'viticulturist_id' => null,
            'plot_id' => null,
            'campaign_id' => null,
            'service_type' => $this->faker->randomElement(array_keys(Subcontracting::SERVICE_TYPES)),
            'company_name' => $this->faker->company(),
            'contact_person' => $this->faker->optional()->name(),
            'contact_phone' => $this->faker->optional()->phoneNumber(),
            'service_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'service_end_date' => null,
            'amount' => $this->faker->optional()->randomFloat(2, 100, 5000),
            'invoiced' => $this->faker->boolean(30),
            'invoice_number' => $this->faker->optional()->bothify('FAC-####'),
            'description' => $this->faker->optional()->sentence(),
            'notes' => null,
        ];
    }
}
