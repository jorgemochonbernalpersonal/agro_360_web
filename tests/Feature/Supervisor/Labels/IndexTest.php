<?php

namespace Tests\Feature\Supervisor\Labels;

use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class IndexTest extends SupervisorTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_supervisor_can_access_labels_index(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('supervisor.labels.index'))
            ->assertOk();
    }

    public function test_winery_cannot_access_labels_index(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('supervisor.labels.index'))
            ->assertForbidden();
    }

    public function test_viticulturist_cannot_access_labels_index(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($viticulturist)
            ->get(route('supervisor.labels.index'))
            ->assertForbidden();
    }

    // ── render ────────────────────────────────────────────────────────────────

    public function test_labels_livewire_component_renders(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Labels\Index::class)
            ->assertOk();
    }
}
