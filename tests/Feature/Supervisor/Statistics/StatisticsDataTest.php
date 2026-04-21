<?php

namespace Tests\Feature\Supervisor\Statistics;

use App\Livewire\Supervisor\Statistics\Index;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class StatisticsDataTest extends SupervisorTestCase
{
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeViticulturistForStats(User $supervisor, User $winery): User
    {
        $vit = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'supervisor_id'    => $supervisor->id,
            'winery_id'        => $winery->id,
            'viticulturist_id' => $vit->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'assigned_by'      => $supervisor->id,
        ]);

        return $vit;
    }

    private function makeHarvest(User $winery, array $attrs = []): void
    {
        $activityId = DB::table('agricultural_activities')->insertGetId([
            'viticulturist_id' => $winery->id,
            'activity_type'    => 'observation',
            'activity_date'    => now()->format('Y-m-d'),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        DB::table('harvests')->insert(array_merge([
            'activity_id'        => $activityId,
            'winery_id'          => $winery->id,
            'harvest_start_date' => now()->format('Y-m-d'),
            'total_weight'       => 1000,
            'brix_degree'        => 22.0,
            'vintage'            => now()->year,
            'status'             => 'active',
            'created_at'         => now(),
            'updated_at'         => now(),
        ], $attrs));
    }

    // ── totals ────────────────────────────────────────────────────────────────

    public function test_total_wineries_counts_linked_wineries(): void
    {
        [$supervisor] = $this->makeSupervisorWithWinery();
        $winery2      = $this->makeWinery();

        \App\Models\SupervisorWinery::create([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery2->id,
            'assigned_by'   => $supervisor->id,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('totalWineries', 2);
    }

    public function test_total_viticulturists_counts_supervisor_source_only(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $this->makeViticulturistForStats($supervisor, $winery);
        $this->makeViticulturistForStats($supervisor, $winery);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('totalViticulturists', 2);
    }

    public function test_total_viticulturists_excludes_other_sources(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $ownVit = User::factory()->create(['role' => 'viticulturist']);
        WineryViticulturist::create([
            'supervisor_id'    => $supervisor->id,
            'winery_id'        => $winery->id,
            'viticulturist_id' => $ownVit->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('totalViticulturists', 0);
    }

    public function test_total_viticulturists_isolated_from_other_supervisor(): void
    {
        [$supervisor]                    = $this->makeSupervisorWithWinery();
        [$otherSupervisor, $otherWinery] = $this->makeSupervisorWithWinery();
        $this->makeViticulturistForStats($otherSupervisor, $otherWinery);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('totalViticulturists', 0);
    }

    // ── totalPlotAreaHa ───────────────────────────────────────────────────────

    public function test_total_plot_area_sums_active_plots_for_supervisor_viticulturists(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForStats($supervisor, $winery);

        $this->makePlot($vit, ['area' => 2.5, 'active' => true]);
        $this->makePlot($vit, ['area' => 1.5, 'active' => true]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('totalPlotAreaHa', fn ($v) => abs($v - 4.0) < 0.01);
    }

    public function test_total_plot_area_excludes_inactive_plots(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForStats($supervisor, $winery);

        $this->makePlot($vit, ['area' => 5.0, 'active' => false]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('totalPlotAreaHa', fn ($v) => (float) $v === 0.0);
    }

    public function test_total_plot_area_isolated_from_other_supervisor(): void
    {
        [$supervisor]                    = $this->makeSupervisorWithWinery();
        [$otherSupervisor, $otherWinery] = $this->makeSupervisorWithWinery();

        $otherVit = $this->makeViticulturistForStats($otherSupervisor, $otherWinery);
        $this->makePlot($otherVit, ['area' => 10.0, 'active' => true]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('totalPlotAreaHa', fn ($v) => (float) $v === 0.0);
    }

    // ── totalKgCurrentVintage ─────────────────────────────────────────────────

    public function test_total_kg_sums_active_current_year_harvests(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeHarvest($winery, ['total_weight' => 700, 'vintage' => now()->year]);
        $this->makeHarvest($winery, ['total_weight' => 300, 'vintage' => now()->year]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('totalKgCurrentVintage', fn ($v) => abs($v - 1000.0) < 0.01);
    }

    public function test_total_kg_excludes_previous_year(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeHarvest($winery, ['total_weight' => 999, 'vintage' => now()->year - 1]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('totalKgCurrentVintage', fn ($v) => (float) $v === 0.0);
    }

    public function test_total_kg_excludes_other_supervisor_wineries(): void
    {
        [$supervisor]                    = $this->makeSupervisorWithWinery();
        [$otherSupervisor, $otherWinery] = $this->makeSupervisorWithWinery();

        $this->makeHarvest($otherWinery, ['total_weight' => 999, 'vintage' => now()->year]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('totalKgCurrentVintage', fn ($v) => (float) $v === 0.0);
    }

    // ── harvestByVintage ──────────────────────────────────────────────────────

    public function test_harvest_by_vintage_groups_by_year(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeHarvest($winery, ['total_weight' => 500, 'vintage' => 2025]);
        $this->makeHarvest($winery, ['total_weight' => 800, 'vintage' => 2024]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('harvestByVintage', function ($rows) {
                $r25 = $rows->firstWhere('vintage', 2025);
                $r24 = $rows->firstWhere('vintage', 2024);
                return $r25?->total_kg == 500
                    && $r24?->total_kg == 800
                    && $r25?->reception_count == 1
                    && $r24?->reception_count == 1;
            });
    }

    public function test_harvest_by_vintage_averages_brix_ignoring_zeros(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeHarvest($winery, ['brix_degree' => 22.0, 'vintage' => 2025]);
        $this->makeHarvest($winery, ['brix_degree' => 24.0, 'vintage' => 2025]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('harvestByVintage', function ($rows) {
                $row = $rows->firstWhere('vintage', 2025);
                return $row !== null && abs($row->avg_brix - 23.0) < 0.01;
            });
    }
}
