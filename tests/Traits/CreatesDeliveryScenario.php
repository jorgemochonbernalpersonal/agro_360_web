<?php

namespace Tests\Traits;

use App\Models\Campaign;
use App\Models\Container;
use App\Models\GrapeReceptionBatch;
use App\Models\GrapeVariety;
use App\Models\Harvest;
use App\Models\HarvestDelivery;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\User;
use App\Models\WineryViticulturist;

/**
 * Sets up the winery ↔ viticulturist scenario needed for vendimia tests:
 * one winery, one viticulturist, linked plot + planting.
 *
 * Provides helpers to create HarvestDelivery and winery Harvest records
 * without going through Livewire, so observer / auto-link logic can be
 * tested in isolation.
 */
trait CreatesDeliveryScenario
{
    protected User $winery;
    protected User $viticulturist;
    protected Plot $plot;
    protected PlotPlanting $planting;
    protected GrapeVariety $grapeVariety;

    protected function setUpScenario(): void
    {
        $this->viticulturist = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->winery = User::factory()->create([
            'role'              => 'winery',
            'email'             => 'bodega@test.com',
            'email_verified_at' => now(),
        ]);

        WineryViticulturist::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->winery->id,
        ]);

        $this->grapeVariety = GrapeVariety::firstOrCreate(
            ['code' => 'TEMP'],
            ['name' => 'Tempranillo', 'color' => 'red']
        );

        $this->plot = Plot::create([
            'viticulturist_id' => $this->viticulturist->id,
            'name'             => 'Parcela Test',
            'reference'        => 'TEST-001',
            'area'             => 5.0,
            'active'           => true,
        ]);

        $this->planting = PlotPlanting::create([
            'plot_id'          => $this->plot->id,
            'grape_variety_id' => $this->grapeVariety->id,
            'area_planted'     => 4.0,
            'planting_year'    => now()->year - 5,
            'status'           => 'active',
        ]);
    }

    /**
     * Create a HarvestDelivery for the viticulturist.
     */
    protected function makeDelivery(array $attrs = []): HarvestDelivery
    {
        return HarvestDelivery::create(array_merge([
            'viticulturist_id' => $this->viticulturist->id,
            'plot_planting_id' => $this->planting->id,
            'vintage_year'     => 2024,
            'buyer_name'       => 'Bodega Test',
            'delivered_kg'     => 1000,
            'delivery_date'    => '2024-09-15',
            'status'           => 'pending',
        ], $attrs));
    }

    /**
     * Create a Container owned by the winery.
     */
    protected function makeContainer(array $attrs = []): Container
    {
        return Container::create(array_merge([
            'user_id'       => $this->winery->id,
            'name'          => 'Depósito Test',
            'capacity'      => 5000,
            'used_capacity' => 0,
            'archived'      => false,
        ], $attrs));
    }

    /**
     * Create a winery reception (Harvest with winery_id + batch).
     *
     * Must call $this->actingAs($this->winery) before using this helper
     * so ContainerStockService::initializeStock() has a valid Auth::id().
     */
    protected function makeWineryReception(array $attrs = []): Harvest
    {
        $campaign = Campaign::getOrCreateActiveForYear($this->viticulturist->id, 2024);

        $batch = GrapeReceptionBatch::firstOrCreate(
            [
                'winery_id'        => $this->winery->id,
                'plot_planting_id' => $this->planting->id,
                'campaign_id'      => $campaign->id,
            ],
            [
                'viticulturist_id' => $this->viticulturist->id,
                'vintage_year'     => 2024,
                'total_weight_kg'  => 0,
                'status'           => 'open',
            ]
        );

        return Harvest::create(array_merge([
            'winery_id'          => $this->winery->id,
            'batch_id'           => $batch->id,
            'plot_planting_id'   => $this->planting->id,
            'vintage'            => 2024,
            'total_weight'       => 1000,
            'harvest_start_date' => '2024-09-15',
            'status'             => 'active',
        ], $attrs));
    }
}
