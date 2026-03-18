<?php

namespace Tests\Feature\Supervisor\Oversight;

use App\Models\SupervisorWinery;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class WineriesIndexTest extends SupervisorTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_supervisor_can_access_oversight_wineries_index(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('supervisor.oversight.wineries.index'))
            ->assertOk();
    }

    public function test_winery_cannot_access_oversight_wineries_index(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('supervisor.oversight.wineries.index'))
            ->assertForbidden();
    }

    public function test_viticulturist_cannot_access_oversight_wineries_index(): void
    {
        $viticulturist = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($viticulturist)
            ->get(route('supervisor.oversight.wineries.index'))
            ->assertForbidden();
    }

    // ── data ──────────────────────────────────────────────────────────────────

    public function test_oversight_wineries_index_shows_assigned_winery(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $winery->update(['name' => 'Bodega Supervisada']);

        $this->actingAs($supervisor)
            ->get(route('supervisor.oversight.wineries.index'))
            ->assertSee('Bodega Supervisada');
    }

    public function test_oversight_wineries_index_does_not_show_unassigned_winery(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery     = $this->makeWinery();
        $winery->update(['name' => 'Bodega No Asignada']);

        $this->actingAs($supervisor)
            ->get(route('supervisor.oversight.wineries.index'))
            ->assertDontSee('Bodega No Asignada');
    }

    public function test_oversight_wineries_index_shows_total_wineries_count(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Oversight\Wineries\Index::class)
            ->assertViewHas('totalWineries', 1);
    }
}
