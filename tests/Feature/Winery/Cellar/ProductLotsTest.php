<?php

namespace Tests\Feature\Winery\Cellar;

use App\Livewire\Winery\Cellar\ProductLots\Create;
use App\Livewire\Winery\Cellar\ProductLots\Edit;
use App\Models\ProductLot;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class ProductLotsTest extends WineryTestCase
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
        $this->get(route('winery.product-lots.index'))
            ->assertOk();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('name', '')
            ->set('quantity', '')
            ->set('available_quantity', '')
            ->call('save')
            ->assertHasErrors(['name', 'quantity', 'available_quantity']);
    }

    public function test_create_saves_product_lot(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Tempranillo Gran Reserva')
            ->set('quantity', '1000')
            ->set('available_quantity', '1000')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_lots', [
            'user_id' => $this->winery->id,
            'name'    => 'Tempranillo Gran Reserva',
        ]);
    }

    public function test_edit_saves_changes(): void
    {
        $lot = ProductLot::create([
            'user_id'            => $this->winery->id,
            'name'               => 'Old Name',
            'wine_type'          => 'tinto',
            'quantity'           => 500,
            'initial_quantity'   => 500,
            'unit'               => 'botellas',
            'available_quantity' => 500,
            'reserved_quantity'  => 0,
            'sold_quantity'      => 0,
            'price_per_unit'     => 0,
            'archived'           => false,
        ]);

        Livewire::test(Edit::class, ['lot' => $lot])
            ->set('name', 'New Name')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_lots', [
            'id'   => $lot->id,
            'name' => 'New Name',
        ]);
    }

    public function test_other_winery_cannot_edit(): void
    {
        $otherWinery = $this->makeOtherWinery();

        $lot = ProductLot::create([
            'user_id'            => $this->winery->id,
            'name'               => 'My Lot',
            'wine_type'          => 'tinto',
            'quantity'           => 500,
            'initial_quantity'   => 500,
            'unit'               => 'botellas',
            'available_quantity' => 500,
            'reserved_quantity'  => 0,
            'sold_quantity'      => 0,
            'price_per_unit'     => 0,
            'archived'           => false,
        ]);

        $this->actingAs($otherWinery)
            ->get(route('winery.product-lots.edit', $lot))
            ->assertForbidden();
    }
}
