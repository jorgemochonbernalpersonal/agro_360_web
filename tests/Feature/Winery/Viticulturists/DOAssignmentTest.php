<?php

namespace Tests\Feature\Winery\Viticulturists;

use App\Livewire\Winery\Viticulturists\Index;
use App\Models\SupervisorViticulturist;
use App\Models\SupervisorWinery;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

/**
 * Winery self-assignment of supervisor (D.O.) viticulturists.
 */
class DOAssignmentTest extends WineryTestCase
{
    // ── assign ────────────────────────────────────────────────────────────────

    public function test_winery_can_assign_supervisor_viticulturist(): void
    {
        $winery = $this->makeWinery();
        $supervisor = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturist();

        $this->linkSupervisorToWinery($supervisor, $winery);
        $this->addToSupervisorPool($supervisor, $viticulturist);

        $this->actingAs($winery);

        Livewire::test(Index::class)
            ->call('assignFromDO', $viticulturist->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('winery_viticulturist', [
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id' => $supervisor->id,
        ]);
    }

    public function test_assign_is_idempotent_when_already_assigned(): void
    {
        $winery = $this->makeWinery();
        $supervisor = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturist();

        $this->linkSupervisorToWinery($supervisor, $winery);
        $this->addToSupervisorPool($supervisor, $viticulturist);

        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id' => $supervisor->id,
            'assigned_by' => $winery->id,
        ]);

        $this->actingAs($winery);

        Livewire::test(Index::class)
            ->call('assignFromDO', $viticulturist->id);

        $this->assertSame(1, WineryViticulturist::where('winery_id', $winery->id)
            ->where('viticulturist_id', $viticulturist->id)
            ->count());
    }

    public function test_winery_cannot_assign_viticulturist_not_in_supervisor_pool(): void
    {
        $winery = $this->makeWinery();
        $supervisor = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturist();

        $this->linkSupervisorToWinery($supervisor, $winery);
        // NOT added to pool

        $this->actingAs($winery);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('assignFromDO', $viticulturist->id);
    }

    // ── unassign ──────────────────────────────────────────────────────────────

    public function test_winery_can_unassign_supervisor_viticulturist(): void
    {
        $winery = $this->makeWinery();
        $supervisor = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturist();

        $this->linkSupervisorToWinery($supervisor, $winery);

        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id' => $supervisor->id,
            'assigned_by' => $winery->id,
        ]);

        $this->actingAs($winery);

        Livewire::test(Index::class)
            ->call('unassignFromDO', $viticulturist->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('winery_viticulturist', [
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
        ]);
    }

    public function test_winery_cannot_unassign_own_viticulturist(): void
    {
        $winery = $this->makeWinery();
        $viticulturist = $this->makeViticulturist();

        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        $this->actingAs($winery);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('unassignFromDO', $viticulturist->id);
    }

    // ── pool visibility ───────────────────────────────────────────────────────

    public function test_do_button_not_shown_when_no_supervisor_linked(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery);

        Livewire::test(Index::class)
            ->assertViewHas('hasSupervisors', false);
    }

    public function test_do_button_shown_when_supervisor_linked(): void
    {
        $winery = $this->makeWinery();
        $supervisor = $this->makeSupervisor();

        $this->linkSupervisorToWinery($supervisor, $winery);

        $this->actingAs($winery);

        Livewire::test(Index::class)
            ->assertViewHas('hasSupervisors', true);
    }

    private function makeSupervisor(): User
    {
        return User::factory()->create([
            'role' => 'supervisor',
            'email_verified_at' => now(),
        ]);
    }

    private function linkSupervisorToWinery(User $supervisor, User $winery): SupervisorWinery
    {
        return SupervisorWinery::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'assigned_by' => $supervisor->id,
        ]);
    }

    private function addToSupervisorPool(User $supervisor, User $viticulturist): SupervisorViticulturist
    {
        return SupervisorViticulturist::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'assigned_by' => $supervisor->id,
        ]);
    }

    private function makeViticulturist(): User
    {
        return User::factory()->create([
            'role' => 'viticulturist',
            'can_login' => true,
        ]);
    }
}
