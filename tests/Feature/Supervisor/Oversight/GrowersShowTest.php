<?php

namespace Tests\Feature\Supervisor\Oversight;

use App\Livewire\Supervisor\Oversight\Growers\Show;
use App\Models\AgriculturalActivity;
use App\Models\Certification;
use App\Models\DoInspection;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class GrowersShowTest extends SupervisorTestCase
{
    // ── carga básica ──────────────────────────────────────────────────────

    public function test_show_loads_for_supervisor_with_their_viticulturist(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->assertOk()
            ->assertSet('viticulturist.id', $viticulturist->id);
    }

    public function test_supervisor_can_access_grower_show_via_route(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->actingAs($supervisor)
            ->get(route('supervisor.oversight.growers.show', $viticulturist))
            ->assertOk();
    }

    public function test_winery_cannot_access_grower_show(): void
    {
        $winery        = $this->makeWinery();
        $viticulturist = User::factory()->create(['role' => 'viticulturist', 'email_verified_at' => now()]);

        $this->actingAs($winery)
            ->get(route('supervisor.oversight.growers.show', $viticulturist))
            ->assertForbidden();
    }

    // ── guard ─────────────────────────────────────────────────────────────

    public function test_another_supervisor_cannot_view_unlinked_viticulturist(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);
        $other         = $this->makeSupervisor();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($other)
            ->test(Show::class, ['viticulturist' => $viticulturist]);
    }

    // ── parcelas ─────────────────────────────────────────────────────────

    public function test_show_displays_viticulturist_plots(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlot($viticulturist, ['name' => 'Parcela Norte']);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->assertSee('Parcela Norte');
    }

    public function test_show_total_area_aggregates_active_plots(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlot($viticulturist, ['area' => 2.500, 'active' => true]);
        $this->makePlot($viticulturist, ['area' => 1.250, 'active' => true]);
        $this->makePlot($viticulturist, ['area' => 5.000, 'active' => false]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->assertViewHas('totalArea', fn($v) => round((float) $v, 3) === 3.75)
            ->assertViewHas('totalPlots', 2);
    }

    // ── bodegas asignadas ─────────────────────────────────────────────────

    public function test_show_lists_wineries_this_viticulturist_is_assigned_to(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $viticulturist         = $this->makeViticulturistForSupervisor($supervisor);
        $winery->update(['name' => 'Bodega Los Pinos']);

        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'    => $supervisor->id,
            'assigned_by'      => $supervisor->id,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->assertViewHas('wineryRelations', fn($r) => $r->count() === 1)
            ->assertSee('Bodega Los Pinos');
    }

    public function test_show_does_not_list_wineries_from_another_supervisor(): void
    {
        [$supervisor]          = $this->makeSupervisorWithWinery();
        [$other, $otherWinery] = $this->makeSupervisorWithWinery();
        $viticulturist         = $this->makeViticulturistForSupervisor($supervisor);

        WineryViticulturist::create([
            'winery_id'        => $otherWinery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'    => $other->id,
            'assigned_by'      => $other->id,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->assertViewHas('wineryRelations', fn($r) => $r->isEmpty());
    }

    // ── cuaderno de campo ─────────────────────────────────────────────────

    public function test_show_hides_notebook_when_no_access_granted(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->assertViewHas('hasNotebookAccess', false)
            ->assertViewHas('recentActivities', fn($v) => $v->count() === 0);
    }

    public function test_show_displays_notebook_activities_when_supervisor_access_granted(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        SupervisorViticulturist::where('supervisor_id', $supervisor->id)
            ->where('viticulturist_id', $viticulturist->id)
            ->firstOrFail()
            ->grantNotebookAccess();

        $plot = $this->makePlot($viticulturist);

        AgriculturalActivity::factory()
            ->forViticulturist($viticulturist)
            ->forPlot($plot)
            ->create(['activity_type' => 'irrigation']);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->assertViewHas('hasNotebookAccess', true)
            ->assertViewHas('recentActivities', fn($v) => $v->count() > 0);
    }

    // ── revocar acceso cuaderno ───────────────────────────────────────────

    public function test_supervisor_can_revoke_notebook_access(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        SupervisorViticulturist::where('supervisor_id', $supervisor->id)
            ->where('viticulturist_id', $viticulturist->id)
            ->firstOrFail()
            ->grantNotebookAccess();

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->call('revokeNotebookAccess')
            ->assertDispatched('toast');

        $this->assertFalse(
            SupervisorViticulturist::where('supervisor_id', $supervisor->id)
                ->where('viticulturist_id', $viticulturist->id)
                ->first()
                ->notebook_access
        );
    }

    public function test_revoke_updates_view_state(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        SupervisorViticulturist::where('supervisor_id', $supervisor->id)
            ->where('viticulturist_id', $viticulturist->id)
            ->firstOrFail()
            ->grantNotebookAccess();

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->assertViewHas('hasNotebookAccess', true)
            ->call('revokeNotebookAccess')
            ->assertViewHas('hasNotebookAccess', false);
    }

    // ── certificaciones ───────────────────────────────────────────────────

    public function test_show_displays_certifications(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        Certification::create([
            'viticulturist_id'   => $viticulturist->id,
            'certification_type' => 'ecologico',
            'certifying_body'    => 'Organismo Test',
            'active'             => true,
            'issue_date'         => now()->subYear(),
            'expiry_date'        => now()->addYear(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->assertSee('Agricultura Ecológica');
    }

    public function test_show_only_lists_active_certifications(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        Certification::create([
            'viticulturist_id'   => $viticulturist->id,
            'certification_type' => 'ecologico',
            'certifying_body'    => 'Activa',
            'active'             => true,
            'issue_date'         => now()->subYear(),
            'expiry_date'        => now()->addYear(),
        ]);

        Certification::create([
            'viticulturist_id'   => $viticulturist->id,
            'certification_type' => 'otro',
            'certifying_body'    => 'Inactiva',
            'active'             => false,
            'issue_date'         => now()->subYears(2),
            'expiry_date'        => now()->subYear(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->assertViewHas('certifications', fn($c) => $c->count() === 1);
    }

    // ── inspecciones ──────────────────────────────────────────────────────

    public function test_show_displays_inspections_made_by_this_supervisor(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        DoInspection::create([
            'supervisor_id'   => $supervisor->id,
            'subject_type'    => 'viticulturist',
            'subject_id'      => $viticulturist->id,
            'inspection_date' => now()->subDays(5)->format('Y-m-d'),
            'result'          => 'compliant',
            'notes'           => 'Sin incidencias.',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->assertViewHas('inspections', fn($i) => $i->count() === 1);
    }

    public function test_show_does_not_display_inspections_from_another_supervisor(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);
        $other         = $this->makeSupervisor();

        DoInspection::create([
            'supervisor_id'   => $other->id,
            'subject_type'    => 'viticulturist',
            'subject_id'      => $viticulturist->id,
            'inspection_date' => now()->subDays(3)->format('Y-m-d'),
            'result'          => 'compliant',
            'notes'           => 'Inspección ajena.',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->assertViewHas('inspections', fn($i) => $i->isEmpty());
    }

    // ── alertas PAC ───────────────────────────────────────────────────────

    public function test_show_counts_plots_without_pac_data(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlot($viticulturist, ['pac_eligible_area' => null]);
        $this->makePlot($viticulturist, ['pac_eligible_area' => 1.5]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->assertViewHas('plotsWithoutPac', 1);
    }

    public function test_show_counts_locked_plots(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlot($viticulturist, ['is_locked' => true]);
        $this->makePlot($viticulturist, ['is_locked' => false]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->assertViewHas('lockedPlots', 1);
    }
}
