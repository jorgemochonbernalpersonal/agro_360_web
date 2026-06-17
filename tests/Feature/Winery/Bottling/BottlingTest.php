<?php

namespace Tests\Feature\Winery\Bottling;

use App\Livewire\Winery\Bottling\Create;
use App\Livewire\Winery\Bottling\Index;
use App\Models\Container;
use App\Models\UnitOfMeasurement;
use App\Models\User;
use App\Models\Wine;
use App\Models\WineBottling;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class BottlingTest extends WineryTestCase
{
    private User $winery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    public function test_index_renders(): void
    {
        $this->get(route('winery.bottling.index'))
            ->assertOk();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('bottling_date', '')
            ->set('bottle_format', '')
            ->set('quantity_bottles', '')
            ->set('quantity_liters', '')
            ->call('save')
            ->assertHasErrors(['wine_id', 'bottling_date', 'bottle_format', 'quantity_bottles', 'quantity_liters']);
    }

    public function test_create_rejects_wine_from_other_winery(): void
    {
        $otherWine = Wine::create([
            'user_id'       => $this->makeOtherWinery()->id,
            'name'          => 'Other Wine',
            'wine_type'     => 'red',
            'status'        => 'in_progress',
            'volume_liters' => 1000,
        ]);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $otherWine->id)
            ->set('bottling_date', now()->toDateString())
            ->set('bottle_format', '750')
            ->set('quantity_bottles', '100')
            ->set('quantity_liters', '75')
            ->call('save')
            ->assertHasErrors(['wine_id']);
    }

    public function test_other_winery_cannot_edit(): void
    {
        $otherWinery = $this->makeOtherWinery();

        $wine = Wine::create([
            'user_id' => $this->winery->id,
            'name' => 'Test Wine',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ]);

        $bottling = WineBottling::create([
            'user_id' => $this->winery->id,
            'wine_id' => $wine->id,
            'bottling_date' => today(),
            'bottle_format' => '750',
            'quantity_bottles' => 100,
            'quantity_liters' => 75,
            'lot_number' => 'LOT-001',
            'created_by' => $this->winery->id,
        ]);

        $this->actingAs($otherWinery)
            ->get(route('winery.bottling.edit', $bottling))
            ->assertForbidden();
    }

    public function test_index_shows_only_own_bottlings(): void
    {
        $otherWinery = $this->makeOtherWinery();

        $ownWine = Wine::create([
            'user_id' => $this->winery->id,
            'name' => 'Own Wine',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ]);

        WineBottling::create([
            'user_id' => $this->winery->id,
            'wine_id' => $ownWine->id,
            'bottling_date' => today(),
            'bottle_format' => '750',
            'quantity_bottles' => 100,
            'quantity_liters' => 75,
            'lot_number' => 'MY-LOT-001',
            'created_by' => $this->winery->id,
        ]);

        $otherWine = Wine::create([
            'user_id' => $otherWinery->id,
            'name' => 'Other Wine',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ]);

        WineBottling::create([
            'user_id' => $otherWinery->id,
            'wine_id' => $otherWine->id,
            'bottling_date' => today(),
            'bottle_format' => '750',
            'quantity_bottles' => 50,
            'quantity_liters' => 37.5,
            'lot_number' => 'OTHER-LOT-001',
            'created_by' => $otherWinery->id,
        ]);

        Livewire::test(Index::class)
            ->assertSee('MY-LOT-001')
            ->assertDontSee('OTHER-LOT-001');
    }

    // ── Stock de contenedor ────────────────────────────────────────────────────

    public function test_bottling_decrements_container_wine_volume_liters(): void
    {
        $wine = Wine::create([
            'user_id' => $this->winery->id,
            'name' => 'Vino Embotellado',
            'wine_type' => 'red',
            'status' => 'in_progress',
            'volume_liters' => 1000.0,
        ]);

        $container = Container::create([
            'user_id' => $this->winery->id,
            'name' => 'Dep. Embotellado',
            'capacity' => 5000,
            'used_capacity' => 0,
            'wine_volume_liters' => 1000.0,
            'archived' => false,
        ]);

        $uom = UnitOfMeasurement::firstOrCreate(
            ['symbol' => 'L'],
            ['name' => 'Litros', 'type' => 'volume']
        );

        Livewire::test(Create::class)
            ->set('wine_id', (string) $wine->id)
            ->set('container_id', (string) $container->id)
            ->set('bottling_date', now()->toDateString())
            ->set('bottle_format', '750')
            ->set('quantity_bottles', '100')
            ->set('quantity_liters', '75')
            ->set('lot_number', 'LOT-STOCK-001')
            ->call('save')
            ->assertHasNoErrors();

        $container->refresh();

        // El embotellado descuenta wine_volume_liters
        $this->assertEquals(925.0, (float) $container->wine_volume_liters);

        // used_capacity (cosechas) no debe tocarse
        $this->assertEquals(0.0, (float) $container->used_capacity);
    }

    public function test_bottling_without_container_does_not_affect_any_container(): void
    {
        $wine = Wine::create([
            'user_id' => $this->winery->id,
            'name' => 'Vino Sin Dep',
            'wine_type' => 'red',
            'status' => 'in_progress',
            'volume_liters' => 500.0,
        ]);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $wine->id)
            ->set('container_id', '')
            ->set('bottling_date', now()->toDateString())
            ->set('bottle_format', '750')
            ->set('quantity_bottles', '50')
            ->set('quantity_liters', '37.5')
            ->set('lot_number', 'LOT-NOCONTAINER')
            ->call('save')
            ->assertHasNoErrors();

        // No debe haber creado registros de contenedor con stock
        $this->assertDatabaseCount('container_histories', 0);
    }

    public function test_bottling_blocks_quantity_exceeding_container_wine(): void
    {
        $wine = Wine::create([
            'user_id' => $this->winery->id,
            'name' => 'Vino Poco',
            'wine_type' => 'red',
            'status' => 'in_progress',
            'volume_liters' => 100.0,
        ]);

        $container = Container::create([
            'user_id' => $this->winery->id,
            'name' => 'Dep. Pequeño',
            'capacity' => 5000,
            'used_capacity' => 0,
            'wine_volume_liters' => 50.0,   // solo 50L
            'archived' => false,
        ]);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $wine->id)
            ->set('container_id', (string) $container->id)
            ->set('bottling_date', now()->toDateString())
            ->set('bottle_format', '750')
            ->set('quantity_bottles', '100')
            ->set('quantity_liters', '75')  // más que los 50L disponibles
            ->set('lot_number', 'LOT-OVERFILL')
            ->call('save')
            ->assertHasErrors(['quantity_liters']);

        $container->refresh();
        $this->assertEquals(50.0, (float) $container->wine_volume_liters);
    }
}
