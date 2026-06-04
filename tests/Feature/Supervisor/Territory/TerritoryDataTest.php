<?php

namespace Tests\Feature\Supervisor\Territory;

use App\Livewire\Supervisor\Territory\Index;
use App\Models\GrapeVariety;
use App\Models\PlotPlanting;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class TerritoryDataTest extends SupervisorTestCase
{
    // ── setTab ────────────────────────────────────────────────────────────────

    public function test_set_tab_switches_active_tab(): void
    {
        [$supervisor] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertSet('activeTab', 'provinces')
            ->call('setTab', 'varieties')
            ->assertSet('activeTab', 'varieties');
    }

    // ── byProvince ────────────────────────────────────────────────────────────

    public function test_by_province_groups_plots_with_area_and_count(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForTerritory($supervisor, $winery);

        $this->makePlot($vit, ['area' => 3.0, 'active' => true]);
        $this->makePlot($vit, ['area' => 2.0, 'active' => true]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('byProvince', function ($rows) {
                $row = $rows->first();

                return $row !== null
                    && $row->plot_count === 2
                    && abs($row->total_area - 5.0) < 0.01;
            });
    }

    public function test_by_province_excludes_inactive_plots(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForTerritory($supervisor, $winery);

        $this->makePlot($vit, ['area' => 5.0, 'active' => false]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('byProvince', fn ($rows) => $rows->isEmpty());
    }

    public function test_by_province_isolated_from_other_supervisor(): void
    {
        [$supervisor] = $this->makeSupervisorWithWinery();
        [$otherSupervisor, $otherWinery] = $this->makeSupervisorWithWinery();
        $otherVit = $this->makeViticulturistForTerritory($otherSupervisor, $otherWinery);
        $this->makePlot($otherVit, ['area' => 10.0, 'active' => true]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('byProvince', fn ($rows) => $rows->isEmpty());
    }

    // ── organicPlots ──────────────────────────────────────────────────────────

    public function test_organic_plots_count_only_organic(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForTerritory($supervisor, $winery);

        $this->makePlot($vit, ['active' => true, 'is_organic' => true]);
        $this->makePlot($vit, ['active' => true, 'is_organic' => true]);
        $this->makePlot($vit, ['active' => true, 'is_organic' => false]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('organicPlots', 2);
    }

    // ── totalArea / totalPlots ────────────────────────────────────────────────

    public function test_total_area_sums_all_active_plots(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForTerritory($supervisor, $winery);

        $this->makePlot($vit, ['area' => 1.5, 'active' => true]);
        $this->makePlot($vit, ['area' => 2.5, 'active' => true]);
        $this->makePlot($vit, ['area' => 9.0, 'active' => false]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('totalArea', fn ($v) => abs($v - 4.0) < 0.01)
            ->assertViewHas('totalPlots', 2);
    }

    // ── byVariety ─────────────────────────────────────────────────────────────

    public function test_by_variety_groups_planted_area_per_grape_variety(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForTerritory($supervisor, $winery);
        $plot = $this->makePlot($vit, ['active' => true]);

        $tempranillo = $this->makeVariety('Tempranillo', 'red');
        $albarin = $this->makeVariety('Albariño', 'white');

        PlotPlanting::factory()->create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $tempranillo->id,
            'area_planted' => 2.0,
            'status' => 'active',
        ]);

        PlotPlanting::factory()->create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $albarin->id,
            'area_planted' => 1.5,
            'status' => 'active',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('byVariety', function ($rows) {
                $temp = $rows->firstWhere('variety_name', 'Tempranillo');
                $alb = $rows->firstWhere('variety_name', 'Albariño');

                return $temp !== null && abs($temp->planted_area - 2.0) < 0.01
                    && $alb !== null && abs($alb->planted_area - 1.5) < 0.01;
            });
    }

    public function test_by_variety_excludes_removed_plantings(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForTerritory($supervisor, $winery);
        $plot = $this->makePlot($vit, ['active' => true]);

        $variety = $this->makeVariety('Monastrell', 'red');

        PlotPlanting::factory()->create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $variety->id,
            'area_planted' => 3.0,
            'status' => 'removed',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('byVariety', fn ($rows) => $rows->isEmpty());
    }

    public function test_by_variety_isolated_from_other_supervisor(): void
    {
        [$supervisor] = $this->makeSupervisorWithWinery();
        [$otherSupervisor, $otherWinery] = $this->makeSupervisorWithWinery();

        $otherVit = $this->makeViticulturistForTerritory($otherSupervisor, $otherWinery);
        $otherPlot = $this->makePlot($otherVit, ['active' => true]);
        $variety = $this->makeVariety('Garnacha', 'red');

        PlotPlanting::factory()->create([
            'plot_id' => $otherPlot->id,
            'grape_variety_id' => $variety->id,
            'area_planted' => 5.0,
            'status' => 'active',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('byVariety', fn ($rows) => $rows->isEmpty());
    }

    // ── byMunicipality ────────────────────────────────────────────────────────

    public function test_by_municipality_groups_plots_per_municipality(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForTerritory($supervisor, $winery);

        $this->makePlot($vit, ['area' => 1.0, 'active' => true]);
        $this->makePlot($vit, ['area' => 2.0, 'active' => true]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('byMunicipality', function ($rows) {
                $row = $rows->first();

                return $row !== null
                    && $row->plot_count === 2
                    && abs($row->total_area - 3.0) < 0.01;
            });
    }

    // ── tabs count ────────────────────────────────────────────────────────────

    public function test_tabs_counts_reflect_groups(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForTerritory($supervisor, $winery);

        $this->makePlot($vit, ['active' => true]);

        $variety = $this->makeVariety('Verdejo', 'white');
        $plot = $this->makePlot($vit, ['active' => true]);
        PlotPlanting::factory()->create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $variety->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('tabs', function ($tabs) {
                return $tabs['provinces']['count'] >= 1
                    && $tabs['varieties']['count'] >= 1
                    && $tabs['municipalities']['count'] >= 1;
            });
    }
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeViticulturistForTerritory(User $supervisor, User $winery): User
    {
        $vit = User::factory()->create(['role' => 'viticulturist']);

        SupervisorViticulturist::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $vit->id,
            'assigned_by' => $supervisor->id,
        ]);

        return $vit;
    }

    private function makeVariety(string $name, string $color = 'red'): GrapeVariety
    {
        return GrapeVariety::firstOrCreate(
            ['name' => $name],
            ['color' => $color, 'active' => true]
        );
    }
}
