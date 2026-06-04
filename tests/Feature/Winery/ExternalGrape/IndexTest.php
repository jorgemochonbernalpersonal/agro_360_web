<?php

namespace Tests\Feature\Winery\ExternalGrape;

use App\Livewire\Winery\ExternalGrape\Index;
use App\Models\ExternalGrape;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class IndexTest extends WineryTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_winery_can_access_external_grape_index(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('winery.external-grape.index'))
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

    public function test_shows_own_external_grapes(): void
    {
        $winery = $this->makeWinery();
        $this->makeGrape($winery->id, ['supplier_name' => 'ProveedorVisible']);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertSee('ProveedorVisible');
    }

    public function test_does_not_show_other_winery_grapes(): void
    {
        $winery1 = $this->makeWinery();
        $winery2 = $this->makeOtherWinery();
        $this->makeGrape($winery2->id, ['supplier_name' => 'ProveedorAjeno']);

        Livewire::actingAs($winery1)
            ->test(Index::class)
            ->assertDontSee('ProveedorAjeno');
    }

    // ── archivar ──────────────────────────────────────────────────────────────

    public function test_winery_can_archive_own_grape(): void
    {
        $winery = $this->makeWinery();
        $grape = $this->makeGrape($winery->id);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->call('archive', $grape->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('external_grapes', [
            'id' => $grape->id,
            'status' => 'archived',
        ]);
    }

    public function test_cannot_archive_other_winery_grape(): void
    {
        $winery1 = $this->makeWinery();
        $winery2 = $this->makeOtherWinery();
        $grape = $this->makeGrape($winery2->id);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($winery1)
            ->test(Index::class)
            ->call('archive', $grape->id);
    }

    // ── filtros ───────────────────────────────────────────────────────────────

    public function test_filter_by_status(): void
    {
        $winery = $this->makeWinery();
        $this->makeGrape($winery->id, ['supplier_name' => 'ProvDisponible', 'status' => 'available']);
        $this->makeGrape($winery->id, ['supplier_name' => 'ProvUsado',      'status' => 'used']);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('statusFilter', 'available')
            ->assertSee('ProvDisponible')
            ->assertDontSee('ProvUsado');
    }

    public function test_filter_by_grape_type(): void
    {
        $winery = $this->makeWinery();
        $this->makeGrape($winery->id, ['supplier_name' => 'ProvUva',    'grape_type' => 'grapes']);
        $this->makeGrape($winery->id, ['supplier_name' => 'ProvMosto',  'grape_type' => 'must']);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('typeFilter', 'grapes')
            ->assertSee('ProvUva')
            ->assertDontSee('ProvMosto');
    }

    public function test_filter_by_vintage(): void
    {
        $winery = $this->makeWinery();
        $this->makeGrape($winery->id, ['supplier_name' => 'Prov2024', 'vintage_year' => 2024]);
        $this->makeGrape($winery->id, ['supplier_name' => 'Prov2023', 'vintage_year' => 2023]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('vintageFilter', '2024')
            ->assertSee('Prov2024')
            ->assertDontSee('Prov2023');
    }

    public function test_search_by_supplier_name(): void
    {
        $winery = $this->makeWinery();
        $this->makeGrape($winery->id, ['supplier_name' => 'ProveedorBuscar']);
        $this->makeGrape($winery->id, ['supplier_name' => 'ProveedorOtro']);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('search', 'Buscar')
            ->assertSee('ProveedorBuscar')
            ->assertDontSee('ProveedorOtro');
    }

    public function test_clear_filters_resets_all(): void
    {
        $winery = $this->makeWinery();

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('typeFilter', 'grapes')
            ->set('statusFilter', 'available')
            ->set('colorFilter', 'red')
            ->call('clearFilters')
            ->assertSet('typeFilter', '')
            ->assertSet('statusFilter', '')
            ->assertSet('colorFilter', '');
    }

    // ── stats ─────────────────────────────────────────────────────────────────

    public function test_stats_reflect_available_grapes(): void
    {
        $winery = $this->makeWinery();
        $this->makeGrape($winery->id, ['total_weight_kg' => 500, 'used_weight_kg' => 100, 'status' => 'available']);
        $this->makeGrape($winery->id, ['total_weight_kg' => 300, 'used_weight_kg' => 0, 'status' => 'available']);
        $this->makeGrape($winery->id, ['total_weight_kg' => 200, 'used_weight_kg' => 0, 'status' => 'used']);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertViewHas('stats', fn ($s) => $s['partidas'] === 2   // only available
                && $s['total_kg'] == 800.0
            );
    }
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeGrape(int $wineryId, array $attrs = []): ExternalGrape
    {
        return ExternalGrape::create(array_merge([
            'user_id' => $wineryId,
            'supplier_name' => 'Proveedor Test',
            'grape_type' => 'grapes',
            'color' => 'red',
            'total_weight_kg' => 1000,
            'used_weight_kg' => 0,
            'entry_date' => now()->format('Y-m-d'),
            'status' => 'available',
        ], $attrs));
    }
}
