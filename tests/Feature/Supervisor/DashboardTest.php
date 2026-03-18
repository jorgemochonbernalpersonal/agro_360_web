<?php

namespace Tests\Feature\Supervisor;

use App\Models\SupervisorWinery;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class DashboardTest extends SupervisorTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_supervisor_can_access_dashboard(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('supervisor.dashboard'))
            ->assertOk();
    }

    public function test_winery_cannot_access_dashboard(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('supervisor.dashboard'))
            ->assertForbidden();
    }

    public function test_viticulturist_cannot_access_dashboard(): void
    {
        $viticulturist = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($viticulturist)
            ->get(route('supervisor.dashboard'))
            ->assertForbidden();
    }

    // ── counts ────────────────────────────────────────────────────────────────

    public function test_dashboard_shows_winery_count(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('wineryCount', 1);
    }

    public function test_dashboard_shows_viticulturist_count(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $viticulturist = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'    => $supervisor->id,
            'assigned_by'      => $supervisor->id,
        ]);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('viticulturistCount', 1);
    }

    public function test_dashboard_counts_only_own_wineries(): void
    {
        $supervisor      = $this->makeSupervisor();
        $otherSupervisor = $this->makeSupervisor();
        $winery          = $this->makeWinery();

        SupervisorWinery::create([
            'supervisor_id' => $otherSupervisor->id,
            'winery_id'     => $winery->id,
            'assigned_by'   => $otherSupervisor->id,
        ]);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('wineryCount', 0);
    }
}
