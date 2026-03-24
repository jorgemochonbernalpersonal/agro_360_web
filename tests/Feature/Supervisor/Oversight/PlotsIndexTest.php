<?php

namespace Tests\Feature\Supervisor\Oversight;

use App\Livewire\Supervisor\Oversight\Plots\Index;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class PlotsIndexTest extends SupervisorTestCase
{
    // ── carga básica ──────────────────────────────────────────────────────

    public function test_index_loads_for_supervisor(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertOk();
    }

    public function test_index_shows_plots_of_own_viticulturists(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlot($viticulturist, ['name' => 'Parcela Propia']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertSee('Parcela Propia');
    }

    // ── aislamiento ───────────────────────────────────────────────────────

    public function test_index_does_not_show_plots_of_other_supervisors_viticulturists(): void
    {
        $supervisor = $this->makeSupervisor();
        $outsideVit = User::factory()->create(['role' => 'viticulturist']);

        $this->makePlot($outsideVit, ['name' => 'Parcela Ajena']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertDontSee('Parcela Ajena');
    }

    // ── stats globales ────────────────────────────────────────────────────

    public function test_global_stats_reflect_supervisor_plots(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlot($viticulturist, ['area' => 3.0, 'pac_eligible_area' => null]);
        $this->makePlot($viticulturist, ['area' => 2.0, 'pac_eligible_area' => 1.5]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('globalStats', fn($stats) =>
                $stats->total_plots == 2
                && (float) $stats->total_area == 5.0
                && $stats->without_pac == 1
            );
    }

    // ── filtros ───────────────────────────────────────────────────────────

    public function test_filter_by_viticulturist_narrows_results(): void
    {
        $supervisor = $this->makeSupervisor();
        $vit1       = $this->makeViticulturistForSupervisor($supervisor);
        $vit2       = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlot($vit1, ['name' => 'Parcela Uno']);
        $this->makePlot($vit2, ['name' => 'Parcela Dos']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterVit', (string) $vit1->id)
            ->assertSee('Parcela Uno')
            ->assertDontSee('Parcela Dos');
    }

    public function test_filter_locked_shows_only_locked_plots(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlot($viticulturist, ['name' => 'Bloqueada', 'is_locked' => true]);
        $this->makePlot($viticulturist, ['name' => 'Libre', 'is_locked' => false]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterLocked', '1')
            ->assertSee('Bloqueada')
            ->assertDontSee('Libre');
    }

    public function test_search_filters_by_plot_name(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlot($viticulturist, ['name' => 'Viña Alta']);
        $this->makePlot($viticulturist, ['name' => 'Terraza Sur']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('search', 'Alta')
            ->assertSee('Viña Alta')
            ->assertDontSee('Terraza Sur');
    }

    public function test_clear_filters_resets_all(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('search', 'algo')
            ->set('filterLocked', '1')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('filterLocked', '');
    }
}
