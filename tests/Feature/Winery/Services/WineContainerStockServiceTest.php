<?php

namespace Tests\Feature\Winery\Services;

use App\Models\Container;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WineBottling;
use App\Models\WineLoss;
use App\Models\WineTransfer;
use App\Services\WineContainerStockService;
use Tests\Feature\WineryTestCase;
use Tests\Traits\CreatesDeliveryScenario;

/**
 * Cubre el flujo completo de WineContainerStockService:
 * trasvases, mermas y embotellado.
 *
 * Fuente de verdad: app/Services/WineContainerStockService.php
 */
class WineContainerStockServiceTest extends WineryTestCase
{
    use CreatesDeliveryScenario;

    private WineContainerStockService $service;

    private Wine $wine;

    private UnitOfMeasurement $uom;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
        $this->actingAs($this->winery);
        $this->service = app(WineContainerStockService::class);
        $this->wine = Wine::create([
            'user_id' => $this->winery->id,
            'name' => 'Vino Test',
            'wine_type' => 'red',
            'status' => 'in_progress',
            'volume_liters' => 99999,
        ]);
        $this->uom = UnitOfMeasurement::firstOrCreate(
            ['symbol' => 'L'],
            ['name' => 'Litros', 'type' => 'volume']
        );
    }

    // ── recordTransfer ────────────────────────────────────────────────────────

    public function test_record_transfer_decrements_source_and_increments_destination(): void
    {
        $source = $this->makeWineContainer(1000.0);
        $dest = $this->makeWineContainer(0.0);

        $transfer = $this->makeTransfer($source, $dest, 300.0);

        $this->service->recordTransfer($transfer);

        $source->refresh();
        $dest->refresh();

        $this->assertEquals(700.0, (float) $source->wine_volume_liters);
        $this->assertEquals(300.0, (float) $dest->wine_volume_liters);
    }

    public function test_record_transfer_without_source_only_increments_destination(): void
    {
        $dest = $this->makeWineContainer(200.0);

        $transfer = $this->makeTransfer(null, $dest, 500.0);

        $this->service->recordTransfer($transfer);

        $dest->refresh();
        $this->assertEquals(700.0, (float) $dest->wine_volume_liters);
    }

    public function test_record_transfer_registers_history_entries(): void
    {
        $source = $this->makeWineContainer(1000.0);
        $dest = $this->makeWineContainer(0.0);

        $transfer = $this->makeTransfer($source, $dest, 200.0);

        $this->service->recordTransfer($transfer);

        $this->assertDatabaseHas('container_histories', [
            'container_id' => $source->id,
            'operation_type' => 'wine_transfer_out',
        ]);
        $this->assertDatabaseHas('container_histories', [
            'container_id' => $dest->id,
            'operation_type' => 'wine_transfer_in',
        ]);
    }

    // ── revertTransfer ────────────────────────────────────────────────────────

    public function test_revert_transfer_restores_original_volumes(): void
    {
        $source = $this->makeWineContainer(1000.0);
        $dest = $this->makeWineContainer(0.0);

        $transfer = $this->makeTransfer($source, $dest, 400.0);
        $this->service->recordTransfer($transfer);

        $this->service->revertTransfer($transfer);

        $source->refresh();
        $dest->refresh();

        $this->assertEquals(1000.0, (float) $source->wine_volume_liters);
        $this->assertEquals(0.0, (float) $dest->wine_volume_liters);
    }

    public function test_record_then_revert_is_idempotent(): void
    {
        $source = $this->makeWineContainer(500.0);
        $dest = $this->makeWineContainer(100.0);

        $originalSource = 500.0;
        $originalDest = 100.0;

        $transfer = $this->makeTransfer($source, $dest, 150.0);

        $this->service->recordTransfer($transfer);
        $this->service->revertTransfer($transfer);

        $source->refresh();
        $dest->refresh();

        $this->assertEquals($originalSource, (float) $source->wine_volume_liters);
        $this->assertEquals($originalDest, (float) $dest->wine_volume_liters);
    }

    // ── updateTransfer ────────────────────────────────────────────────────────

    public function test_update_transfer_applies_new_quantity_atomically(): void
    {
        $source = $this->makeWineContainer(1000.0);
        $dest = $this->makeWineContainer(0.0);

        $transfer = $this->makeTransfer($source, $dest, 300.0);
        $this->service->recordTransfer($transfer);

        $oldData = [
            'wine_id' => $transfer->wine_id,
            'from_container_id' => $transfer->from_container_id,
            'to_container_id' => $transfer->to_container_id,
            'quantity' => 300.0,
        ];

        $transfer->quantity = 600.0;
        $transfer->save();

        $this->service->updateTransfer($transfer, $oldData);

        $source->refresh();
        $dest->refresh();

        $this->assertEquals(400.0, (float) $source->wine_volume_liters);
        $this->assertEquals(600.0, (float) $dest->wine_volume_liters);
    }

    // ── recordLoss ────────────────────────────────────────────────────────────

    public function test_record_loss_decrements_container_wine_volume(): void
    {
        $container = $this->makeWineContainer(800.0);

        $loss = $this->makeLoss($container, 80.0);

        $this->service->recordLoss($loss);

        $container->refresh();
        $this->assertEquals(720.0, (float) $container->wine_volume_liters);
    }

    public function test_record_loss_does_not_go_below_zero(): void
    {
        $container = $this->makeWineContainer(50.0);

        $loss = $this->makeLoss($container, 200.0);

        $this->service->recordLoss($loss);

        $container->refresh();
        $this->assertEquals(0.0, (float) $container->wine_volume_liters);
    }

    public function test_record_loss_creates_history_entry(): void
    {
        $container = $this->makeWineContainer(600.0);

        $loss = $this->makeLoss($container, 60.0);

        $this->service->recordLoss($loss);

        $this->assertDatabaseHas('container_histories', [
            'container_id' => $container->id,
            'operation_type' => 'wine_loss',
        ]);
    }

    // ── revertLoss ────────────────────────────────────────────────────────────

    public function test_revert_loss_restores_container_volume(): void
    {
        $container = $this->makeWineContainer(700.0);

        $loss = $this->makeLoss($container, 100.0);
        $this->service->recordLoss($loss);

        $this->service->revertLoss($loss);

        $container->refresh();
        $this->assertEquals(700.0, (float) $container->wine_volume_liters);
    }

    // ── updateLoss ────────────────────────────────────────────────────────────

    public function test_update_loss_applies_new_quantity(): void
    {
        $container = $this->makeWineContainer(500.0);

        $loss = $this->makeLoss($container, 50.0);
        $this->service->recordLoss($loss);

        $oldData = [
            'wine_id' => $loss->wine_id,
            'container_id' => $loss->container_id,
            'quantity' => 50.0,
        ];

        $loss->quantity = 150.0;
        $loss->save();

        $this->service->updateLoss($loss, $oldData);

        $container->refresh();
        // 500 - 150 = 350
        $this->assertEquals(350.0, (float) $container->wine_volume_liters);
    }

    // ── recordBottling ────────────────────────────────────────────────────────

    public function test_record_bottling_decrements_container_wine_volume(): void
    {
        $container = $this->makeWineContainer(2000.0);

        $bottling = $this->makeBottling($container, 750.0);

        $this->service->recordBottling($bottling);

        $container->refresh();
        $this->assertEquals(1250.0, (float) $container->wine_volume_liters);
    }

    public function test_record_bottling_creates_history_entry(): void
    {
        $container = $this->makeWineContainer(2000.0);

        $bottling = $this->makeBottling($container, 500.0);

        $this->service->recordBottling($bottling);

        $this->assertDatabaseHas('container_histories', [
            'container_id' => $container->id,
            'operation_type' => 'bottling',
        ]);
    }

    // ── revertBottling ────────────────────────────────────────────────────────

    public function test_revert_bottling_restores_container_volume(): void
    {
        $container = $this->makeWineContainer(2000.0);

        $bottling = $this->makeBottling($container, 600.0);
        $this->service->recordBottling($bottling);

        $this->service->revertBottling($bottling);

        $container->refresh();
        $this->assertEquals(2000.0, (float) $container->wine_volume_liters);
    }

    // ── updateBottling ────────────────────────────────────────────────────────

    public function test_update_bottling_applies_new_liters(): void
    {
        $container = $this->makeWineContainer(2000.0);

        $bottling = $this->makeBottling($container, 400.0);
        $this->service->recordBottling($bottling);

        $oldData = [
            'wine_id' => $bottling->wine_id,
            'container_id' => $bottling->container_id,
            'quantity_liters' => 400.0,
        ];

        $bottling->quantity_liters = 800.0;
        $bottling->save();

        $this->service->updateBottling($bottling, $oldData);

        $container->refresh();
        // 2000 - 800 = 1200
        $this->assertEquals(1200.0, (float) $container->wine_volume_liters);
    }

    // ── emptyWineContent ─────────────────────────────────────────────────────

    public function test_empty_wine_content_sets_volume_to_zero(): void
    {
        $container = $this->makeWineContainer(900.0);

        $this->service->emptyWineContent($container);

        $container->refresh();
        $this->assertEquals(0.0, (float) $container->wine_volume_liters);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function makeWineContainer(float $wineVolume): Container
    {
        return Container::create([
            'user_id' => $this->winery->id,
            'name' => 'Dep. '.uniqid(),
            'capacity' => 5000,
            'used_capacity' => 0,
            'wine_volume_liters' => $wineVolume,
            'archived' => false,
        ]);
    }

    private function makeTransfer(?Container $from, Container $to, float $qty): WineTransfer
    {
        return WineTransfer::create([
            'wine_id' => $this->wine->id,
            'from_container_id' => $from?->id,
            'to_container_id' => $to->id,
            'quantity' => $qty,
            'unit_of_measurement_id' => $this->uom->id,
            'transfer_type' => 'racking',
            'transfer_date' => now(),
            'created_by' => $this->winery->id,
        ]);
    }

    private function makeLoss(Container $container, float $qty): WineLoss
    {
        return WineLoss::create([
            'wine_id' => $this->wine->id,
            'container_id' => $container->id,
            'quantity' => $qty,
            'unit_of_measurement_id' => $this->uom->id,
            'loss_type' => 'evaporation',
            'loss_date' => now(),
            'created_by' => $this->winery->id,
        ]);
    }

    private function makeBottling(Container $container, float $liters): WineBottling
    {
        return WineBottling::create([
            'user_id' => $this->winery->id,
            'wine_id' => $this->wine->id,
            'container_id' => $container->id,
            'quantity_liters' => $liters,
            'quantity_bottles' => (int) ($liters / 0.75),
            'bottle_format' => '750',
            'bottling_date' => now(),
            'created_by' => $this->winery->id,
        ]);
    }
}
