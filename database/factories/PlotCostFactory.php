<?php

namespace Database\Factories;

use App\Models\PlotCost;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlotCostFactory extends Factory
{
    protected $model = PlotCost::class;

    public function definition(): array
    {
        return [
            'viticulturist_id'  => null,
            'plot_id'           => null,
            'campaign_id'       => null,
            'category'          => $this->faker->randomElement(array_keys(PlotCost::CATEGORIES)),
            'description'       => $this->faker->sentence(4),
            'amount'            => $this->faker->randomFloat(2, 50, 2000),
            'cost_date'         => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'supplier'          => $this->faker->optional()->company(),
            'invoice_reference' => $this->faker->optional()->bothify('FAC-####'),
            'notes'             => $this->faker->optional()->sentence(),
        ];
    }
}
