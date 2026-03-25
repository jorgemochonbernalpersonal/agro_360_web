<?php

namespace Database\Factories;

use App\Models\Plot;
use App\Models\PlotPlanting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlotPlanting>
 */
class PlotPlantingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'plot_id'      => Plot::factory(),
            'status'       => 'active',
            'area_planted' => $this->faker->randomFloat(3, 0.1, 5.0),
        ];
    }
}
