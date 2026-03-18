<?php

namespace Tests\Feature\Supervisor\Oversight;

use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class GrowersIndexTest extends SupervisorTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_supervisor_can_access_oversight_growers_index(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('supervisor.oversight.growers.index'))
            ->assertOk();
    }

    public function test_winery_cannot_access_oversight_growers_index(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('supervisor.oversight.growers.index'))
            ->assertForbidden();
    }

    public function test_viticulturist_cannot_access_oversight_growers_index(): void
    {
        $viticulturist = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($viticulturist)
            ->get(route('supervisor.oversight.growers.index'))
            ->assertForbidden();
    }

    // ── data ──────────────────────────────────────────────────────────────────

    public function test_oversight_growers_index_shows_viticulturist_assigned_via_supervisor(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $viticulturist = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
            'name'              => 'Viticultor Supervisado',
        ]);

        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'    => $supervisor->id,
            'assigned_by'      => $supervisor->id,
        ]);

        $this->actingAs($supervisor)
            ->get(route('supervisor.oversight.growers.index'))
            ->assertSee('Viticultor Supervisado');
    }

    public function test_oversight_growers_index_does_not_show_viticulturist_from_other_supervisor(): void
    {
        [$supervisor, $winery]         = $this->makeSupervisorWithWinery();
        [$otherSupervisor, $otherWinery] = $this->makeSupervisorWithWinery();

        $viticulturist = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
            'name'              => 'Viticultor Ajeno',
        ]);

        WineryViticulturist::create([
            'winery_id'        => $otherWinery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'    => $otherSupervisor->id,
            'assigned_by'      => $otherSupervisor->id,
        ]);

        $this->actingAs($supervisor)
            ->get(route('supervisor.oversight.growers.index'))
            ->assertDontSee('Viticultor Ajeno');
    }

    public function test_oversight_growers_livewire_component_renders(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Oversight\Growers\Index::class)
            ->assertOk();
    }
}
