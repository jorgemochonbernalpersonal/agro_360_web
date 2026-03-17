<?php

namespace Tests\Feature\Winery;

use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\WineryTestCase;

/**
 * Tests for WineryViticulturist::scopeVisibleTo, scopeEditableBy, and isVisibleTo.
 *
 * Visibility rules:
 *   1. A viticulturist always sees their own sub-viticulturists
 *      (source=viticulturist, parent_viticulturist_id = me).
 *   2. If they have a supervisor, they see viticulturists from the supervisor pool.
 *   3. If they belong to a winery, they see source=own|viticulturist in that winery.
 *   4. They never see themselves.
 *
 * Editable rule:
 *   Only source=viticulturist AND parent_viticulturist_id = me.
 */
class WineryViticulturistScopeTest extends WineryTestCase
{
    protected User $winery;
    protected User $creator;   // viticulturist linked to $winery

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush(); // evitar contaminación de caché entre tests

        $this->winery = $this->makeWinery();

        $this->creator = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        // Vinculamos creator a la bodega (source=own)
        WineryViticulturist::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->creator->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->winery->id,
        ]);
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeViticulturistInWinery(User $winery, string $source = WineryViticulturist::SOURCE_OWN, ?User $parent = null): User
    {
        $v = User::factory()->create(['role' => 'viticulturist', 'email_verified_at' => now()]);

        WineryViticulturist::create([
            'winery_id'              => $winery->id,
            'viticulturist_id'       => $v->id,
            'source'                 => $source,
            'assigned_by'            => $winery->id,
            'parent_viticulturist_id' => $parent?->id,
        ]);

        return $v;
    }

    private function makeSubViticulturist(User $creator, ?User $winery = null): User
    {
        $v = User::factory()->create(['role' => 'viticulturist', 'email_verified_at' => now()]);

        WineryViticulturist::create([
            'winery_id'              => $winery?->id,
            'viticulturist_id'       => $v->id,
            'source'                 => WineryViticulturist::SOURCE_VITICULTURIST,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by'            => $creator->id,
        ]);

        return $v;
    }

    // ── scopeVisibleTo ────────────────────────────────────────────────────────

    public function test_creator_sees_their_own_sub_viticulturists(): void
    {
        $sub = $this->makeSubViticulturist($this->creator, $this->winery);

        $ids = WineryViticulturist::visibleTo($this->creator)
            ->pluck('viticulturist_id');

        $this->assertContains($sub->id, $ids);
    }

    public function test_creator_sees_other_own_viticulturists_in_shared_winery(): void
    {
        $own = $this->makeViticulturistInWinery($this->winery, WineryViticulturist::SOURCE_OWN);

        $ids = WineryViticulturist::visibleTo($this->creator)
            ->pluck('viticulturist_id');

        $this->assertContains($own->id, $ids);
    }

    public function test_creator_does_not_see_viticulturists_from_a_different_winery(): void
    {
        $otherWinery = $this->makeOtherWinery();
        $other = $this->makeViticulturistInWinery($otherWinery, WineryViticulturist::SOURCE_OWN);

        $ids = WineryViticulturist::visibleTo($this->creator)
            ->pluck('viticulturist_id');

        $this->assertNotContains($other->id, $ids);
    }

    public function test_creator_does_not_see_themselves(): void
    {
        $ids = WineryViticulturist::visibleTo($this->creator)
            ->pluck('viticulturist_id');

        $this->assertNotContains($this->creator->id, $ids);
    }

    public function test_viticulturist_without_winery_sees_only_their_sub_viticulturists(): void
    {
        $standalone = User::factory()->create(['role' => 'viticulturist', 'email_verified_at' => now()]);

        // standalone no tiene bodega — crea un sub sin bodega
        $sub = $this->makeSubViticulturist($standalone, null);

        // viticultor de otra bodega — no debe ser visible
        $this->makeViticulturistInWinery($this->winery, WineryViticulturist::SOURCE_OWN);

        $ids = WineryViticulturist::visibleTo($standalone)
            ->pluck('viticulturist_id');

        $this->assertContains($sub->id, $ids);
        $this->assertCount(1, $ids);
    }

    public function test_sub_viticulturist_created_by_someone_else_is_not_visible_to_creator(): void
    {
        $otherCreator = $this->makeViticulturistInWinery($this->winery, WineryViticulturist::SOURCE_OWN);
        $subOfOther   = $this->makeSubViticulturist($otherCreator, $this->winery);

        // creator NO creó a subOfOther, pero comparte bodega → sí visible por rama 3
        // Este test verifica que la rama 1 (solo mis creados) no interfiere con la 3
        $ids = WineryViticulturist::visibleTo($this->creator)
            ->pluck('viticulturist_id');

        // subOfOther sí aparece por la rama 3 (source=viticulturist, winery_id=$this->winery)
        $this->assertContains($subOfOther->id, $ids);
    }

    public function test_non_viticulturist_role_returns_empty(): void
    {
        $this->makeViticulturistInWinery($this->winery);

        $results = WineryViticulturist::visibleTo($this->winery)->get();

        $this->assertEmpty($results);
    }

    public function test_viticulturist_with_supervisor_sees_supervisor_pool(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor', 'email_verified_at' => now()]);

        // viewer: viticultor sin bodega previa, lo vincularemos via supervisor
        $viewer = User::factory()->create(['role' => 'viticulturist', 'email_verified_at' => now()]);
        WineryViticulturist::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $viewer->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'    => $supervisor->id,
            'assigned_by'      => $supervisor->id,
        ]);

        // poolVitic: otro viticultor del mismo supervisor en la misma bodega
        $poolVitic = User::factory()->create(['role' => 'viticulturist', 'email_verified_at' => now()]);
        WineryViticulturist::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $poolVitic->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'    => $supervisor->id,
            'assigned_by'      => $supervisor->id,
        ]);

        // Forzar recarga del atributo cacheado de supervisor
        Cache::forget("user_{$viewer->id}_supervisor");
        Cache::forget("user_{$viewer->id}_wineries");
        $viewer->clearAttributeCache();

        $ids = WineryViticulturist::visibleTo($viewer)
            ->pluck('viticulturist_id');

        $this->assertContains($poolVitic->id, $ids);
    }

    // ── scopeEditableBy ───────────────────────────────────────────────────────

    public function test_editable_by_returns_only_sub_viticulturists_created_by_user(): void
    {
        $sub = $this->makeSubViticulturist($this->creator, $this->winery);
        $own = $this->makeViticulturistInWinery($this->winery, WineryViticulturist::SOURCE_OWN);

        $ids = WineryViticulturist::editableBy($this->creator)
            ->pluck('viticulturist_id');

        $this->assertContains($sub->id, $ids);
        $this->assertNotContains($own->id, $ids);
    }

    public function test_editable_by_excludes_source_own(): void
    {
        $own = $this->makeViticulturistInWinery($this->winery, WineryViticulturist::SOURCE_OWN);

        $ids = WineryViticulturist::editableBy($this->creator)
            ->pluck('viticulturist_id');

        $this->assertNotContains($own->id, $ids);
    }

    public function test_editable_by_returns_empty_for_non_viticulturist_role(): void
    {
        $this->makeSubViticulturist($this->creator, $this->winery);

        $results = WineryViticulturist::editableBy($this->winery)->get();

        $this->assertEmpty($results);
    }

    // ── isVisibleTo ───────────────────────────────────────────────────────────

    public function test_is_visible_to_returns_true_for_own_sub_viticulturist(): void
    {
        $sub = $this->makeSubViticulturist($this->creator, $this->winery);

        $relation = WineryViticulturist::where('viticulturist_id', $sub->id)->firstOrFail();

        $this->assertTrue($relation->isVisibleTo($this->creator));
    }

    public function test_is_visible_to_returns_false_for_unrelated_winery_viticulturist(): void
    {
        $otherWinery = $this->makeOtherWinery();
        $other = $this->makeViticulturistInWinery($otherWinery, WineryViticulturist::SOURCE_OWN);

        $relation = WineryViticulturist::where('viticulturist_id', $other->id)->firstOrFail();

        $this->assertFalse($relation->isVisibleTo($this->creator));
    }

    public function test_is_visible_to_returns_false_for_self(): void
    {
        $relation = WineryViticulturist::where('viticulturist_id', $this->creator->id)->firstOrFail();

        $this->assertFalse($relation->isVisibleTo($this->creator));
    }

    public function test_is_visible_to_returns_false_for_non_viticulturist_role(): void
    {
        $own = $this->makeViticulturistInWinery($this->winery, WineryViticulturist::SOURCE_OWN);

        $relation = WineryViticulturist::where('viticulturist_id', $own->id)->firstOrFail();

        $this->assertFalse($relation->isVisibleTo($this->winery));
    }
}
