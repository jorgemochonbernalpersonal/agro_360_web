<?php

namespace Tests\Feature\Supervisor\Oversight\Wineries;

use App\Livewire\Supervisor\Oversight\Wineries\Show;
use App\Models\SupervisorWinery;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class ToggleAccessTest extends SupervisorTestCase
{
    // ── desactivar ────────────────────────────────────────────────────────────

    public function test_supervisor_can_deactivate_winery(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $winery->update(['can_login' => true]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->call('toggleAccess')
            ->assertDispatched('toast');

        $this->assertFalse($winery->fresh()->can_login);
    }

    // ── activar ───────────────────────────────────────────────────────────────

    public function test_supervisor_can_activate_winery(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $winery->update(['can_login' => false]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->call('toggleAccess')
            ->assertDispatched('toast');

        $this->assertTrue($winery->fresh()->can_login);
    }

    // ── autorización ──────────────────────────────────────────────────────────

    public function test_another_supervisor_cannot_toggle_access(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $otherSupervisor = $this->makeSupervisor();

        // otherSupervisor NO tiene SupervisorWinery con esta bodega
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($otherSupervisor)
            ->test(Show::class, ['winery' => $winery]);
    }

    // ── badge refleja estado ──────────────────────────────────────────────────

    public function test_show_reflects_new_state_after_deactivation(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $winery->update(['can_login' => true]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->call('toggleAccess')
            ->assertSee('Sin acceso');
    }

    public function test_show_reflects_new_state_after_activation(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $winery->update(['can_login' => false]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->call('toggleAccess')
            ->assertSee('Activa');
    }
}
