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
            'plot_planting_id' => null, // Se debe proporcionar explícitamente o crear manualmente
            'container_id' => null, // Se debe proporcionar explícitamente o crear manualmente
            'harvest_start_date' => now(),
            'harvest_end_date' => now()->addDays(1),
            'total_weight' => $totalWeight,
            'brix_degree' => $this->faker->optional()->randomFloat(2, 10, 25),
            'price_per_kg' => $this->faker->optional()->randomFloat(4, 0.5, 5.0),
            'total_value' => null,
            'status' => 'active',
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
            if ($harvest->container_id) {
                $container = \App\Models\Container::find($harvest->container_id);
                if ($container) {
                    \App\Models\ContainerCurrentState::updateOrCreate(
                        ['container_id' => $container->id],
                        [
                            'harvest_id' => $harvest->id,
                            'current_quantity' => $harvest->total_weight,
                            'available_qty' => $harvest->total_weight,
                            'reserved_qty' => 0,
                            'sold_qty' => 0,
                            'has_subproducts' => false,
                            'last_movement_at' => now(),
                            'last_movement_by' => $harvest->activity->viticulturist_id,
                        ]
                    );
                }
            }
        });
    }
}
