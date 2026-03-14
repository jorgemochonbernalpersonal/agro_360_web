<?php

namespace Tests\Feature\Winery\Bottling;

use App\Livewire\Winery\Bottling\Create;
use App\Livewire\Winery\Bottling\Index;
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

    public function test_other_winery_cannot_edit(): void
    {
        $otherWinery = $this->makeOtherWinery();

        $wine = Wine::create([
            'user_id'   => $this->winery->id,
            'name'      => 'Test Wine',
            'wine_type' => 'red',
            'status'    => 'in_progress',
        ]);

        $bottling = WineBottling::create([
            'user_id'          => $this->winery->id,
            'wine_id'          => $wine->id,
            'bottling_date'    => today(),
            'bottle_format'    => '750',
            'quantity_bottles' => 100,
            'quantity_liters'  => 75,
            'lot_number'       => 'LOT-001',
            'created_by'       => $this->winery->id,
        ]);

        $this->actingAs($otherWinery)
            ->get(route('winery.bottling.edit', $bottling))
            ->assertForbidden();
    }

    public function test_index_shows_only_own_bottlings(): void
    {
        $otherWinery = $this->makeOtherWinery();

        $ownWine = Wine::create([
            'user_id'   => $this->winery->id,
            'name'      => 'Own Wine',
            'wine_type' => 'red',
            'status'    => 'in_progress',
        ]);

        WineBottling::create([
            'user_id'          => $this->winery->id,
            'wine_id'          => $ownWine->id,
            'bottling_date'    => today(),
            'bottle_format'    => '750',
            'quantity_bottles' => 100,
            'quantity_liters'  => 75,
            'lot_number'       => 'MY-LOT-001',
            'created_by'       => $this->winery->id,
        ]);

        $otherWine = Wine::create([
            'user_id'   => $otherWinery->id,
            'name'      => 'Other Wine',
            'wine_type' => 'red',
            'status'    => 'in_progress',
        ]);

        WineBottling::create([
            'user_id'          => $otherWinery->id,
            'wine_id'          => $otherWine->id,
            'bottling_date'    => today(),
            'bottle_format'    => '750',
            'quantity_bottles' => 50,
            'quantity_liters'  => 37.5,
            'lot_number'       => 'OTHER-LOT-001',
            'created_by'       => $otherWinery->id,
        ]);

        Livewire::test(Index::class)
            ->assertSee('MY-LOT-001')
            ->assertDontSee('OTHER-LOT-001');
    }
}
