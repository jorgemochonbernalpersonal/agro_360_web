<?php

namespace Tests\Feature\Supervisor\Oversight;

use App\Livewire\Supervisor\Oversight\Pac\Index;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class PacIndexTest extends SupervisorTestCase
{
    // ── carga básica ──────────────────────────────────────────────────────

    public function test_index_loads_for_supervisor(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertOk();
    }

    public function test_index_loads_with_zero_plots_when_no_viticulturists(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('stats', fn($stats) => $stats->total_plots == 0);
    }

    // ── stats PAC ─────────────────────────────────────────────────────────

    public function test_stats_count_plots_with_and_without_pac(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlot($viticulturist, ['pac_eligible_area' => 2.0]);
        $this->makePlot($viticulturist, ['pac_eligible_area' => null]);
        $this->makePlot($viticulturist, ['pac_eligible_area' => null]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('stats', fn($stats) =>
                $stats->total_plots == 3
                && $stats->with_pac == 1
                && $stats->without_pac == 2
            );
    }

    public function test_stats_count_locked_plots(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlot($viticulturist, ['is_locked' => true]);
        $this->makePlot($viticulturist, ['is_locked' => false]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('stats', fn($stats) => $stats->locked_count == 1);
    }

    public function test_stats_do_not_include_plots_of_other_supervisors(): void
    {
        $supervisor = $this->makeSupervisor();
        $this->makeViticulturistForSupervisor($supervisor);

        $outsideVit = User::factory()->create(['role' => 'viticulturist']);
        $this->makePlot($outsideVit);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('stats', fn($stats) => $stats->total_plots == 0);
    }

    // ── filtros ───────────────────────────────────────────────────────────

    public function test_filter_missing_pac_shows_only_plots_without_eligible_area(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlot($viticulturist, ['name' => 'Sin PAC', 'pac_eligible_area' => null]);
        $this->makePlot($viticulturist, ['name' => 'Con PAC', 'pac_eligible_area' => 1.5]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterStatus', 'missing_pac')
            ->assertSee('Sin PAC')
            ->assertDontSee('Con PAC');
    }

    public function test_filter_locked_shows_only_locked_plots(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlot($viticulturist, ['name' => 'Bloqueada', 'is_locked' => true]);
        $this->makePlot($viticulturist, ['name' => 'Libre', 'is_locked' => false]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterStatus', 'locked')
            ->assertSee('Bloqueada')
            ->assertDontSee('Libre');
    }

    public function test_filter_ok_shows_only_complete_unlocked_plots(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlot($viticulturist, ['name' => 'Correcta', 'pac_eligible_area' => 2.0, 'is_locked' => false]);
        $this->makePlot($viticulturist, ['name' => 'Incompleta', 'pac_eligible_area' => null, 'is_locked' => false]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterStatus', 'ok')
            ->assertSee('Correcta')
            ->assertDontSee('Incompleta');
    }

    public function test_filter_by_viticulturist(): void
    {
        $supervisor = $this->makeSupervisor();
        $vit1       = $this->makeViticulturistForSupervisor($supervisor);
        $vit2       = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlot($vit1, ['name' => 'Parcela Vit1']);
        $this->makePlot($vit2, ['name' => 'Parcela Vit2']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterVit', (string) $vit1->id)
            ->assertSee('Parcela Vit1')
            ->assertDontSee('Parcela Vit2');
    }
}
