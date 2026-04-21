<?php

namespace Tests\Feature\Supervisor\Census;

use App\Livewire\Supervisor\Census\Index;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class CensusTabsTest extends SupervisorTestCase
{
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeViticulturistForCensus(User $supervisor, User $winery): User
    {
        $vit = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'supervisor_id'    => $supervisor->id,
            'winery_id'        => $winery->id,
            'viticulturist_id' => $vit->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'assigned_by'      => $supervisor->id,
        ]);

        return $vit;
    }

    // ── assignWinery: producer allowed ────────────────────────────────────────

    public function test_supervisor_can_assign_a_producer(): void
    {
        $supervisor = $this->makeSupervisor();
        $producer   = User::factory()->create([
            'role'              => 'producer',
            'email_verified_at' => now(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('assignWinery', $producer->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('supervisor_winery', [
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $producer->id,
        ]);
    }

    // ── switchTab ─────────────────────────────────────────────────────────────

    public function test_switch_tab_changes_current_tab(): void
    {
        [$supervisor] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertSet('currentTab', 'wineries')
            ->call('switchTab', 'viticulturists')
            ->assertSet('currentTab', 'viticulturists');
    }

    public function test_switch_tab_clears_search(): void
    {
        [$supervisor] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('search', 'busqueda')
            ->call('switchTab', 'viticulturists')
            ->assertSet('search', '');
    }

    // ── modal ─────────────────────────────────────────────────────────────────

    public function test_open_assign_modal_sets_state(): void
    {
        [$supervisor] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openAssignModal')
            ->assertSet('showAssignModal', true)
            ->assertSet('assignSearch', '');
    }

    public function test_close_assign_modal_resets_state(): void
    {
        [$supervisor] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openAssignModal')
            ->set('assignSearch', 'algo')
            ->call('closeAssignModal')
            ->assertSet('showAssignModal', false)
            ->assertSet('assignSearch', '');
    }

    // ── counters ──────────────────────────────────────────────────────────────

    public function test_winery_count_reflects_linked_wineries(): void
    {
        [$supervisor] = $this->makeSupervisorWithWinery();
        $winery2      = $this->makeWinery();

        \App\Models\SupervisorWinery::create([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery2->id,
            'assigned_by'   => $supervisor->id,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('wineryCount', 2);
    }

    public function test_viticulturist_count_reflects_supervisor_source_only(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $this->makeViticulturistForCensus($supervisor, $winery);
        $this->makeViticulturistForCensus($supervisor, $winery);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('viticulturistCount', 2);
    }

    // ── vitCountByWinery ──────────────────────────────────────────────────────

    public function test_vit_count_by_winery_shows_per_winery_totals(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeViticulturistForCensus($supervisor, $winery);
        $this->makeViticulturistForCensus($supervisor, $winery);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('vitCountByWinery', fn ($map) =>
                ($map[$winery->id] ?? null) == 2
            );
    }

    // ── search in wineries tab ────────────────────────────────────────────────

    public function test_search_filters_wineries_by_name(): void
    {
        $supervisor = $this->makeSupervisor();

        $visible = User::factory()->create(['role' => 'winery', 'name' => 'Bodega Seleccionada']);
        $hidden  = User::factory()->create(['role' => 'winery', 'name' => 'Otra Bodega']);

        \App\Models\SupervisorWinery::create(['supervisor_id' => $supervisor->id, 'winery_id' => $visible->id, 'assigned_by' => $supervisor->id]);
        \App\Models\SupervisorWinery::create(['supervisor_id' => $supervisor->id, 'winery_id' => $hidden->id,  'assigned_by' => $supervisor->id]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('search', 'seleccionada')
            ->assertViewHas('items', fn ($items) =>
                $items->pluck('id')->contains($visible->id) &&
                !$items->pluck('id')->contains($hidden->id)
            );
    }

    public function test_clear_search_resets_filter(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('search', 'xyz')
            ->assertViewHas('items', fn ($i) => $i->isEmpty())
            ->call('clearSearch')
            ->assertSet('search', '')
            ->assertViewHas('items', fn ($i) => $i->isNotEmpty());
    }

    // ── search in viticulturists tab ──────────────────────────────────────────

    public function test_search_filters_viticulturists_by_name(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $visible = User::factory()->create(['role' => 'viticulturist', 'name' => 'Ana Viñedo']);
        $hidden  = User::factory()->create(['role' => 'viticulturist', 'name' => 'Pedro Campo']);

        WineryViticulturist::create(['supervisor_id' => $supervisor->id, 'winery_id' => $winery->id, 'viticulturist_id' => $visible->id, 'source' => WineryViticulturist::SOURCE_SUPERVISOR, 'assigned_by' => $supervisor->id]);
        WineryViticulturist::create(['supervisor_id' => $supervisor->id, 'winery_id' => $winery->id, 'viticulturist_id' => $hidden->id,  'source' => WineryViticulturist::SOURCE_SUPERVISOR, 'assigned_by' => $supervisor->id]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('switchTab', 'viticulturists')
            ->set('search', 'viñedo')
            ->assertViewHas('items', fn ($items) =>
                $items->pluck('id')->contains($visible->id) &&
                !$items->pluck('id')->contains($hidden->id)
            );
    }
}
