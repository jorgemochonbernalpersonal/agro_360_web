<?php

namespace Tests\Feature\Supervisor\Statistics;

use App\Livewire\Supervisor\Statistics\Index;
use App\Models\GrapeVariety;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class StatisticsDataTest extends SupervisorTestCase
{
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeViticulturistForStats(User $supervisor, ?User $winery = null): User
    {
        $vit = User::factory()->create(['role' => 'viticulturist']);

        // Always in supervisor pool
        SupervisorViticulturist::create([
            'supervisor_id'    => $supervisor->id,
            'viticulturist_id' => $vit->id,
            'assigned_by'      => $supervisor->id,
        ]);

        // Optionally linked to a winery
        if ($winery) {
            WineryViticulturist::create([
                'supervisor_id'    => $supervisor->id,
                'winery_id'        => $winery->id,
                'viticulturist_id' => $vit->id,
                'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
                'assigned_by'      => $supervisor->id,
            ]);
        }

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

    public function test_total_viticulturists_counts_pool_members(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $this->makeViticulturistForStats($supervisor, $winery);
        $this->makeViticulturistForStats($supervisor, $winery);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('totalViticulturists', 2);
    }

    public function test_total_viticulturists_excludes_winery_own_source_not_in_pool(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        // Viticulturist only in winery's own pool, not in supervisor pool
        $ownVit = User::factory()->create(['role' => 'viticulturist']);
        WineryViticulturist::create([
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
        [$supervisor]          = $this->makeSupervisorWithWinery();
        [$otherSupervisor]     = $this->makeSupervisorWithWinery();
        $this->makeViticulturistForStats($otherSupervisor);

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

    // ── poolComposition ───────────────────────────────────────────────────────

    public function test_pool_composition_counts_active_ghost_and_invited(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $active = $this->makeViticulturistForStats($supervisor, $winery);
        $active->update(['can_login' => true]);

        $ghost = $this->makeViticulturistForStats($supervisor);
        $ghost->update(['can_login' => false, 'invitation_token' => null]);

        $invited = $this->makeViticulturistForStats($supervisor);
        $invited->update([
            'can_login'             => false,
            'invitation_token'      => 'token',
            'invitation_expires_at' => now()->addDays(5),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('poolComposition', fn ($c) =>
                $c['active'] === 1 && $c['ghost'] === 1 && $c['invited'] === 1
            );
    }

    public function test_notebook_access_count_only_counts_granted(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $granted = $this->makeViticulturistForStats($supervisor, $winery);
        SupervisorViticulturist::where('supervisor_id', $supervisor->id)
            ->where('viticulturist_id', $granted->id)
            ->update(['notebook_access' => true]);

        $notGranted = $this->makeViticulturistForStats($supervisor, $winery);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('notebookAccessCount', 1);
    }

    // ── organicPct / plotStats ────────────────────────────────────────────────

    public function test_organic_pct_correct_when_half_organic(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForStats($supervisor, $winery);

        $this->makePlot($vit, ['area' => 2.0, 'active' => true, 'is_organic' => true]);
        $this->makePlot($vit, ['area' => 2.0, 'active' => true, 'is_organic' => false]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('organicPct', 50.0);
    }

    public function test_organic_pct_zero_when_no_plots(): void
    {
        [$supervisor] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('organicPct', 0);
    }

    public function test_plot_stats_sums_organic_area(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForStats($supervisor, $winery);

        $this->makePlot($vit, ['area' => 3.0, 'active' => true, 'is_organic' => true]);
        $this->makePlot($vit, ['area' => 1.0, 'active' => true, 'is_organic' => false]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('plotStats', fn ($s) =>
                (int) $s->total_plots === 2
                && (int) $s->organic_plots === 1
                && abs($s->organic_area - 3.0) < 0.01
            );
    }

    // ── topVarieties ──────────────────────────────────────────────────────────

    public function test_top_varieties_ordered_by_area_descending(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForStats($supervisor, $winery);
        $plot = $this->makePlot($vit, ['area' => 5.0, 'active' => true]);

        $tempranillo = GrapeVariety::firstOrCreate(['name' => 'Tempranillo'], ['color' => 'red', 'active' => true]);
        $garnacha    = GrapeVariety::firstOrCreate(['name' => 'Garnacha'],    ['color' => 'red', 'active' => true]);

        DB::table('plot_plantings')->insert([
            ['plot_id' => $plot->id, 'grape_variety_id' => $tempranillo->id, 'area_planted' => 3.0, 'status' => 'active', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['plot_id' => $plot->id, 'grape_variety_id' => $garnacha->id,    'area_planted' => 1.0, 'status' => 'active', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('topVarieties', fn ($rows) =>
                $rows->first()?->variety_name === 'Tempranillo'
            );
    }

    public function test_top_varieties_excludes_other_supervisor_viticulturists(): void
    {
        [$supervisor]      = $this->makeSupervisorWithWinery();
        [$otherSupervisor] = $this->makeSupervisorWithWinery();

        $otherVit  = $this->makeViticulturistForStats($otherSupervisor);
        $otherPlot = $this->makePlot($otherVit, ['area' => 5.0, 'active' => true]);

        $variety = GrapeVariety::firstOrCreate(['name' => 'Ajena'], ['color' => 'white', 'active' => true]);
        DB::table('plot_plantings')->insert([
            'plot_id' => $otherPlot->id, 'grape_variety_id' => $variety->id,
            'area_planted' => 10.0, 'status' => 'active', 'active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('topVarieties', fn ($rows) => $rows->isEmpty());
    }

    // ── activityBreakdown ─────────────────────────────────────────────────────

    public function test_activity_breakdown_counts_by_type_for_current_year(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit  = $this->makeViticulturistForStats($supervisor, $winery);
        $plot = $this->makePlot($vit, ['area' => 1.0, 'active' => true]);

        DB::table('agricultural_activities')->insert([
            ['plot_id' => $plot->id, 'viticulturist_id' => $vit->id, 'activity_type' => 'pruning',       'activity_date' => now()->format('Y-m-d'), 'created_at' => now(), 'updated_at' => now()],
            ['plot_id' => $plot->id, 'viticulturist_id' => $vit->id, 'activity_type' => 'pruning',       'activity_date' => now()->format('Y-m-d'), 'created_at' => now(), 'updated_at' => now()],
            ['plot_id' => $plot->id, 'viticulturist_id' => $vit->id, 'activity_type' => 'fertilization', 'activity_date' => now()->format('Y-m-d'), 'created_at' => now(), 'updated_at' => now()],
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('activityBreakdown', fn ($rows) =>
                $rows->firstWhere('activity_type', 'pruning')?->total == 2
                && $rows->firstWhere('activity_type', 'fertilization')?->total == 1
            );
    }

    public function test_activity_breakdown_excludes_previous_year_activities(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit  = $this->makeViticulturistForStats($supervisor, $winery);
        $plot = $this->makePlot($vit, ['area' => 1.0, 'active' => true]);

        DB::table('agricultural_activities')->insert([
            'plot_id' => $plot->id, 'viticulturist_id' => $vit->id,
            'activity_type' => 'irrigation',
            'activity_date' => now()->subYear()->format('Y-m-d'),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('activityBreakdown', fn ($rows) => $rows->isEmpty());
    }

    public function test_activity_breakdown_isolated_from_other_supervisor(): void
    {
        [$supervisor]      = $this->makeSupervisorWithWinery();
        [$otherSupervisor] = $this->makeSupervisorWithWinery();

        $otherVit  = $this->makeViticulturistForStats($otherSupervisor);
        $otherPlot = $this->makePlot($otherVit, ['area' => 1.0, 'active' => true]);

        DB::table('agricultural_activities')->insert([
            'plot_id' => $otherPlot->id, 'viticulturist_id' => $otherVit->id,
            'activity_type' => 'cultural',
            'activity_date' => now()->format('Y-m-d'),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('activityBreakdown', fn ($rows) => $rows->isEmpty());
    }
}
