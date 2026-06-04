<?php

namespace Tests\Feature\Winery\FermentationControls;

use App\Livewire\Winery\FermentationControls\Index;
use App\Models\Container;
use App\Models\Wine;
use App\Models\WineFermentationControl;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class IndexTest extends WineryTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_winery_can_access_fermentation_controls_index(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('winery.fermentation-controls.index'))
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

    public function test_shows_own_fermentation_controls(): void
    {
        $winery = $this->makeWinery();
        $wine = $this->makeWine($winery->id, ['name' => 'Garnacha Especial']);
        $this->makeControl($wine);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertSee('Garnacha Especial');
    }

    public function test_does_not_show_other_winery_controls(): void
    {
        $winery1 = $this->makeWinery();
        $winery2 = $this->makeOtherWinery();
        $wine2 = $this->makeWine($winery2->id, ['name' => 'Vino Ajeno']);
        $this->makeControl($wine2);

        Livewire::actingAs($winery1)
            ->test(Index::class)
            ->assertDontSee('Vino Ajeno');
    }

    // ── eliminar ──────────────────────────────────────────────────────────────

    public function test_winery_can_delete_own_control(): void
    {
        $winery = $this->makeWinery();
        $wine = $this->makeWine($winery->id);
        $control = $this->makeControl($wine);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->call('delete', $control->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('wine_fermentation_controls', ['id' => $control->id]);
    }

    public function test_cannot_delete_other_winery_control(): void
    {
        $winery1 = $this->makeWinery();
        $winery2 = $this->makeOtherWinery();
        $wine2 = $this->makeWine($winery2->id);
        $control = $this->makeControl($wine2);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($winery1)
            ->test(Index::class)
            ->call('delete', $control->id);
    }

    // ── filtros ───────────────────────────────────────────────────────────────

    public function test_filter_by_wine(): void
    {
        $winery = $this->makeWinery();
        $wine1 = $this->makeWine($winery->id, ['name' => 'TempranilloA']);
        $wine2 = $this->makeWine($winery->id, ['name' => 'GarnachaB']);
        $ctrl1 = $this->makeControl($wine1);
        $this->makeControl($wine2);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('wineFilter', (string) $wine1->id)
            ->assertViewHas('controls', fn ($c) => $c->count() === 1 && $c->first()->id === $ctrl1->id
            );
    }

    public function test_filter_fermenting_status(): void
    {
        $winery = $this->makeWinery();
        $wine1 = $this->makeWine($winery->id);
        $ctrl1 = $this->makeControl($wine1, ['density' => 1.050]); // fermenting
        $wine2 = $this->makeWine($winery->id);
        $this->makeControl($wine2, ['density' => 0.995]); // done

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('statusFilter', 'fermenting')
            ->assertViewHas('controls', fn ($c) => $c->count() === 1 && $c->first()->id === $ctrl1->id
            );
    }

    // ── stats ─────────────────────────────────────────────────────────────────

    public function test_stats_total_reflects_own_controls(): void
    {
        $winery = $this->makeWinery();
        $wine = $this->makeWine($winery->id);
        $this->makeControl($wine);
        $this->makeControl($wine);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertViewHas('stats', fn ($s) => $s['total'] === 2);
    }

    public function test_stats_fermenting_count(): void
    {
        $winery = $this->makeWinery();
        $wine = $this->makeWine($winery->id);
        $this->makeControl($wine, ['density' => 1.060]); // fermenting
        $this->makeControl($wine, ['density' => 0.990]); // done

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertViewHas('stats', fn ($s) => $s['fermenting'] === 1);
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

    private function makeContainer(int $wineryId): Container
    {
        return Container::create([
            'user_id' => $wineryId,
            'name' => 'Depósito Test',
            'capacity' => 1000,
        ]);
    }

    private function makeControl(Wine $wine, array $attrs = []): WineFermentationControl
    {
        $containerId = $attrs['container_id'] ?? $this->makeContainer($wine->user_id)->id;

        return WineFermentationControl::create(array_merge([
            'wine_id' => $wine->id,
            'container_id' => $containerId,
            'control_date' => now()->format('Y-m-d H:i:s'),
        ], $attrs));
    }
}
