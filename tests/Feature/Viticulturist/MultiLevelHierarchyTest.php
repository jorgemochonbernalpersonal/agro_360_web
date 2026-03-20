<?php

namespace Tests\Feature\Viticulturist;

use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifica el comportamiento de visibilidad en jerarquías multi-nivel.
 *
 * La jerarquía viticulturist → sub-viticulturist solo tiene garantía
 * de visibilidad directa (un nivel). El scope scopeVisibleTo trabaja con:
 *
 *   Rama 1: parent_viticulturist_id = me.id AND source = VITICULTURIST
 *            → viticultores que YO creé directamente
 *   Rama 3: winery_id IN mis_bodegas AND source IN [own, viticulturist]
 *            → cualquiera en mi misma bodega
 *
 * Un sub-viticulturist creado por B (donde B es hijo de A) es visible para A
 * SOLO si comparte bodega con A (rama 3), NO directamente por parentesco.
 */
class MultiLevelHierarchyTest extends TestCase
{
    use RefreshDatabase;

    private User $winery;
    private User $viticulturistA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->winery = User::factory()->create(['role' => 'winery']);

        $this->viticulturistA = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        WineryViticulturist::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturistA->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->winery->id,
        ]);
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeChildOf(User $parent, ?User $winery = null): User
    {
        $child = User::factory()->create(['role' => 'viticulturist', 'email_verified_at' => now()]);

        WineryViticulturist::create([
            'winery_id'               => $winery?->id,
            'viticulturist_id'        => $child->id,
            'source'                  => WineryViticulturist::SOURCE_VITICULTURIST,
            'parent_viticulturist_id' => $parent->id,
            'assigned_by'             => $parent->id,
        ]);

        return $child;
    }

    // ── Nivel 1: A ve a B directamente (rama 1) ───────────────────────────────

    public function test_a_sees_directly_created_viticulturist_b(): void
    {
        $viticulturistB = $this->makeChildOf($this->viticulturistA, $this->winery);

        $this->viticulturistA->clearAttributeCache();

        $ids = WineryViticulturist::visibleTo($this->viticulturistA)->pluck('viticulturist_id');

        $this->assertContains($viticulturistB->id, $ids);
    }

    public function test_b_sees_their_own_parent_a_only_via_shared_winery_not_direct(): void
    {
        $viticulturistB = $this->makeChildOf($this->viticulturistA, $this->winery);

        $viticulturistB->clearAttributeCache();

        // B's scope: rama 1 = viticultores CREADOS por B (ninguno aún)
        //            rama 3 = viticultores en la misma bodega (incluye A y otros)
        $ids = WineryViticulturist::visibleTo($viticulturistB)->pluck('viticulturist_id');

        // B sees A via branch 3 (shared winery), not because of parentage
        $this->assertContains($this->viticulturistA->id, $ids);
    }

    // ── Nivel 2: A NO ve a C directamente (solo por bodega compartida) ─────────

    public function test_a_does_not_see_c_created_by_b_when_c_has_no_shared_winery(): void
    {
        $viticulturistB = $this->makeChildOf($this->viticulturistA, $this->winery);

        // C es creado por B pero SIN asignar a ninguna bodega
        $viticulturistC = $this->makeChildOf($viticulturistB, winery: null);

        $this->viticulturistA->clearAttributeCache();

        $ids = WineryViticulturist::visibleTo($this->viticulturistA)->pluck('viticulturist_id');

        // A no creó a C directamente (rama 1 falla)
        // C no comparte bodega con A (rama 3 falla: winery_id es null para C)
        $this->assertNotContains($viticulturistC->id, $ids);
    }

    public function test_a_sees_c_created_by_b_when_c_shares_winery_with_a(): void
    {
        $viticulturistB = $this->makeChildOf($this->viticulturistA, $this->winery);

        // C es creado por B Y asignado a la misma bodega que A
        $viticulturistC = $this->makeChildOf($viticulturistB, $this->winery);

        $this->viticulturistA->clearAttributeCache();

        $ids = WineryViticulturist::visibleTo($this->viticulturistA)->pluck('viticulturist_id');

        // A ve a C por rama 3 (winery_id compartido), NO por parentesco directo
        $this->assertContains($viticulturistC->id, $ids);
    }

    // ── editableBy es estrictamente de 1 nivel ─────────────────────────────────

    public function test_a_can_edit_b_but_not_c(): void
    {
        $viticulturistB = $this->makeChildOf($this->viticulturistA, $this->winery);
        $viticulturistC = $this->makeChildOf($viticulturistB, $this->winery);

        $editableIds = WineryViticulturist::editableBy($this->viticulturistA)->pluck('viticulturist_id');

        $this->assertContains($viticulturistB->id, $editableIds);
        $this->assertNotContains($viticulturistC->id, $editableIds);
    }

    public function test_b_can_edit_c_but_not_a(): void
    {
        $viticulturistB = $this->makeChildOf($this->viticulturistA, $this->winery);
        $viticulturistC = $this->makeChildOf($viticulturistB, $this->winery);

        $editableIds = WineryViticulturist::editableBy($viticulturistB)->pluck('viticulturist_id');

        $this->assertContains($viticulturistC->id, $editableIds);
        $this->assertNotContains($this->viticulturistA->id, $editableIds);
    }

    // ── Viticulturist sin bodega solo ve a sus hijos directos ─────────────────

    public function test_standalone_viticulturist_sees_only_direct_children(): void
    {
        // standalone no tiene bodega
        $standalone = User::factory()->create(['role' => 'viticulturist', 'email_verified_at' => now()]);

        $child = $this->makeChildOf($standalone, winery: null);

        // C hijo de child, sin bodega → standalone NO lo ve
        $grandchild = $this->makeChildOf($child, winery: null);

        $standalone->clearAttributeCache();

        $ids = WineryViticulturist::visibleTo($standalone)->pluck('viticulturist_id');

        $this->assertContains($child->id, $ids);
        $this->assertNotContains($grandchild->id, $ids);
    }

    // ── A no se ve a sí mismo ─────────────────────────────────────────────────

    public function test_viticulturist_does_not_see_themselves(): void
    {
        $this->viticulturistA->clearAttributeCache();

        $ids = WineryViticulturist::visibleTo($this->viticulturistA)->pluck('viticulturist_id');

        $this->assertNotContains($this->viticulturistA->id, $ids);
    }

    // ── isVisibleTo coincide con scopeVisibleTo ───────────────────────────────

    public function test_is_visible_to_matches_scope_for_direct_child(): void
    {
        $viticulturistB = $this->makeChildOf($this->viticulturistA, $this->winery);

        $this->viticulturistA->clearAttributeCache();

        $relation = WineryViticulturist::where('viticulturist_id', $viticulturistB->id)->first();

        $this->assertTrue($relation->isVisibleTo($this->viticulturistA));
    }

    public function test_is_visible_to_false_for_grandchild_without_shared_winery(): void
    {
        $viticulturistB = $this->makeChildOf($this->viticulturistA, $this->winery);
        $viticulturistC = $this->makeChildOf($viticulturistB, winery: null);

        $this->viticulturistA->clearAttributeCache();

        $relation = WineryViticulturist::where('viticulturist_id', $viticulturistC->id)->first();

        $this->assertFalse($relation->isVisibleTo($this->viticulturistA));
    }
}
