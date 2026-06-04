<?php

namespace Tests\Feature\Winery\Traceability;

use App\Livewire\Winery\Traceability\Index;
use App\Models\Wine;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class IndexTest extends WineryTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_winery_can_access_traceability_index(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('winery.traceability.index'))
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
        $this->makeWine($winery->id, ['name' => 'Ribera Especial 2024']);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertSee('Ribera Especial 2024');
    }

    public function test_does_not_show_other_winery_wines(): void
    {
        $winery1 = $this->makeWinery();
        $winery2 = $this->makeOtherWinery();
        $this->makeWine($winery2->id, ['name' => 'Vino Ajeno 2024']);

        Livewire::actingAs($winery1)
            ->test(Index::class)
            ->assertDontSee('Vino Ajeno 2024');
    }

    // ── filtros ───────────────────────────────────────────────────────────────

    public function test_filter_by_vintage(): void
    {
        $winery = $this->makeWinery();
        $this->makeWine($winery->id, ['name' => 'Vino2023', 'vintage' => 2023]);
        $this->makeWine($winery->id, ['name' => 'Vino2022', 'vintage' => 2022]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('vintageFilter', '2023')
            ->assertSee('Vino2023')
            ->assertDontSee('Vino2022');
    }

    public function test_filter_by_status(): void
    {
        $winery = $this->makeWinery();
        $this->makeWine($winery->id, ['name' => 'VinoBotellado', 'status' => 'bottled']);
        $this->makeWine($winery->id, ['name' => 'VinoEnProceso', 'status' => 'in_progress']);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('statusFilter', 'bottled')
            ->assertSee('VinoBotellado')
            ->assertDontSee('VinoEnProceso');
    }

    public function test_search_by_name(): void
    {
        $winery = $this->makeWinery();
        $this->makeWine($winery->id, ['name' => 'Tinto Reserva Especial']);
        $this->makeWine($winery->id, ['name' => 'Blanco Joven']);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('search', 'Reserva')
            ->assertSee('Tinto Reserva Especial')
            ->assertDontSee('Blanco Joven');
    }

    // ── vintages dropdown ─────────────────────────────────────────────────────

    public function test_vintages_list_contains_own_wine_years(): void
    {
        $winery = $this->makeWinery();
        $this->makeWine($winery->id, ['vintage' => 2024]);
        $this->makeWine($winery->id, ['vintage' => 2023]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertViewHas('vintages', fn ($v) => $v->contains(2024) && $v->contains(2023));
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
