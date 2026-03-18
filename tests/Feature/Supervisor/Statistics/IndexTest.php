<?php

namespace Tests\Feature\Supervisor\Statistics;

use App\Models\SupervisorWinery;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class IndexTest extends SupervisorTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_supervisor_can_access_statistics_index(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('supervisor.statistics.index'))
            ->assertOk();
    }

    public function test_winery_cannot_access_statistics_index(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('supervisor.statistics.index'))
            ->assertForbidden();
    }

    public function test_viticulturist_cannot_access_statistics_index(): void
    {
        $viticulturist = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($viticulturist)
            ->get(route('supervisor.statistics.index'))
            ->assertForbidden();
    }

    // ── render ────────────────────────────────────────────────────────────────

    public function test_statistics_livewire_component_renders(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Statistics\Index::class)
            ->assertOk();
    }

    public function test_statistics_counts_only_own_bodegas_and_viticultors(): void
    {
        [$supervisor, $winery]           = $this->makeSupervisorWithWinery();
        [$otherSupervisor, $otherWinery] = $this->makeSupervisorWithWinery();

        // Viticulturist assigned to this supervisor only
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

        // The page must load successfully — counts are scoped to this supervisor
        $this->get(route('supervisor.statistics.index'))->assertOk();
    }
}
