<?php

namespace Tests\Feature\Winery\Services;

use App\Models\Container;
use App\Models\Harvest;
use App\Models\HarvestStock;
use App\Services\ContainerStockService;
use Tests\Feature\WineryTestCase;
use Tests\Traits\CreatesDeliveryScenario;

/**
 * Cubre el flujo completo de ContainerStockService:
 * initializeStock, adjustWeight, transferContainer.
 *
 * Fuente de verdad: app/Services/ContainerStockService.php
 */
class ContainerStockServiceTest extends WineryTestCase
{
    use CreatesDeliveryScenario;

    private ContainerStockService $service;

    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
        $this->actingAs($this->winery);
        $this->service = app(ContainerStockService::class);
        $this->container = $this->makeContainer(['capacity' => 2000, 'used_capacity' => 0]);
    }

    // ── initializeStock ───────────────────────────────────────────────────────

    public function test_initialize_stock_creates_harvest_stock_entry(): void
    {
        $harvest = $this->makeHarvest(['total_weight' => 500]);

        $this->service->initializeStock($harvest);

        $this->assertDatabaseHas('harvest_stocks', [
            'harvest_id' => $harvest->id,
            'movement_type' => 'initial',
            'available_qty' => 500,
            'reserved_qty' => 0,
            'sold_qty' => 0,
        ]);
    }

    public function test_initialize_stock_increments_container_used_capacity(): void
    {
        $harvest = $this->makeHarvest(['total_weight' => 600]);

        $this->service->initializeStock($harvest);

        $this->container->refresh();
        $this->assertEquals(600.0, (float) $this->container->used_capacity);
    }

    public function test_initialize_stock_creates_container_current_state(): void
    {
        $harvest = $this->makeHarvest(['total_weight' => 400]);

        $this->service->initializeStock($harvest);

        $this->assertDatabaseHas('container_current_states', [
            'container_id' => $this->container->id,
            'harvest_id' => $harvest->id,
            'available_qty' => 400,
        ]);
    }

    public function test_initialize_stock_throws_when_container_is_full(): void
    {
        $fullContainer = $this->makeContainer(['capacity' => 100, 'used_capacity' => 100]);
        $harvest = $this->makeHarvest(['container_id' => $fullContainer->id, 'total_weight' => 50]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/capacidad/i');

        $this->service->initializeStock($harvest);
    }

    public function test_initialize_stock_throws_when_weight_exceeds_available_capacity(): void
    {
        $smallContainer = $this->makeContainer(['capacity' => 200, 'used_capacity' => 0]);
        $harvest = $this->makeHarvest(['container_id' => $smallContainer->id, 'total_weight' => 500]);

        $this->expectException(\Exception::class);

        $this->service->initializeStock($harvest);
    }

    // ── adjustWeight ──────────────────────────────────────────────────────────

    public function test_adjust_weight_increase_updates_available_qty(): void
    {
        $harvest = $this->makeHarvestWithStock(800);

        $this->service->adjustWeight($harvest, 800, 1000);

        $latest = HarvestStock::where('harvest_id', $harvest->id)->latest('id')->first();
        $this->assertEquals(1000.0, (float) $latest->available_qty);
        $this->assertEquals('adjustment', $latest->movement_type);
    }

    public function test_adjust_weight_increase_updates_container_capacity(): void
    {
        $harvest = $this->makeHarvestWithStock(500);
        $this->container->refresh();
        $previousUsed = (float) $this->container->used_capacity;

        $this->service->adjustWeight($harvest, 500, 700);

        $this->container->refresh();
        $this->assertEquals($previousUsed + 200, (float) $this->container->used_capacity);
    }

    public function test_adjust_weight_decrease_updates_available_and_container(): void
    {
        $harvest = $this->makeHarvestWithStock(1000);
        $this->container->refresh();

        $this->service->adjustWeight($harvest, 1000, 800);

        $latest = HarvestStock::where('harvest_id', $harvest->id)->latest('id')->first();
        $this->assertEquals(800.0, (float) $latest->available_qty);

        $this->container->refresh();
        $this->assertEquals(800.0, (float) $this->container->used_capacity);
    }

    public function test_adjust_weight_does_not_affect_sold_or_reserved_qty(): void
    {
        $harvest = $this->makeHarvestWithStock(1000);

        // Simular que 200 kg ya están reservados
        HarvestStock::where('harvest_id', $harvest->id)->latest('id')->first()
            ->update(['available_qty' => 800, 'reserved_qty' => 200]);

        // Re-leer el harvest con stock actualizado
        $harvest->refresh();

        $this->service->adjustWeight($harvest, 1000, 900);

        $latest = HarvestStock::where('harvest_id', $harvest->id)->latest('id')->first();
        // reserved no debe cambiar
        $this->assertEquals(200.0, (float) $latest->reserved_qty);
        $this->assertEquals(0.0, (float) $latest->sold_qty);
    }

    // ── transferContainer ─────────────────────────────────────────────────────

    public function test_transfer_container_frees_source_and_fills_destination(): void
    {
        $source = $this->makeContainer(['capacity' => 1000, 'used_capacity' => 500]);
        $destination = $this->makeContainer(['capacity' => 1000, 'used_capacity' => 0]);

        $harvest = $this->makeHarvestWithStock(500, $source);

        $this->service->transferContainer($harvest, $source->id, $destination->id);

        $source->refresh();
        $destination->refresh();

        $this->assertEquals(0.0, (float) $source->used_capacity);
        $this->assertEquals(500.0, (float) $destination->used_capacity);
    }

    public function test_transfer_container_throws_when_destination_has_no_capacity(): void
    {
        $source = $this->makeContainer(['capacity' => 1000, 'used_capacity' => 500]);
        $destination = $this->makeContainer(['capacity' => 200, 'used_capacity' => 200]);

        $harvest = $this->makeHarvestWithStock(500, $source);

        $this->expectException(\Exception::class);

        $this->service->transferContainer($harvest, $source->id, $destination->id);
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    /**
     * Crea un Harvest SIN disparar el HarvestObserver.
     * Permite testear el servicio de forma aislada sin doble-llamada a initializeStock.
     */
    private function makeHarvest(array $attrs = []): Harvest
    {
        return Harvest::withoutEvents(fn () => Harvest::create(array_merge([
            'winery_id' => $this->winery->id,
            'container_id' => $this->container->id,
            'plot_planting_id' => $this->planting->id,
            'total_weight' => 500,
            'harvest_start_date' => '2024-09-15',
            'status' => 'active',
            'vintage' => 2024,
        ], $attrs)));
    }

    private function makeHarvestWithStock(float $weight, ?Container $container = null): Harvest
    {
        $cnt = $container ?? $this->container;
        $cnt->update(['used_capacity' => 0]);

        $harvest = $this->makeHarvest([
            'container_id' => $cnt->id,
            'total_weight' => $weight,
        ]);

        $this->service->initializeStock($harvest);
        $harvest->refresh();

        return $harvest;
    }
}
