<?php

namespace Tests\Feature\Supervisor\Oversight\Wineries;

use App\Livewire\Supervisor\Oversight\Wineries\Show;
use App\Models\SupervisorWinery;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class ShowTest extends SupervisorTestCase
{
    // ── render ────────────────────────────────────────────────────────────────

    public function test_show_renders_for_assigned_winery(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->actingAs($supervisor)
            ->get(route('supervisor.oversight.wineries.show', $winery))
            ->assertOk();
    }

    public function test_show_displays_winery_name(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->actingAs($supervisor)
            ->get(route('supervisor.oversight.wineries.show', $winery))
            ->assertSee($winery->name);
    }

    // ── autorización ──────────────────────────────────────────────────────────

    public function test_supervisor_cannot_see_winery_not_assigned_to_them(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();
        // Sin SupervisorWinery — no está adscrita

        $this->actingAs($supervisor)
            ->get(route('supervisor.oversight.wineries.show', $winery))
            ->assertNotFound();
    }

    public function test_another_supervisor_cannot_see_winery(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $otherSupervisor = $this->makeSupervisor();

        $this->actingAs($otherSupervisor)
            ->get(route('supervisor.oversight.wineries.show', $winery))
            ->assertNotFound();
    }

    // ── viticultores ──────────────────────────────────────────────────────────

    public function test_shows_viticulturists_assigned_by_this_supervisor(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $viticulturist = User::factory()->create(['role' => 'viticulturist', 'name' => 'Viticultor Asignado']);

        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id' => $supervisor->id,
            'assigned_by' => $supervisor->id,
        ]);

        $this->actingAs($supervisor)
            ->get(route('supervisor.oversight.wineries.show', $winery))
            ->assertSee('Viticultor Asignado');
    }

    public function test_does_not_show_viticulturists_from_another_supervisor(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $otherSupervisor = $this->makeSupervisor();

        $viticulturist = User::factory()->create(['role' => 'viticulturist', 'name' => 'Viticultor Ajeno']);

        // Mismo winery, pero asignado por otro supervisor
        SupervisorWinery::create([
            'supervisor_id' => $otherSupervisor->id,
            'winery_id' => $winery->id,
            'assigned_by' => $otherSupervisor->id,
        ]);

        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id' => $otherSupervisor->id,
            'assigned_by' => $otherSupervisor->id,
        ]);

        $this->actingAs($supervisor)
            ->get(route('supervisor.oversight.wineries.show', $winery))
            ->assertDontSee('Viticultor Ajeno');
    }

    // ── estado activo/inactivo ────────────────────────────────────────────────

    public function test_shows_active_badge_when_winery_can_login(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $winery->update(['can_login' => true]);

        $this->actingAs($supervisor)
            ->get(route('supervisor.oversight.wineries.show', $winery))
            ->assertSee('Activa');
    }

    public function test_shows_inactive_badge_when_winery_cannot_login(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $winery->update(['can_login' => false]);

        $this->actingAs($supervisor)
            ->get(route('supervisor.oversight.wineries.show', $winery))
            ->assertSee('Sin acceso');
    }

    // ── livewire mount guard ──────────────────────────────────────────────────

    public function test_livewire_component_throws_not_found_for_unassigned_winery(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();

        $this->actingAs($supervisor);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Show::class, ['winery' => $winery]);
    }
}
