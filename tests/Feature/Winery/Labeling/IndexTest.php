<?php

namespace Tests\Feature\Winery\Labeling;

use App\Livewire\Winery\Labeling\Index;
use App\Models\Wine;
use App\Models\WineLabeling;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class IndexTest extends WineryTestCase
{
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeWine(int $wineryId, array $attrs = []): Wine
    {
        return Wine::create(array_merge([
            'user_id'   => $wineryId,
            'name'      => 'Vino Test',
            'wine_type' => 'red',
            'status'    => 'bottled',
        ], $attrs));
    }

    private function makeLabeling(int $wineryId, Wine $wine, array $attrs = []): WineLabeling
    {
        return WineLabeling::create(array_merge([
            'user_id'          => $wineryId,
            'wine_id'          => $wine->id,
            'labeling_date'    => now()->format('Y-m-d'),
            'quantity_labeled' => 100,
            'created_by'       => $wineryId,
        ], $attrs));
    }

    // ── access ────────────────────────────────────────────────────────────────

    public function test_winery_can_access_labeling_index(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('winery.labeling.index'))
            ->assertOk();
    }

    public function test_component_renders(): void
    {
        $winery = $this->makeWinery();

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertOk();
    }

    // ── visibilidad ───────────────────────────────────────────────────────────

    public function test_shows_own_labelings(): void
    {
        $winery   = $this->makeWinery();
        $wine     = $this->makeWine($winery->id, ['name' => 'TempranilloEtiquetado']);
        $this->makeLabeling($winery->id, $wine);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertSee('TempranilloEtiquetado');
    }

    public function test_does_not_show_other_winery_labelings(): void
    {
        $winery1 = $this->makeWinery();
        $winery2 = $this->makeOtherWinery();
        $wine2   = $this->makeWine($winery2->id, ['name' => 'VinoAjeno']);
        $this->makeLabeling($winery2->id, $wine2);

        Livewire::actingAs($winery1)
            ->test(Index::class)
            ->assertDontSee('VinoAjeno');
    }

    // ── eliminar ──────────────────────────────────────────────────────────────

    public function test_winery_can_delete_own_labeling(): void
    {
        $winery   = $this->makeWinery();
        $wine     = $this->makeWine($winery->id);
        $labeling = $this->makeLabeling($winery->id, $wine);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->call('delete', $labeling->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('wine_labelings', ['id' => $labeling->id]);
    }

    public function test_cannot_delete_other_winery_labeling(): void
    {
        $winery1  = $this->makeWinery();
        $winery2  = $this->makeOtherWinery();
        $wine2    = $this->makeWine($winery2->id);
        $labeling = $this->makeLabeling($winery2->id, $wine2);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($winery1)
            ->test(Index::class)
            ->call('delete', $labeling->id);
    }

    // ── filtros ───────────────────────────────────────────────────────────────

    public function test_filter_by_wine(): void
    {
        $winery = $this->makeWinery();
        $wine1  = $this->makeWine($winery->id, ['name' => 'TintoFiltro']);
        $wine2  = $this->makeWine($winery->id, ['name' => 'BlancoFiltro']);
        $lab1   = $this->makeLabeling($winery->id, $wine1);
        $this->makeLabeling($winery->id, $wine2);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('wineFilter', (string) $wine1->id)
            ->assertViewHas('labelings', fn($l) =>
                $l->count() === 1 && $l->first()->id === $lab1->id
            );
    }

    public function test_search_by_wine_name(): void
    {
        $winery = $this->makeWinery();
        $wine1  = $this->makeWine($winery->id, ['name' => 'RiojaGranReserva']);
        $wine2  = $this->makeWine($winery->id, ['name' => 'RiberaJoven']);
        $lab1   = $this->makeLabeling($winery->id, $wine1);
        $this->makeLabeling($winery->id, $wine2);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('search', 'GranReserva')
            ->assertViewHas('labelings', fn($l) =>
                $l->count() === 1 && $l->first()->id === $lab1->id
            );
    }
}
