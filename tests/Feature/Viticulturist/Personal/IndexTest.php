<?php

namespace Tests\Feature\Viticulturist\Personal;

use App\Livewire\Viticulturist\Personal\Index;
use App\Models\Crew;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    public function test_lists_own_crews(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();

        Crew::create(['name' => 'Mía', 'viticulturist_id' => $viticulturist->id]);
        Crew::create(['name' => 'Ajena', 'viticulturist_id' => $other->id]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertSee('Mía')
            ->assertDontSee('Ajena');
    }

    public function test_search_filters_by_name(): void
    {
        $viticulturist = $this->makeViticulturist();
        Crew::create(['name' => 'Vendimiadores', 'viticulturist_id' => $viticulturist->id]);
        Crew::create(['name' => 'Podadores', 'viticulturist_id' => $viticulturist->id]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('search', 'Vendimia')
            ->assertSee('Vendimiadores')
            ->assertDontSee('Podadores');
    }

    public function test_delete_own_crew(): void
    {
        $viticulturist = $this->makeViticulturist();
        $crew = Crew::create(['name' => 'Para borrar', 'viticulturist_id' => $viticulturist->id]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('delete', $crew);

        $this->assertDatabaseMissing('crews', ['id' => $crew->id]);
    }

    public function test_cannot_delete_other_viticulturist_crew(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $crew = Crew::create(['name' => 'Ajena', 'viticulturist_id' => $other->id]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('delete', $crew);

        $this->assertDatabaseHas('crews', ['id' => $crew->id]);
    }

    public function test_clear_filters_resets_search_and_winery(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('search', 'algo')
            ->set('wineryFilter', '5')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('wineryFilter', '');
    }
}
