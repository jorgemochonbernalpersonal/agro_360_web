<?php

namespace Tests\Feature\Winery;

use App\Livewire\Winery\Viticulturists\Edit;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

/**
 * Verifica que el componente Edit de viticultores (Winery\Viticulturists\Edit)
 * bloquea el acceso a viticultores asignados por DO (source=supervisor).
 *
 * Regla de negocio: la bodega puede VER los viticultores del DO pero NO editarlos.
 * El mount() del componente usa ->where('source', SOURCE_OWN)->firstOrFail()
 * para forzar este aislamiento.
 */
class SupervisorSourceEditGuardTest extends WineryTestCase
{
    protected User $winery;
    protected User $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->winery     = $this->makeWinery();
        $this->supervisor = User::factory()->create(['role' => 'supervisor']);

        $this->actingAs($this->winery);
    }

    // ── source=supervisor → edición bloqueada ─────────────────────────────────

    public function test_winery_cannot_edit_supervisor_assigned_viticulturist(): void
    {
        $vitic = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $vitic->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'    => $this->supervisor->id,
            'assigned_by'      => $this->supervisor->id,
        ]);

        // mount() filters ->where('source', SOURCE_OWN) → firstOrFail throws
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Edit::class, ['viticulturist' => $vitic]);
    }

    // ── source=own → edición permitida ───────────────────────────────────────

    public function test_winery_can_edit_own_assigned_viticulturist(): void
    {
        $vitic = User::factory()->create([
            'role'      => 'viticulturist',
            'can_login' => false,
            'email'     => 'viticultores.' . uniqid() . '@placeholder.agro365.es',
        ]);

        WineryViticulturist::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $vitic->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->winery->id,
        ]);

        Livewire::test(Edit::class, ['viticulturist' => $vitic])
            ->assertOk();
    }

    // ── source=own de otra bodega → bloqueada ────────────────────────────────

    public function test_winery_cannot_edit_viticulturist_owned_by_different_winery(): void
    {
        $otherWinery = $this->makeOtherWinery();
        $vitic       = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'winery_id'        => $otherWinery->id,
            'viticulturist_id' => $vitic->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $otherWinery->id,
        ]);

        // No relation exists for $this->winery → firstOrFail throws
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Edit::class, ['viticulturist' => $vitic]);
    }

    // ── source=viticulturist (sub-viticulturist) → bloqueada para winery ─────

    public function test_winery_cannot_edit_sub_viticulturist_created_by_another_viticulturist(): void
    {
        $parentVitic = User::factory()->create(['role' => 'viticulturist']);
        $subVitic    = User::factory()->create(['role' => 'viticulturist']);

        // Sub-viticulturist created by parentVitic (not by the winery directly)
        WineryViticulturist::create([
            'winery_id'               => $this->winery->id,
            'viticulturist_id'        => $subVitic->id,
            'source'                  => WineryViticulturist::SOURCE_VITICULTURIST,
            'parent_viticulturist_id' => $parentVitic->id,
            'assigned_by'             => $parentVitic->id,
        ]);

        // source != OWN → mount() rejects it
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Edit::class, ['viticulturist' => $subVitic]);
    }
}
