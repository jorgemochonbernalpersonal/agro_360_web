<?php

namespace Tests\Feature\Supervisor\Census;

use App\Livewire\Supervisor\Census\Index;
use App\Models\SupervisorWinery;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class WineryAssignmentTest extends SupervisorTestCase
{
    // ── render ────────────────────────────────────────────────────────────────

    public function test_census_renders(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('supervisor.census.index'))
            ->assertOk();
    }

    // ── adscribir ─────────────────────────────────────────────────────────────

    public function test_supervisor_can_assign_winery(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery     = $this->makeWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('assignWinery', $winery->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('supervisor_winery', [
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery->id,
        ]);
    }

    public function test_assign_is_idempotent(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery     = $this->makeWinery();

        $component = Livewire::actingAs($supervisor)->test(Index::class);
        $component->call('assignWinery', $winery->id);
        $component->call('assignWinery', $winery->id); // segunda vez

        $this->assertDatabaseCount('supervisor_winery', 1);
    }

    public function test_cannot_assign_non_winery_user(): void
    {
        $supervisor = $this->makeSupervisor();
        $other      = User::factory()->create(['role' => 'viticulturist']);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('assignWinery', $other->id);
    }

    // ── desadscribir ──────────────────────────────────────────────────────────

    public function test_supervisor_can_unassign_winery(): void
    {
        // La D.O. debe mantener al menos una bodega, así que partimos de dos
        // para poder desadscribir una.
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $second = $this->makeWinery();
        SupervisorWinery::create([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $second->id,
            'assigned_by'   => $supervisor->id,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('unassignWinery', $winery->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('supervisor_winery', [
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery->id,
        ]);
    }

    public function test_supervisor_cannot_unassign_last_winery(): void
    {
        // Regla de negocio: una D.O. debe mantener al menos una bodega asociada.
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('unassignWinery', $winery->id)
            ->assertDispatched('toast');

        // La única bodega sigue asociada: no se permite dejar la D.O. huérfana.
        $this->assertDatabaseHas('supervisor_winery', [
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery->id,
        ]);
    }

    public function test_supervisor_cannot_unassign_winery_of_another_supervisor(): void
    {
        $supervisor      = $this->makeSupervisor();
        $otherSupervisor = $this->makeSupervisor();
        $winery          = $this->makeWinery();

        SupervisorWinery::create([
            'supervisor_id' => $otherSupervisor->id,
            'winery_id'     => $winery->id,
            'assigned_by'   => $otherSupervisor->id,
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('unassignWinery', $winery->id);
    }

    // ── lista de disponibles ──────────────────────────────────────────────────

    public function test_available_wineries_excludes_already_assigned(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $other = $this->makeWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openAssignModal')
            ->assertViewHas('availableWineries', fn ($list) => $list->contains('id', $other->id))
            ->assertViewHas('availableWineries', fn ($list) => $list->doesntContain('id', $winery->id));
    }

    public function test_assigned_winery_appears_in_census_table(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertSee($winery->name);
    }
}
