<?php

namespace Tests\Feature\Supervisor\Growers;

use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class IndexTest extends SupervisorTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_supervisor_can_access_growers_index(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('supervisor.growers.index'))
            ->assertOk();
    }

    public function test_winery_cannot_access_growers_index(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('supervisor.growers.index'))
            ->assertForbidden();
    }

    public function test_viticulturist_cannot_access_growers_index(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($viticulturist)
            ->get(route('supervisor.growers.index'))
            ->assertForbidden();
    }

    // ── data ──────────────────────────────────────────────────────────────────

    public function test_growers_index_shows_viticulturists_assigned_by_supervisor(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
            'name' => 'Viticultor Propio',
        ]);

        // Add to supervisor pool first
        SupervisorViticulturist::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'assigned_by' => $supervisor->id,
        ]);

        // Also assign to a winery (optional, but reflects realistic state)
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id' => $supervisor->id,
            'assigned_by' => $supervisor->id,
        ]);

        $this->actingAs($supervisor)
            ->get(route('supervisor.growers.index'))
            ->assertSee('Viticultor Propio');
    }

    public function test_growers_index_livewire_component_renders(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Growers\Index::class)
            ->assertOk();
    }
}
