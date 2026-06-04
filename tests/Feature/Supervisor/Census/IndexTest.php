<?php

namespace Tests\Feature\Supervisor\Census;

use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class IndexTest extends SupervisorTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_supervisor_can_access_census(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('supervisor.census.index'))
            ->assertOk();
    }

    public function test_winery_cannot_access_census(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('supervisor.census.index'))
            ->assertForbidden();
    }

    public function test_viticulturist_cannot_access_census(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($viticulturist)
            ->get(route('supervisor.census.index'))
            ->assertForbidden();
    }

    // ── data ──────────────────────────────────────────────────────────────────

    public function test_census_shows_assigned_winery_name(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $winery->update(['name' => 'Bodega Censal']);

        $this->actingAs($supervisor)
            ->get(route('supervisor.census.index'))
            ->assertSee('Bodega Censal');
    }

    public function test_census_does_not_show_unassigned_wineries(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();
        $winery->update(['name' => 'Bodega Ajena']);

        $this->actingAs($supervisor)
            ->get(route('supervisor.census.index'))
            ->assertDontSee('Bodega Ajena');
    }

    public function test_census_livewire_component_renders(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Census\Index::class)
            ->assertOk();
    }
}
