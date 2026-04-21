<?php

namespace Tests\Feature\Supervisor\Oversight;

use App\Livewire\Supervisor\Oversight\Activity\Index;
use App\Models\AgriculturalActivity;
use App\Models\Observation;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class ActivityIndexTest extends SupervisorTestCase
{
    private function makeSetup(bool $cuadernoAccess = true): array
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $viticulturist         = $this->makeViticulturistForSupervisor($supervisor);

        WineryViticulturist::create([
            'winery_id'           => $winery->id,
            'viticulturist_id'    => $viticulturist->id,
            'source'              => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'       => $supervisor->id,
            'assigned_by'         => $supervisor->id,
            'notebook_access'     => $cuadernoAccess,
            'notebook_granted_at' => $cuadernoAccess ? now() : null,
        ]);

        return [$supervisor, $viticulturist, $winery];
    }

    // ── carga básica ──────────────────────────────────────────────────────

    public function test_index_loads_for_supervisor(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertOk();
    }

    public function test_shows_zero_access_count_when_no_cuaderno_granted(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('totalWithAccess', 0);
    }

    // ── visibilidad de actividades ────────────────────────────────────────

    public function test_shows_activities_when_cuaderno_access_granted(): void
    {
        [$supervisor, $viticulturist] = $this->makeSetup(cuadernoAccess: true);

        $plot = $this->makePlot($viticulturist);

        AgriculturalActivity::factory()
            ->forViticulturist($viticulturist)
            ->forPlot($plot)
            ->create(['activity_type' => 'pruning', 'notes' => 'Poda de invierno']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertSee('Poda de invierno');
    }

    public function test_hides_activities_when_cuaderno_access_not_granted(): void
    {
        [$supervisor, $viticulturist] = $this->makeSetup(cuadernoAccess: false);

        $plot = $this->makePlot($viticulturist);

        AgriculturalActivity::factory()
            ->forViticulturist($viticulturist)
            ->forPlot($plot)
            ->create(['notes' => 'Nota oculta']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertDontSee('Nota oculta');
    }

    public function test_does_not_show_activities_of_unrelated_viticulturists(): void
    {
        $supervisor = $this->makeSupervisor();
        $outsideVit = User::factory()->create(['role' => 'viticulturist']);
        $plot       = $this->makePlot($outsideVit);

        AgriculturalActivity::factory()
            ->forViticulturist($outsideVit)
            ->forPlot($plot)
            ->create(['notes' => 'Actividad ajena']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertDontSee('Actividad ajena');
    }

    // ── filtros ───────────────────────────────────────────────────────────

    public function test_filter_by_activity_type(): void
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

    public function test_filter_by_date_from(): void
    {
        [$supervisor, $viticulturist] = $this->makeSetup(cuadernoAccess: true);

        $plot = $this->makePlot($viticulturist);

        AgriculturalActivity::factory()
            ->forViticulturist($viticulturist)
            ->forPlot($plot)
            ->create(['activity_date' => '2026-03-20', 'notes' => 'Actividad reciente']);

        AgriculturalActivity::factory()
            ->forViticulturist($viticulturist)
            ->forPlot($plot)
            ->create(['activity_date' => '2026-01-01', 'notes' => 'Actividad antigua']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterFrom', '2026-03-01')
            ->assertSee('Actividad reciente')
            ->assertDontSee('Actividad antigua');
    }

    // ── alertas ───────────────────────────────────────────────────────────

    public function test_only_alerts_filter_shows_observations_with_threshold_exceeded(): void
    {
        [$supervisor, $viticulturist] = $this->makeSetup(cuadernoAccess: true);

        $plot = $this->makePlot($viticulturist);

        $actAlert = AgriculturalActivity::factory()
            ->forViticulturist($viticulturist)
            ->forPlot($plot)
            ->create(['activity_type' => 'observation', 'notes' => 'Alerta plagas']);

        Observation::create([
            'activity_id'              => $actAlert->id,
            'threshold_exceeded'       => true,
            'affected_area_percentage' => 35.0,
            'observation_type'         => 'pest',
            'description'              => 'Plaga detectada',
        ]);

        $actNormal = AgriculturalActivity::factory()
            ->forViticulturist($viticulturist)
            ->forPlot($plot)
            ->create(['activity_type' => 'observation', 'notes' => 'Observación normal']);

        Observation::create([
            'activity_id'        => $actNormal->id,
            'threshold_exceeded' => false,
            'observation_type'   => 'general',
            'description'        => 'Sin incidencias',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('onlyAlerts', true)
            ->assertSee('Alerta plagas')
            ->assertDontSee('Observación normal');
    }

    public function test_active_alerts_count_includes_overdue_follow_up(): void
    {
        [$supervisor, $viticulturist] = $this->makeSetup(cuadernoAccess: true);

        $plot = $this->makePlot($viticulturist);

        $act = AgriculturalActivity::factory()
            ->forViticulturist($viticulturist)
            ->forPlot($plot)
            ->create(['activity_type' => 'observation']);

        Observation::create([
            'activity_id'              => $act->id,
            'threshold_exceeded'       => false,
            'follow_up_date'           => now()->subDays(3),
            'affected_area_percentage' => 10.0,
            'observation_type'         => 'general',
            'description'              => 'Seguimiento vencido',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('activeAlerts', 1);
    }

    public function test_clear_filters_resets_all(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterType', 'pruning')
            ->set('onlyAlerts', true)
            ->call('clearFilters')
            ->assertSet('filterType', '')
            ->assertSet('onlyAlerts', false);
    }
}
