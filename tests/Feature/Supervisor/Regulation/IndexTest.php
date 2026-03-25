<?php

namespace Tests\Feature\Supervisor\Regulation;

use App\Livewire\Supervisor\Regulation\Index;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class IndexTest extends SupervisorTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_supervisor_can_access_regulation_index(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('supervisor.regulation.index'))
            ->assertOk();
    }

    public function test_winery_cannot_access_regulation_index(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('supervisor.regulation.index'))
            ->assertForbidden();
    }

    public function test_viticulturist_cannot_access_regulation_index(): void
    {
        $viticulturist = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($viticulturist)
            ->get(route('supervisor.regulation.index'))
            ->assertForbidden();
    }

    // ── render ────────────────────────────────────────────────────────────────

    public function test_livewire_component_renders(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertOk();
    }

    public function test_default_tab_is_autorizaciones(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertSet('currentTab', 'autorizaciones');
    }

    public function test_can_switch_tabs(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('switchTab', 'certificaciones')
            ->assertSet('currentTab', 'certificaciones')
            ->call('switchTab', 'pliego')
            ->assertSet('currentTab', 'pliego')
            ->call('switchTab', 'reglamento')
            ->assertSet('currentTab', 'reglamento');
    }

    public function test_switching_tab_resets_filters(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterVit', '99')
            ->set('search', 'algo')
            ->call('switchTab', 'certificaciones')
            ->assertSet('filterVit', '')
            ->assertSet('search', '');
    }
}
