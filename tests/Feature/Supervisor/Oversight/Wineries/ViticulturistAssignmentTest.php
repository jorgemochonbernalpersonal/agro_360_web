<?php

namespace Tests\Feature\Supervisor\Oversight\Wineries;

use App\Livewire\Supervisor\Oversight\Wineries\Show;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class ViticulturistAssignmentTest extends SupervisorTestCase
{
    // ── helpers ───────────────────────────────────────────────────────────────

    /** Añade un viticultor al pool del supervisor */
    private function addToPool(User $supervisor, User $viticulturist): void
    {
        SupervisorViticulturist::create([
            'supervisor_id'    => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'assigned_by'      => $supervisor->id,
        ]);
    }

    // ── asignar ───────────────────────────────────────────────────────────────

    public function test_supervisor_can_assign_pool_viticulturist_to_winery(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = User::factory()->create(['role' => 'viticulturist']);
        $this->addToPool($supervisor, $vit);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->call('assignViticulturist', $vit->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('winery_viticulturist', [
            'winery_id'        => $winery->id,
            'viticulturist_id' => $vit->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'    => $supervisor->id,
        ]);
    }

    public function test_assign_is_idempotent(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = User::factory()->create(['role' => 'viticulturist']);
        $this->addToPool($supervisor, $vit);

        $component = Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery]);

        $component->call('assignViticulturist', $vit->id);
        $component->call('assignViticulturist', $vit->id);  // segunda vez

        $this->assertDatabaseCount('winery_viticulturist', 1);
    }

    public function test_supervisor_cannot_assign_viticulturist_not_in_their_pool(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $outsider = User::factory()->create(['role' => 'viticulturist']);
        // outsider NO está en el pool

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->call('assignViticulturist', $outsider->id);
    }

    // ── retirar ───────────────────────────────────────────────────────────────

    public function test_supervisor_can_unassign_viticulturist(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = User::factory()->create(['role' => 'viticulturist']);
        $this->addToPool($supervisor, $vit);

        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $vit->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'    => $supervisor->id,
            'assigned_by'      => $supervisor->id,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->call('unassignViticulturist', $vit->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('winery_viticulturist', [
            'winery_id'        => $winery->id,
            'viticulturist_id' => $vit->id,
            'supervisor_id'    => $supervisor->id,
        ]);
    }

    public function test_supervisor_cannot_unassign_viticulturist_from_another_supervisor(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $otherSupervisor       = $this->makeSupervisor();
        $vit = User::factory()->create(['role' => 'viticulturist']);

        // Asignado por OTRO supervisor
        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $vit->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'    => $otherSupervisor->id,
            'assigned_by'      => $otherSupervisor->id,
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->call('unassignViticulturist', $vit->id);
    }

    // ── pool visible en modal ─────────────────────────────────────────────────

    public function test_pool_viticulturist_appears_in_modal_list(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = User::factory()->create(['role' => 'viticulturist', 'name' => 'Viticultor Pool']);
        $this->addToPool($supervisor, $vit);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->assertSee('Viticultor Pool');
    }

    public function test_already_assigned_viticulturist_not_in_pool_list(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = User::factory()->create(['role' => 'viticulturist', 'name' => 'Ya Asignado']);
        $this->addToPool($supervisor, $vit);

        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $vit->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'    => $supervisor->id,
            'assigned_by'      => $supervisor->id,
        ]);

        // Aparece en la tabla de asignados pero NO en el pool (available)
        // El componente muestra el nombre en la tabla de asignados
        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->assertViewHas('poolViticulturists', fn ($pool) => $pool->doesntContain('id', $vit->id));
    }
}
