<?php

namespace Tests\Feature\Winery\Subproducts;

use App\Livewire\Winery\Subproducts\Create;
use App\Livewire\Winery\Subproducts\Edit;
use App\Models\User;
use App\Models\Wine;
use App\Models\WineSubproduct;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class SubproductsTest extends WineryTestCase
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
        $this->get(route('winery.subproducts.index'))->assertOk();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('type', '')
            ->set('subproduct_date', '')
            ->set('quantity', '')
            ->set('destination', '')
            ->call('save')
            ->assertHasErrors(['type', 'subproduct_date', 'quantity', 'destination']);
    }

    public function test_create_saves_subproduct(): void
    {
        $firstType = array_key_first(WineSubproduct::TYPES);        // 'orujo'
        $firstDestination = array_key_first(WineSubproduct::DESTINATIONS); // 'distillery'

        Livewire::test(Create::class)
            ->set('type', $firstType)
            ->set('subproduct_date', today()->toDateString())
            ->set('quantity', '100')
            ->set('destination', $firstDestination)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_subproducts', [
            'user_id' => $this->winery->id,
            'type' => $firstType,
        ]);
    }

    public function test_edit_saves_changes(): void
    {
        $firstType = array_key_first(WineSubproduct::TYPES);
        $firstDestination = array_key_first(WineSubproduct::DESTINATIONS);

        $subproduct = WineSubproduct::create([
            'user_id' => $this->winery->id,
            'type' => $firstType,
            'subproduct_date' => today(),
            'quantity' => 100,
            'destination' => $firstDestination,
            'created_by' => $this->winery->id,
        ]);

        Livewire::test(Edit::class, ['subproduct' => $subproduct])
            ->set('quantity', '200')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_subproducts', [
            'id' => $subproduct->id,
            'quantity' => 200,
        ]);
    }

    public function test_create_rejects_wine_from_other_winery(): void
    {
        $otherWine = Wine::create([
            'user_id' => $this->makeOtherWinery()->id,
            'name' => 'Other Wine',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ]);

        $firstType = array_key_first(WineSubproduct::TYPES);
        $firstDestination = array_key_first(WineSubproduct::DESTINATIONS);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $otherWine->id)
            ->set('type', $firstType)
            ->set('subproduct_date', today()->toDateString())
            ->set('quantity', '100')
            ->set('destination', $firstDestination)
            ->call('save')
            ->assertHasErrors(['wine_id']);
    }

    public function test_other_winery_cannot_edit(): void
    {
        $firstType = array_key_first(WineSubproduct::TYPES);
        $firstDestination = array_key_first(WineSubproduct::DESTINATIONS);

        $subproduct = WineSubproduct::create([
            'user_id' => $this->winery->id,
            'type' => $firstType,
            'subproduct_date' => today(),
            'quantity' => 100,
            'destination' => $firstDestination,
            'created_by' => $this->winery->id,
        ]);

        $otherWinery = $this->makeOtherWinery();

        $this->actingAs($otherWinery)
            ->get(route('winery.subproducts.edit', $subproduct))
            ->assertForbidden();
    }
}
