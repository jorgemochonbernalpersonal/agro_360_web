<?php

namespace Tests\Feature\Supervisor\Oversight;

use App\Livewire\Supervisor\Oversight\Growers\Show;
use App\Models\AgriculturalActivity;
use App\Models\Certification;
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

    public function test_show_total_area_aggregates_plots(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlot($viticulturist, ['area' => 2.500]);
        $this->makePlot($viticulturist, ['area' => 1.250]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->assertViewHas('totalArea', fn($v) => round((float) $v, 3) === 3.75);
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

    public function test_show_displays_notebook_activities_when_access_granted(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $viticulturist         = $this->makeViticulturistForSupervisor($supervisor);

        WineryViticulturist::create([
            'winery_id'           => $winery->id,
            'viticulturist_id'    => $viticulturist->id,
            'source'              => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'       => $supervisor->id,
            'assigned_by'         => $supervisor->id,
            'cuaderno_access'     => true,
            'cuaderno_granted_at' => now(),
        ]);

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
}
