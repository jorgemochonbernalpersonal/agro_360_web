<?php

namespace Tests\Feature\Winery\ProductSheets;

use App\Livewire\Winery\ProductSheets\Index;
use App\Models\Wine;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class IndexTest extends WineryTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_winery_can_access_product_sheets_index(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('winery.product-sheets.index'))
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

    public function test_shows_own_wines(): void
    {
        $winery = $this->makeWinery();
        $this->makeWine($winery->id, ['name' => 'FichaPropia']);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertSee('FichaPropia');
    }

    public function test_does_not_show_other_winery_wines(): void
    {
        $winery1 = $this->makeWinery();
        $winery2 = $this->makeOtherWinery();
        $this->makeWine($winery2->id, ['name' => 'FichaAjena']);

        Livewire::actingAs($winery1)
            ->test(Index::class)
            ->assertDontSee('FichaAjena');
    }

    public function test_cancelled_wines_excluded_by_default(): void
    {
        $winery = $this->makeWinery();
        $this->makeWine($winery->id, ['name' => 'VinoCancelado', 'status' => 'cancelled']);
        $this->makeWine($winery->id, ['name' => 'VinoActivo',    'status' => 'in_progress']);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertSee('VinoActivo')
            ->assertDontSee('VinoCancelado');
    }

    // ── filtros ───────────────────────────────────────────────────────────────

    public function test_filter_by_wine_type(): void
    {
        $winery = $this->makeWinery();
        $this->makeWine($winery->id, ['name' => 'TintoFiltro',  'wine_type' => 'red']);
        $this->makeWine($winery->id, ['name' => 'BlancoFiltro', 'wine_type' => 'white']);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('typeFilter', 'red')
            ->assertSee('TintoFiltro')
            ->assertDontSee('BlancoFiltro');
    }

    public function test_filter_by_status(): void
    {
        $winery = $this->makeWinery();
        $this->makeWine($winery->id, ['name' => 'VinoCrianza',   'status' => 'aged']);
        $this->makeWine($winery->id, ['name' => 'VinoEnProceso', 'status' => 'in_progress']);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('statusFilter', 'aged')
            ->assertSee('VinoCrianza')
            ->assertDontSee('VinoEnProceso');
    }

    public function test_search_by_name(): void
    {
        $winery = $this->makeWinery();
        $this->makeWine($winery->id, ['name' => 'Tempranillo Selección']);
        $this->makeWine($winery->id, ['name' => 'Garnacha Clásica']);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('search', 'Selección')
            ->assertSee('Tempranillo Selección')
            ->assertDontSee('Garnacha Clásica');
    }

    // ── stats ─────────────────────────────────────────────────────────────────

    public function test_stats_count_by_status(): void
    {
        $winery = $this->makeWinery();
        $this->makeWine($winery->id, ['status' => 'in_progress']);
        $this->makeWine($winery->id, ['status' => 'aged']);
        $this->makeWine($winery->id, ['status' => 'bottled']);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertViewHas('stats', fn ($s) => $s['total'] === 3 && $s['active'] === 2 && $s['bottled'] === 1
            );
    }

    public function test_stats_total_excludes_other_winery_wines(): void
    {
        $winery1 = $this->makeWinery();
        $winery2 = $this->makeOtherWinery();
        $this->makeWine($winery1->id);
        $this->makeWine($winery2->id);

        Livewire::actingAs($winery1)
            ->test(Index::class)
            ->assertViewHas('stats', fn ($s) => $s['total'] === 1);
    }
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeWine(int $wineryId, array $attrs = []): Wine
    {
        return Wine::create(array_merge([
            'user_id' => $wineryId,
            'name' => 'Vino Test',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ], $attrs));
    }
}
