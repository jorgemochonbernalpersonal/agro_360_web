<?php

namespace Database\Factories;

use App\Models\Harvest;
use App\Models\AgriculturalActivity;
use App\Models\PlotPlanting;
use App\Models\HarvestContainer;
use Illuminate\Database\Eloquent\Factories\Factory;

class HarvestFactory extends Factory
{
    protected $model = Harvest::class;

    public function definition(): array
    {
        $totalWeight = $this->faker->randomFloat(2, 100, 5000);
        
        return [
            'activity_id' => AgriculturalActivity::factory(),
            'plot_planting_id' => PlotPlanting::factory(),
            'container_id' => HarvestContainer::factory(),
            'harvest_start_date' => now(),
            'harvest_end_date' => now()->addDays(1),
            'total_weight' => $totalWeight,
            'unit' => 'kg',
            'quality_rating' => $this->faker->optional()->numberBetween(1, 10),
            'brix_degree' => $this->faker->optional()->randomFloat(1, 10, 25),
            'observations' => $this->faker->optional()->sentence(),
            'weather_conditions' => $this->faker->optional()->word(),
            'estimated_value' => $this->faker->optional()->randomFloat(2, 100, 5000),
            'total_value' => null,
        ];
    }

    public function withAvailableStock(): static
    {
        return $this->afterCreating(function (Harvest $harvest) {
            // Crear stock inicial disponible
            \App\Models\HarvestStock::create([
                'harvest_id' => $harvest->id,
                'container_id' => $harvest->container_id,
                'user_id' => $harvest->activity->viticulturist_id,
                'movement_type' => 'initial',
                'quantity_change' => $harvest->total_weight,
                'quantity_after' => $harvest->total_weight,
                'available_qty' => $harvest->total_weight,
                'reserved_qty' => 0,
                'sold_qty' => 0,
                'gifted_qty' => 0,
                'lost_qty' => 0,
                'notes' => 'Stock inicial de cosecha',
            ]);

            // Crear/actualizar estado del contenedor
            \App\Models\ContainerState::updateOrCreate(
                ['container_id' => $harvest->container_id],
                [
                    'total_quantity' => $harvest->total_weight,
                    'available_qty' => $harvest->total_weight,
                    'reserved_qty' => 0,
                    'sold_qty' => 0,
                    'gifted_qty' => 0,
                    'lost_qty' => 0,
                    'unit' => 'kg',
                    'last_movement_at' => now(),
                    'last_movement_by' => $harvest->activity->viticulturist_id,
                ]
            );
        });
    }
}
