<?php

namespace Tests\Feature\Winery\Wines;

use App\Livewire\Winery\Wines\Create;
use App\Livewire\Winery\Wines\Edit;
use App\Models\User;
use App\Models\Wine;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class WinesTest extends WineryTestCase
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
        $this->get(route('winery.wines.index'))->assertOk();
    }

    public function test_create_validates_required_fields(): void
    {
        // name is required; wine_type and status have defaults so we blank them first
        Livewire::test(Create::class)
            ->set('name', '')
            ->set('wine_type', '')
            ->set('status', '')
            ->call('save')
            ->assertHasErrors(['name', 'wine_type', 'status']);
    }

    public function test_create_saves_wine(): void
    {
        $firstType = array_key_first(Wine::WINE_TYPES);   // 'red'
        $firstStatus = array_key_first(Wine::STATUSES);     // 'in_progress'

        Livewire::test(Create::class)
            ->set('name', 'Tempranillo 2024')
            ->set('wine_type', $firstType)
            ->set('status', $firstStatus)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wines', [
            'user_id' => $this->winery->id,
            'name' => 'Tempranillo 2024',
        ]);
    }

    public function test_edit_saves_changes(): void
    {
        $wine = Wine::create([
            'user_id' => $this->winery->id,
            'name' => 'Old Name',
            'wine_type' => array_key_first(Wine::WINE_TYPES),
            'status' => array_key_first(Wine::STATUSES),
        ]);

        Livewire::test(Edit::class, ['wine' => $wine])
            ->set('name', 'New Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wines', [
            'id' => $wine->id,
            'name' => 'New Name',
        ]);
    }

    public function test_other_winery_cannot_edit(): void
    {
        $wine = Wine::create([
            'user_id' => $this->winery->id,
            'name' => 'Protected Wine',
            'wine_type' => array_key_first(Wine::WINE_TYPES),
            'status' => array_key_first(Wine::STATUSES),
        ]);

        $otherWinery = $this->makeOtherWinery();

        $this->actingAs($otherWinery)
            ->get(route('winery.wines.edit', $wine))
            ->assertForbidden();
    }
}
