<?php

namespace Tests\Feature\Supervisor\Inspection;

use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class IndexTest extends SupervisorTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_supervisor_can_access_inspection_index(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('supervisor.inspection.index'))
            ->assertOk();
    }

    public function test_winery_cannot_access_inspection_index(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('supervisor.inspection.index'))
            ->assertForbidden();
    }

    public function test_viticulturist_cannot_access_inspection_index(): void
    {
        $viticulturist = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($viticulturist)
            ->get(route('supervisor.inspection.index'))
            ->assertForbidden();
    }

    // ── render ────────────────────────────────────────────────────────────────

    public function test_inspection_livewire_component_renders(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Inspection\Index::class)
            ->assertOk();
    }
}
