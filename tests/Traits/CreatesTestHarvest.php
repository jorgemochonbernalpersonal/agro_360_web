<?php

namespace Tests\Traits;

use App\Models\User;
use App\Models\Harvest;
use App\Models\Container;
use App\Models\HarvestStock;
use App\Models\ContainerCurrentState;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;

trait CreatesTestHarvest
{
    protected function createHarvestWithStock(User $user, float $weight = 5000): Harvest
    {
        // Create campaign
        $campaign = Campaign::create([
            'user_id' => $user->id,
            'viticulturist_id' => $user->id,
            'name' => 'Temporada Test',
            'year' => now()->year,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
        ]);

        // Create plot
        $plot = \App\Models\Plot::create([
            'viticulturist_id' => $user->id,
            'name' => 'Parcela Test',
            'reference' => 'TEST-' . rand(100, 999),
            'area' => 10.5,
            'active' => true,
        ]);
        
        // Create planting for the plot
        $grapeVariety = \App\Models\GrapeVariety::firstOrCreate(
            ['code' => 'TEMP'],
            ['name' => 'Tempranillo', 'color' => 'red']
        );
        $planting = \App\Models\PlotPlanting::create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $grapeVariety->id,
            'area_planted' => $plot->area * 0.8,
            'planting_year' => now()->year - 5,
            'status' => 'active',
        ]);

        // Create activity
        $activity = AgriculturalActivity::create([
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        // Create container
        $container = Container::create([
            'user_id' => $user->id,
            'name' => 'Cuba Test',
            'serial_number' => '1',
            'quantity' => 10,
            'capacity' => $weight * 1.2, // Capacidad 20% mayor que el peso
            'used_capacity' => 0, // Inicialmente vacío
            'purchase_date' => now(),
            'unit_of_measurement_id' => 1, // kg
            'type_id' => 1,
            'material_id' => 1,
        ]);

        // Create harvest
        $harvest = Harvest::create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
            'container_id' => $container->id,
            'harvest_start_date' => now(),
            'harvest_end_date' => now()->addDays(1),
            'total_weight' => $weight,
            'unit' => 'kg',
        ]);

        // El HarvestObserver actualizará automáticamente used_capacity y ContainerCurrentState

        // Create initial stock
        HarvestStock::create([
            'harvest_id' => $harvest->id,
            'container_id' => $container->id,
            'user_id' => $user->id,
            'movement_type' => 'initial',
            'quantity_change' => $weight,
            'quantity_after' => $weight,
            'available_qty' => $weight,
            'reserved_qty' => 0,
            'sold_qty' => 0,
            'gifted_qty' => 0,
            'lost_qty' => 0,
            'notes' => 'Initial test stock',
        ]);

        // El ContainerCurrentState se crea automáticamente por el HarvestObserver
        // Refrescar para obtener los datos actualizados
        $container->refresh();

        return $harvest->fresh();
    }
}
