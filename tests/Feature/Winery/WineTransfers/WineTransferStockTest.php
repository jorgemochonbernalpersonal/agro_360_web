<?php

namespace Tests\Feature\Winery\WineTransfers;

use App\Livewire\Winery\WineTransfers\Create;
use App\Livewire\Winery\WineTransfers\Edit;
use App\Models\Container;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WineTransfer;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

/**
 * Cubre que los trasvases de vino sólo modifican wine_volume_liters
 * y NO tocan used_capacity (que es exclusivo de cosechas/uva).
 *
 * Regresión contra el bug de doble conteo:
 *   WineTransferObserver::created() incrementaba used_capacity del destino
 *   a la vez que WineContainerStockService::recordTransfer() incrementaba
 *   wine_volume_liters. Resultado: getTotalUsed() contaba el trasvase el doble.
 */
class WineTransferStockTest extends WineryTestCase
{
    private Wine $wine;
    private UnitOfMeasurement $uom;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->makeWinery());

        $this->wine = Wine::create([
            'user_id'   => auth()->id(),
            'name'      => 'Vino Tempranillo',
            'wine_type' => 'red',
            'status'    => 'in_progress',
        ]);

        $this->uom = UnitOfMeasurement::firstOrCreate(
            ['symbol' => 'L'],
            ['name' => 'Litros', 'type' => 'volume']
        );
    }

    // ── Crear trasvase ─────────────────────────────────────────────────────────

    public function test_create_transfer_only_modifies_wine_volume_liters(): void
    {
        $source = $this->makeWineContainer(wine: 1000.0, grapes: 0.0);
        $dest   = $this->makeWineContainer(wine: 0.0,    grapes: 0.0);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $this->wine->id)
            ->set('from_container_id', (string) $source->id)
            ->set('to_container_id', (string) $dest->id)
            ->set('quantity', '300')
            ->set('unit_of_measurement_id', (string) $this->uom->id)
            ->set('transfer_type', 'racking')
            ->set('transfer_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $source->refresh();
        $dest->refresh();

        // Volumen de vino movido correctamente
        $this->assertEquals(700.0, (float) $source->wine_volume_liters);
        $this->assertEquals(300.0, (float) $dest->wine_volume_liters);

        // used_capacity (uva/cosecha) NO debe cambiar — solo gestiona cosechas
        $this->assertEquals(0.0, (float) $source->used_capacity, 'used_capacity del origen no debe modificarse en un trasvase de vino');
        $this->assertEquals(0.0, (float) $dest->used_capacity, 'used_capacity del destino no debe modificarse en un trasvase de vino');
    }

    public function test_create_transfer_does_not_corrupt_existing_grape_stock(): void
    {
        // Contenedor con uva Y vino (contenedor mixto, raro pero posible)
        $source = $this->makeWineContainer(wine: 800.0, grapes: 500.0);
        $dest   = $this->makeWineContainer(wine: 0.0,   grapes: 0.0);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $this->wine->id)
            ->set('from_container_id', (string) $source->id)
            ->set('to_container_id', (string) $dest->id)
            ->set('quantity', '200')
            ->set('unit_of_measurement_id', (string) $this->uom->id)
            ->set('transfer_type', 'racking')
            ->set('transfer_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $source->refresh();
        $dest->refresh();

        // Vino trasvasado
        $this->assertEquals(600.0, (float) $source->wine_volume_liters);
        $this->assertEquals(200.0, (float) $dest->wine_volume_liters);

        // Stock de uva/cosecha NO debe tocarse
        $this->assertEquals(500.0, (float) $source->used_capacity, 'used_capacity del origen (cosechas) no debe modificarse');
        $this->assertEquals(0.0,   (float) $dest->used_capacity,   'used_capacity del destino no debe modificarse');
    }

    public function test_create_transfer_total_used_capacity_is_correct(): void
    {
        $source = $this->makeWineContainer(wine: 1000.0, grapes: 0.0);
        $dest   = $this->makeWineContainer(wine: 0.0,    grapes: 0.0, capacity: 5000.0);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $this->wine->id)
            ->set('from_container_id', (string) $source->id)
            ->set('to_container_id', (string) $dest->id)
            ->set('quantity', '400')
            ->set('unit_of_measurement_id', (string) $this->uom->id)
            ->set('transfer_type', 'racking')
            ->set('transfer_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $dest->refresh();

        // getTotalUsed = used_capacity + wine_volume_liters = 0 + 400 = 400
        // NO debe ser 800 (bug: 400 del observer + 400 del servicio)
        $this->assertEquals(400.0, $dest->getTotalUsed(), 'getTotalUsed no debe contar el trasvase el doble');
        $this->assertEquals(4600.0, $dest->getAvailableCapacity(), 'capacidad disponible incorrecta');
    }

    public function test_create_transfer_without_source_increments_destination(): void
    {
        $dest = $this->makeWineContainer(wine: 200.0, grapes: 0.0);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $this->wine->id)
            ->set('from_container_id', '')
            ->set('to_container_id', (string) $dest->id)
            ->set('quantity', '100')
            ->set('unit_of_measurement_id', (string) $this->uom->id)
            ->set('transfer_type', 'racking')
            ->set('transfer_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $dest->refresh();
        $this->assertEquals(300.0, (float) $dest->wine_volume_liters);
        $this->assertEquals(0.0,   (float) $dest->used_capacity);
    }

    public function test_capacity_validation_blocks_overfill(): void
    {
        $source = $this->makeWineContainer(wine: 1000.0, grapes: 0.0);
        $dest   = $this->makeWineContainer(wine: 0.0,    grapes: 0.0, capacity: 200.0);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $this->wine->id)
            ->set('from_container_id', (string) $source->id)
            ->set('to_container_id', (string) $dest->id)
            ->set('quantity', '500')   // más que la capacidad de 200L
            ->set('unit_of_measurement_id', (string) $this->uom->id)
            ->set('transfer_type', 'racking')
            ->set('transfer_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['quantity']);

        // Sin cambios en contenedores
        $source->refresh();
        $dest->refresh();
        $this->assertEquals(1000.0, (float) $source->wine_volume_liters);
        $this->assertEquals(0.0,    (float) $dest->wine_volume_liters);
    }

    // ── Editar trasvase ────────────────────────────────────────────────────────

    public function test_edit_transfer_recalculates_wine_volume_correctly(): void
    {
        $source = $this->makeWineContainer(wine: 1000.0, grapes: 0.0);
        $dest   = $this->makeWineContainer(wine: 0.0,    grapes: 0.0);

        // Crear trasvase inicial via Livewire (300L)
        Livewire::test(Create::class)
            ->set('wine_id', (string) $this->wine->id)
            ->set('from_container_id', (string) $source->id)
            ->set('to_container_id', (string) $dest->id)
            ->set('quantity', '300')
            ->set('unit_of_measurement_id', (string) $this->uom->id)
            ->set('transfer_type', 'racking')
            ->set('transfer_date', now()->toDateString())
            ->call('save');

        $transfer = WineTransfer::latest()->first();
        $source->refresh();
        $dest->refresh();

        // Editar: cambiar a 500L
        Livewire::test(Edit::class, ['transfer' => $transfer])
            ->set('quantity', '500')
            ->call('save')
            ->assertHasNoErrors();

        $source->refresh();
        $dest->refresh();

        $this->assertEquals(500.0, (float) $source->wine_volume_liters, 'origen debe tener 500L restantes');
        $this->assertEquals(500.0, (float) $dest->wine_volume_liters,   'destino debe tener 500L');
        $this->assertEquals(0.0,   (float) $source->used_capacity);
        $this->assertEquals(0.0,   (float) $dest->used_capacity);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeWineContainer(float $wine, float $grapes, float $capacity = 5000.0): Container
    {
        return Container::create([
            'user_id'            => auth()->id(),
            'name'               => 'Dep. ' . uniqid(),
            'capacity'           => $capacity,
            'used_capacity'      => $grapes,
            'wine_volume_liters' => $wine,
            'archived'           => false,
        ]);
    }
}
