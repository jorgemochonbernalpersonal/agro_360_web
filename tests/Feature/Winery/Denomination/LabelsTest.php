<?php

namespace Tests\Feature\Winery\Denomination;

use App\Livewire\Winery\Denomination\Labels\Index;
use App\Models\DoLabel;
use App\Models\SupervisorWinery;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class LabelsTest extends WineryTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_winery_can_access_denomination_labels(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('winery.denomination.labels.index'))
            ->assertOk();
    }

    public function test_supervisor_cannot_access_denomination_labels(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('winery.denomination.labels.index'))
            ->assertForbidden();
    }

    // ── scoping ───────────────────────────────────────────────────────────────

    public function test_winery_sees_only_own_labels(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();
        $other = $this->makeOtherWinery();

        $this->link($supervisor, $winery);
        $this->link($supervisor, $other);

        $own = DoLabel::create(['supervisor_id' => $supervisor->id, 'winery_id' => $winery->id, 'vintage' => 2025, 'quantity_requested' => 100]);
        $theirs = DoLabel::create(['supervisor_id' => $supervisor->id, 'winery_id' => $other->id, 'vintage' => 2025, 'quantity_requested' => 200]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertViewHas('labels', fn ($l) => $l->contains('id', $own->id) && ! $l->contains('id', $theirs->id));
    }

    // ── counts ────────────────────────────────────────────────────────────────

    public function test_counts_reflect_label_statuses(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();
        $this->link($supervisor, $winery);

        DoLabel::create(['supervisor_id' => $supervisor->id, 'winery_id' => $winery->id, 'vintage' => 2025, 'quantity_requested' => 100, 'status' => 'pending']);
        DoLabel::create(['supervisor_id' => $supervisor->id, 'winery_id' => $winery->id, 'vintage' => 2025, 'quantity_requested' => 200, 'status' => 'issued', 'quantity_issued' => 200, 'issued_at' => now()]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertViewHas('counts', fn ($c) => $c['all'] === 2 && $c['pending'] === 1 && $c['issued'] === 1);
    }

    // ── filters ───────────────────────────────────────────────────────────────

    public function test_vintage_filter_narrows_results(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();
        $this->link($supervisor, $winery);

        $a = DoLabel::create(['supervisor_id' => $supervisor->id, 'winery_id' => $winery->id, 'vintage' => 2024, 'quantity_requested' => 100]);
        $b = DoLabel::create(['supervisor_id' => $supervisor->id, 'winery_id' => $winery->id, 'vintage' => 2025, 'quantity_requested' => 100]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('vintageFilter', '2024')
            ->assertViewHas('labels', fn ($l) => $l->contains('id', $a->id) && ! $l->contains('id', $b->id));
    }

    public function test_status_filter_narrows_results(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();
        $this->link($supervisor, $winery);

        $pending = DoLabel::create(['supervisor_id' => $supervisor->id, 'winery_id' => $winery->id, 'vintage' => 2025, 'quantity_requested' => 100, 'status' => 'pending']);
        $approved = DoLabel::create(['supervisor_id' => $supervisor->id, 'winery_id' => $winery->id, 'vintage' => 2025, 'quantity_requested' => 100, 'status' => 'approved']);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('statusFilter', 'pending')
            ->assertViewHas('labels', fn ($l) => $l->contains('id', $pending->id) && ! $l->contains('id', $approved->id));
    }

    private function makeSupervisor(): User
    {
        return User::factory()->create(['role' => 'supervisor', 'email_verified_at' => now()]);
    }

    private function link(User $supervisor, User $winery): void
    {
        SupervisorWinery::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'assigned_by' => $supervisor->id,
        ]);
    }
}
