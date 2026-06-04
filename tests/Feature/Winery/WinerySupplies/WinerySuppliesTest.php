<?php

namespace Tests\Feature\Winery\WinerySupplies;

use App\Livewire\Winery\WinerySupplies\Create;
use App\Livewire\Winery\WinerySupplies\Edit;
use App\Models\User;
use App\Models\WinerySupply;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class WinerySuppliesTest extends WineryTestCase
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
        $this->get(route('winery.winery-supplies.index'))->assertOk();
    }

    public function test_create_validates_required_fields(): void
    {
        // supply_type has a default ('other') so we blank it to force an error
        Livewire::test(Create::class)
            ->set('name', '')
            ->set('supply_type', '')
            ->call('save')
            ->assertHasErrors(['name', 'supply_type']);
    }

    public function test_create_saves_supply(): void
    {
        $firstType = array_key_first(WinerySupply::SUPPLY_TYPES); // 'cleaning'

        Livewire::test(Create::class)
            ->set('name', 'Sulfuroso')
            ->set('supply_type', $firstType)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('winery_supplies', [
            'user_id' => $this->winery->id,
            'name' => 'Sulfuroso',
        ]);
    }

    public function test_edit_saves_changes(): void
    {
        $supply = WinerySupply::create([
            'user_id' => $this->winery->id,
            'name' => 'Test Supply',
            'supply_type' => array_key_first(WinerySupply::SUPPLY_TYPES),
            'active' => true,
        ]);

        Livewire::test(Edit::class, ['winerySupply' => $supply])
            ->set('name', 'Updated')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('winery_supplies', [
            'id' => $supply->id,
            'name' => 'Updated',
        ]);
    }

    public function test_other_winery_cannot_edit(): void
    {
        $supply = WinerySupply::create([
            'user_id' => $this->winery->id,
            'name' => 'Protected Supply',
            'supply_type' => array_key_first(WinerySupply::SUPPLY_TYPES),
            'active' => true,
        ]);

        $otherWinery = $this->makeOtherWinery();

        $this->actingAs($otherWinery)
            ->get(route('winery.winery-supplies.edit', $supply))
            ->assertForbidden();
    }
}
