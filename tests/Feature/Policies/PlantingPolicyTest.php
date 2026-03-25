<?php

namespace Tests\Feature\Policies;

use App\Models\PlotPlanting;
use App\Policies\PlotPlantingPolicy;
use App\Models\SupervisorViticulturist;
use App\Models\SupervisorWinery;
use App\Models\User;
use App\Models\WineryViticulturist;
use Tests\Feature\SupervisorTestCase;

/**
 * PlantingPolicy: wineries can only create/edit plantings on plots belonging to
 * viticulturists they own (source=own). Supervisor-assigned viticulturists are read-only.
 */
class PlantingPolicyTest extends SupervisorTestCase
{
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeOwnViticulturist(User $winery): User
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);

        return $viticulturist;
    }

    private function makeSupervisorAssignedViticulturist(User $supervisor, User $winery): User
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        SupervisorViticulturist::create([
            'supervisor_id'    => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'assigned_by'      => $supervisor->id,
        ]);

        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'    => $supervisor->id,
            'assigned_by'      => $winery->id,
        ]);

        return $viticulturist;
    }

    // ── create ────────────────────────────────────────────────────────────────

    public function test_winery_can_create_planting_on_own_viticulturist_plot(): void
    {
        $winery        = $this->makeWinery();
        $viticulturist = $this->makeOwnViticulturist($winery);
        $plot          = $this->makePlot($viticulturist);

        $this->assertTrue($winery->can('create', [\App\Models\PlotPlanting::class, $plot]));
    }

    public function test_winery_cannot_create_planting_on_supervisor_viticulturist_plot(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $viticulturist         = $this->makeSupervisorAssignedViticulturist($supervisor, $winery);
        $plot                  = $this->makePlot($viticulturist);

        $this->assertFalse($winery->can('create', [\App\Models\PlotPlanting::class, $plot]));
    }

    // ── update ────────────────────────────────────────────────────────────────

    public function test_winery_can_update_planting_on_own_viticulturist_plot(): void
    {
        $winery        = $this->makeWinery();
        $viticulturist = $this->makeOwnViticulturist($winery);
        $plot          = $this->makePlot($viticulturist);
        $planting      = PlotPlanting::factory()->create(['plot_id' => $plot->id]);

        $this->assertTrue($winery->can('update', $planting));
    }

    public function test_winery_cannot_update_planting_on_supervisor_viticulturist_plot(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $viticulturist         = $this->makeSupervisorAssignedViticulturist($supervisor, $winery);
        $plot                  = $this->makePlot($viticulturist);
        $planting              = PlotPlanting::factory()->create(['plot_id' => $plot->id]);

        $this->assertFalse($winery->can('update', $planting));
    }

    // ── delete ────────────────────────────────────────────────────────────────

    public function test_winery_cannot_delete_planting_on_supervisor_viticulturist_plot(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $viticulturist         = $this->makeSupervisorAssignedViticulturist($supervisor, $winery);
        $plot                  = $this->makePlot($viticulturist);
        $planting              = PlotPlanting::factory()->create(['plot_id' => $plot->id]);

        $this->assertFalse($winery->can('delete', $planting));
    }

    // ── viticulturist owns their own plots ────────────────────────────────────

    public function test_viticulturist_can_update_own_planting(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot          = $this->makePlot($viticulturist);
        $planting      = PlotPlanting::factory()->create(['plot_id' => $plot->id]);

        $this->assertTrue($viticulturist->can('update', $planting));
    }

    // ── route protection ──────────────────────────────────────────────────────

    public function test_winery_gets_403_creating_planting_on_supervisor_viticulturist_plot(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $viticulturist         = $this->makeSupervisorAssignedViticulturist($supervisor, $winery);
        $plot                  = $this->makePlot($viticulturist);

        $this->actingAs($winery)
            ->get(route('plots.plantings.create', $plot))
            ->assertForbidden();
    }

    public function test_winery_can_access_planting_create_for_own_viticulturist(): void
    {
        $winery        = $this->makeWinery();
        $viticulturist = $this->makeOwnViticulturist($winery);
        $plot          = $this->makePlot($viticulturist);

        $this->actingAs($winery)
            ->get(route('plots.plantings.create', $plot))
            ->assertOk();
    }
}
