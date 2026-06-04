<?php

namespace Database\Factories;

use App\Models\AgriInsurance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgriInsuranceFactory extends Factory
{
    protected $model = AgriInsurance::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-2 years', 'now');
        $end = (clone $start)->modify('+1 year');

        return [
            'viticulturist_id' => null,
            'policy_number' => $this->faker->optional()->bothify('POL-########'),
            'insurance_company' => $this->faker->company(),
            'coverage_type' => $this->faker->randomElement(array_keys(AgriInsurance::COVERAGE_TYPES)),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'insured_amount' => $this->faker->optional()->randomFloat(2, 5000, 100000),
            'premium' => $this->faker->optional()->randomFloat(2, 100, 3000),
            'subsidy_amount' => $this->faker->optional()->randomFloat(2, 0, 500),
            'status' => $this->faker->randomElement(array_keys(AgriInsurance::STATUSES)),
            'agent_name' => $this->faker->optional()->name(),
            'agent_phone' => $this->faker->optional()->phoneNumber(),
            'covered_plots' => null,
            'notes' => null,
        ];
    }
}
