<?php

namespace Tests\Feature\Supervisor\Campaigns;

use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class IndexTest extends SupervisorTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_supervisor_can_access_campaigns_index(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('supervisor.campaigns.index'))
            ->assertOk();
    }

    public function test_winery_cannot_access_campaigns_index(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('supervisor.campaigns.index'))
            ->assertForbidden();
    }

    public function test_viticulturist_cannot_access_campaigns_index(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($viticulturist)
            ->get(route('supervisor.campaigns.index'))
            ->assertForbidden();
    }

    // ── render ────────────────────────────────────────────────────────────────

    public function test_campaigns_livewire_component_renders(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Campaigns\Index::class)
            ->assertOk();
    }
}
