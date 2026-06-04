<?php

namespace Tests\Feature\Supervisor\Oversight;

use App\Livewire\Supervisor\Oversight\Notebook\Index;
use App\Models\AgriculturalActivity;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class NotebookIndexTest extends SupervisorTestCase
{
    // ── carga básica ──────────────────────────────────────────────────────

    public function test_index_loads_for_supervisor(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertOk();
    }

    public function test_index_shows_zero_access_when_no_notebook_granted(): void
    {
        [$supervisor] = $this->makeSetup(cuadernoAccess: false);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('totalWithAccess', 0);
    }

    // ── visibilidad de actividades ────────────────────────────────────────

    public function test_activities_visible_when_notebook_access_granted(): void
    {
        [$supervisor, $viticulturist] = $this->makeSetup(cuadernoAccess: true);

        $plot = $this->makePlot($viticulturist);

        AgriculturalActivity::factory()
            ->forViticulturist($viticulturist)
            ->forPlot($plot)
            ->create(['activity_type' => 'irrigation', 'notes' => 'Riego de prueba']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertSee('Riego de prueba');
    }

    public function test_activities_hidden_when_notebook_access_not_granted(): void
    {
        [$supervisor, $viticulturist] = $this->makeSetup(cuadernoAccess: false);

        $plot = $this->makePlot($viticulturist);

        AgriculturalActivity::factory()
            ->forViticulturist($viticulturist)
            ->forPlot($plot)
            ->create(['notes' => 'Actividad secreta']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertDontSee('Actividad secreta');
    }

    public function test_activities_of_other_supervisors_viticulturists_not_shown(): void
    {
        $supervisor = $this->makeSupervisor();
        $outsideVit = User::factory()->create(['role' => 'viticulturist']);
        $plot = $this->makePlot($outsideVit);

        AgriculturalActivity::factory()
            ->forViticulturist($outsideVit)
            ->forPlot($plot)
            ->create(['notes' => 'Actividad ajena']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertDontSee('Actividad ajena');
    }

    // ── filtros ───────────────────────────────────────────────────────────

    public function test_filter_by_type_narrows_results(): void
    {
        [$supervisor, $viticulturist] = $this->makeSetup(cuadernoAccess: true);

        $plot = $this->makePlot($viticulturist);

        AgriculturalActivity::factory()
            ->forViticulturist($viticulturist)
            ->forPlot($plot)
            ->create(['activity_type' => 'irrigation', 'notes' => 'Nota Riego']);

        AgriculturalActivity::factory()
            ->forViticulturist($viticulturist)
            ->forPlot($plot)
            ->create(['activity_type' => 'pruning', 'notes' => 'Nota Poda']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterType', 'irrigation')
            ->assertSee('Nota Riego')
            ->assertDontSee('Nota Poda');
    }

    public function test_filter_by_viticulturist_narrows_results(): void
    {
        [$supervisor, $vit1, $winery] = $this->makeSetup(cuadernoAccess: true);

        $vit2 = $this->makeViticulturistForSupervisor($supervisor);
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $vit2->id,
            'source' => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id' => $supervisor->id,
            'assigned_by' => $supervisor->id,
            'notebook_access' => true,
            'notebook_granted_at' => now(),
        ]);

        $plot1 = $this->makePlot($vit1);
        $plot2 = $this->makePlot($vit2);

        AgriculturalActivity::factory()->forViticulturist($vit1)->forPlot($plot1)->create(['notes' => 'Act Vit1']);
        AgriculturalActivity::factory()->forViticulturist($vit2)->forPlot($plot2)->create(['notes' => 'Act Vit2']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterVit', (string) $vit1->id)
            ->assertSee('Act Vit1')
            ->assertDontSee('Act Vit2');
    }

    public function test_clear_filters_resets_all(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterType', 'irrigation')
            ->set('filterFrom', '2026-01-01')
            ->call('clearFilters')
            ->assertSet('filterType', '')
            ->assertSet('filterFrom', '');
    }

    private function makeSetup(bool $cuadernoAccess = true): array
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id' => $supervisor->id,
            'assigned_by' => $supervisor->id,
            'notebook_access' => $cuadernoAccess,
            'notebook_granted_at' => $cuadernoAccess ? now() : null,
        ]);

        return [$supervisor, $viticulturist, $winery];
    }
}
