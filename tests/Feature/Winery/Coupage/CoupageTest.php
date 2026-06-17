<?php

namespace Tests\Feature\Winery\Coupage;

use App\Livewire\Winery\Coupage\Create;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class CoupageTest extends WineryTestCase
{
    private \App\Models\User $winery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    public function test_index_renders(): void
    {
        $this->get(route('winery.coupage.index'))->assertOk();
    }

    public function test_create_renders(): void
    {
        $this->get(route('winery.coupage.create'))->assertOk();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('target_wine_id', '')
            ->set('to_container_id', '')
            ->set('coupage_date', '')
            ->call('save')
            ->assertHasErrors(['target_wine_id', 'to_container_id', 'coupage_date']);
    }

    public function test_create_rejects_wine_from_other_winery(): void
    {
        $otherWinery = $this->makeOtherWinery();
        $foreignWine = Wine::create([
            'user_id' => $otherWinery->id,
            'name' => 'Foreign Wine',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ]);

        $unit = UnitOfMeasurement::first() ?? UnitOfMeasurement::create([
            'name' => 'Litro',
            'symbol' => 'L',
            'category' => 'volume',
        ]);

        Livewire::test(Create::class)
            ->set('target_wine_id', (string) $foreignWine->id)
            ->set('coupage_date', now()->toDateString())
            ->set('unit_of_measurement_id', (string) $unit->id)
            ->call('save')
            ->assertHasErrors(['target_wine_id']);
    }
}
