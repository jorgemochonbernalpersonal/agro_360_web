<?php

namespace Tests\Feature\Supervisor\Territory;

use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class IndexTest extends SupervisorTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_supervisor_can_access_territory_index(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('supervisor.territory.index'))
            ->assertOk();
    }

    public function test_winery_cannot_access_territory_index(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('supervisor.territory.index'))
            ->assertForbidden();
    }

    public function test_viticulturist_cannot_access_territory_index(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($viticulturist)
            ->get(route('supervisor.territory.index'))
            ->assertForbidden();
    }

    // ── render ────────────────────────────────────────────────────────────────

    public function test_territory_livewire_component_renders(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Territory\Index::class)
            ->assertOk();
    }
}
