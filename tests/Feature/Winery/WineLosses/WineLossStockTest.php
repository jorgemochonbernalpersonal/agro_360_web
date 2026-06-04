<?php

namespace Tests\Feature\Winery\WineLosses;

use App\Livewire\Winery\WineLosses\Create;
use App\Livewire\Winery\WineLosses\Edit;
use App\Models\Container;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WineLoss;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

/**
 * Bug A — Regresión: wine.volume_liters no se actualiza al EDITAR una merma.
 *
 * Traza:
 *   1. Crear merma 100L → wine.volume_liters -= 100 ✓
 *   2. Editar a 150L   → container correcto, wine.volume_liters sigue en -100 ✗
 *   3. Borrar (150L)   → observer deleted: +150, pero solo se restaron 100 → +50 inflado ✗
 */

/**
 * Cubre que las mermas de vino sólo modifican wine_volume_liters (contenedor)
 * y wine.volume_liters (modelo Wine) pero NO tocan used_capacity.
 *
 * Regresión contra el bug de doble conteo:
 *   WineLossObserver::created() decrementaba used_capacity del contenedor
 *   a la vez que WineContainerStockService::recordLoss() decrementaba
 *   wine_volume_liters.
 */
class WineLossStockTest extends WineryTestCase
{
    private Wine $wine;

    private UnitOfMeasurement $uom;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->makeWinery());

        $this->wine = Wine::create([
            'user_id' => auth()->id(),
            'name' => 'Vino Rosado Test',
            'wine_type' => 'rosé',
            'status' => 'in_progress',
            'volume_liters' => 500.0,
        ]);

        $this->uom = UnitOfMeasurement::firstOrCreate(
            ['symbol' => 'L'],
            ['name' => 'Litros', 'type' => 'volume']
        );
    }

    // ── Crear merma ────────────────────────────────────────────────────────────

    public function test_create_loss_decrements_wine_volume_liters_not_used_capacity(): void
    {
        $container = $this->makeWineContainer(wine: 500.0, grapes: 0.0);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $this->wine->id)
            ->set('container_id', (string) $container->id)
            ->set('loss_type', 'evaporation')
            ->set('quantity', '50')
            ->set('unit_of_measurement_id', (string) $this->uom->id)
            ->set('loss_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $container->refresh();

        // Merma descuenta el vino
        $this->assertEquals(450.0, (float) $container->wine_volume_liters);

        // used_capacity (cosechas) no debe verse afectada
        $this->assertEquals(0.0, (float) $container->used_capacity, 'used_capacity no debe modificarse en una merma de vino');
    }

    public function test_create_loss_does_not_corrupt_grape_stock(): void
    {
        // Contenedor con uva Y vino
        $container = $this->makeWineContainer(wine: 600.0, grapes: 400.0);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $this->wine->id)
            ->set('container_id', (string) $container->id)
            ->set('loss_type', 'evaporation')
            ->set('quantity', '100')
            ->set('unit_of_measurement_id', (string) $this->uom->id)
            ->set('loss_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $container->refresh();

        $this->assertEquals(500.0, (float) $container->wine_volume_liters, 'wine_volume_liters debe decrementarse');
        $this->assertEquals(400.0, (float) $container->used_capacity, 'used_capacity (cosechas) no debe modificarse');
    }

    public function test_create_loss_updates_wine_model_volume(): void
    {
        $container = $this->makeWineContainer(wine: 500.0, grapes: 0.0);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $this->wine->id)
            ->set('container_id', (string) $container->id)
            ->set('loss_type', 'evaporation')
            ->set('quantity', '80')
            ->set('unit_of_measurement_id', (string) $this->uom->id)
            ->set('loss_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        // El modelo Wine.volume_liters también debe decrementarse
        $this->wine->refresh();
        $this->assertEquals(420.0, (float) $this->wine->volume_liters, 'wine.volume_liters debe decrementarse');
    }

    public function test_create_loss_total_used_is_correct(): void
    {
        $container = $this->makeWineContainer(wine: 500.0, grapes: 0.0, capacity: 1000.0);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $this->wine->id)
            ->set('container_id', (string) $container->id)
            ->set('loss_type', 'evaporation')
            ->set('quantity', '100')
            ->set('unit_of_measurement_id', (string) $this->uom->id)
            ->set('loss_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $container->refresh();

        // getTotalUsed = 0 (used_capacity) + 400 (wine_volume_liters) = 400
        // NO debe ser 500 (bug: 100L restados dos veces del campo equivocado)
        $this->assertEquals(400.0, $container->getTotalUsed(), 'getTotalUsed incorrecto tras merma');
        $this->assertEquals(600.0, $container->getAvailableCapacity(), 'capacidad disponible incorrecta');
    }

    public function test_loss_without_container_still_updates_wine_volume(): void
    {
        Livewire::test(Create::class)
            ->set('wine_id', (string) $this->wine->id)
            ->set('container_id', '')   // sin contenedor
            ->set('loss_type', 'evaporation')
            ->set('quantity', '30')
            ->set('unit_of_measurement_id', (string) $this->uom->id)
            ->set('loss_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->wine->refresh();
        $this->assertEquals(470.0, (float) $this->wine->volume_liters);
    }

    // ── Editar merma ───────────────────────────────────────────────────────────

    public function test_edit_loss_recalculates_correctly(): void
    {
        $container = $this->makeWineContainer(wine: 500.0, grapes: 0.0);

        // Merma inicial de 100L via Livewire
        Livewire::test(Create::class)
            ->set('wine_id', (string) $this->wine->id)
            ->set('container_id', (string) $container->id)
            ->set('loss_type', 'evaporation')
            ->set('quantity', '100')
            ->set('unit_of_measurement_id', (string) $this->uom->id)
            ->set('loss_date', now()->toDateString())
            ->call('save');

        $loss = WineLoss::latest()->first();
        $container->refresh();

        // Editar: cambiar a 150L
        Livewire::test(Edit::class, ['loss' => $loss])
            ->set('quantity', '150')
            ->call('save')
            ->assertHasNoErrors();

        $container->refresh();

        $this->assertEquals(350.0, (float) $container->wine_volume_liters, 'wine_volume_liters debe reflejar la merma editada');
        $this->assertEquals(0.0, (float) $container->used_capacity, 'used_capacity no debe modificarse');
    }

    // ── Bug A: wine.volume_liters no se actualiza al editar ────────────────────

    public function test_edit_loss_updates_wine_model_volume_liters(): void
    {
        $container = $this->makeWineContainer(wine: 500.0, grapes: 0.0);

        // Crear merma inicial de 100L → wine.volume_liters = 500 - 100 = 400
        Livewire::test(Create::class)
            ->set('wine_id', (string) $this->wine->id)
            ->set('container_id', (string) $container->id)
            ->set('loss_type', 'evaporation')
            ->set('quantity', '100')
            ->set('unit_of_measurement_id', (string) $this->uom->id)
            ->set('loss_date', now()->toDateString())
            ->call('save');

        $this->wine->refresh();
        $this->assertEquals(400.0, (float) $this->wine->volume_liters, 'tras crear merma 100L');

        $loss = WineLoss::latest()->first();

        // Editar a 150L → wine.volume_liters debe pasar de 400 a 350
        Livewire::test(Edit::class, ['loss' => $loss])
            ->set('quantity', '150')
            ->call('save')
            ->assertHasNoErrors();

        $this->wine->refresh();
        $this->assertEquals(350.0, (float) $this->wine->volume_liters,
            'wine.volume_liters debe reflejar la diferencia de la edición (400 - 50 extra = 350)');
    }

    public function test_delete_after_edit_restores_wine_volume_correctly(): void
    {
        $container = $this->makeWineContainer(wine: 500.0, grapes: 0.0);

        // Crear merma 100L
        Livewire::test(Create::class)
            ->set('wine_id', (string) $this->wine->id)
            ->set('container_id', (string) $container->id)
            ->set('loss_type', 'evaporation')
            ->set('quantity', '100')
            ->set('unit_of_measurement_id', (string) $this->uom->id)
            ->set('loss_date', now()->toDateString())
            ->call('save');

        $loss = WineLoss::latest()->first();

        // Editar a 200L
        Livewire::test(Edit::class, ['loss' => $loss])
            ->set('quantity', '200')
            ->call('save');

        // Borrar la merma → wine.volume_liters debe volver a 500 exactamente
        $loss->fresh()->delete();

        $this->wine->refresh();
        $this->assertEquals(500.0, (float) $this->wine->volume_liters,
            'tras borrar la merma el volumen debe volver a 500, no a 400 ni a 600');
    }

    public function test_edit_loss_changing_wine_updates_both_wines(): void
    {
        $container = $this->makeWineContainer(wine: 500.0, grapes: 0.0);

        $wineB = Wine::create([
            'user_id' => auth()->id(),
            'name' => 'Vino B',
            'wine_type' => 'white',
            'status' => 'in_progress',
            'volume_liters' => 600.0,
        ]);

        // Crear merma 80L del vino A
        Livewire::test(Create::class)
            ->set('wine_id', (string) $this->wine->id)
            ->set('container_id', (string) $container->id)
            ->set('loss_type', 'evaporation')
            ->set('quantity', '80')
            ->set('unit_of_measurement_id', (string) $this->uom->id)
            ->set('loss_date', now()->toDateString())
            ->call('save');

        $loss = WineLoss::latest()->first();

        // Editar: cambiar vino A → vino B, cantidad 60L
        Livewire::test(Edit::class, ['loss' => $loss])
            ->set('wine_id', (string) $wineB->id)
            ->set('quantity', '60')
            ->call('save')
            ->assertHasNoErrors();

        $this->wine->refresh();
        $wineB->refresh();

        // Vino A: se restauran los 80L restados = vuelve a 500
        $this->assertEquals(500.0, (float) $this->wine->volume_liters,
            'wine A debe recuperar los 80L restados al cambiar el vino de la merma');

        // Vino B: se descuentan los 60L nuevos
        $this->assertEquals(540.0, (float) $wineB->volume_liters,
            'wine B debe tener descontados los 60L de la nueva merma');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeWineContainer(float $wine, float $grapes, float $capacity = 5000.0): Container
    {
        return Container::create([
            'user_id' => auth()->id(),
            'name' => 'Dep. '.uniqid(),
            'capacity' => $capacity,
            'used_capacity' => $grapes,
            'wine_volume_liters' => $wine,
            'archived' => false,
        ]);
    }
}
