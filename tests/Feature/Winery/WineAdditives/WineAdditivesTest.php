<?php

namespace Tests\Feature\Winery\WineAdditives;

use App\Livewire\Winery\WineAdditives\Index;
use App\Models\User;
use App\Models\Wine;
use App\Models\WineAdditive;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class WineAdditivesTest extends WineryTestCase
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
        $this->get(route('winery.wine-additives.index'))->assertOk();
    }

    public function test_index_shows_only_own_additives(): void
    {
        $other = $this->makeOtherWinery();

        $ownWine = Wine::create([
            'user_id' => $this->winery->id,
            'name' => 'Mi Vino',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ]);

        WineAdditive::create([
            'wine_id' => $ownWine->id,
            'additive_name' => 'Aditivo Propio',
            'quantity' => 10,
            'application_date' => now()->toDateString(),
            'created_by' => $this->winery->id,
        ]);

        $otherWine = Wine::create([
            'user_id' => $other->id,
            'name' => 'Vino Ajeno',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ]);

        WineAdditive::create([
            'wine_id' => $otherWine->id,
            'additive_name' => 'Aditivo Ajeno',
            'quantity' => 5,
            'application_date' => now()->toDateString(),
            'created_by' => $other->id,
        ]);

        Livewire::test(Index::class)
            ->assertSee('Aditivo Propio')
            ->assertDontSee('Aditivo Ajeno');
    }

    public function test_cannot_delete_other_winery_additive(): void
    {
        $other = $this->makeOtherWinery();

        $otherWine = Wine::create([
            'user_id' => $other->id,
            'name' => 'Vino Ajeno',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ]);

        $additive = WineAdditive::create([
            'wine_id' => $otherWine->id,
            'additive_name' => 'Aditivo Protegido',
            'quantity' => 5,
            'application_date' => now()->toDateString(),
            'created_by' => $other->id,
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('delete', $additive->id);
    }
}
