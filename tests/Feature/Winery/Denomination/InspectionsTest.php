<?php

namespace Tests\Feature\Winery\Denomination;

use App\Livewire\Winery\Denomination\Inspections\Index;
use App\Models\DoInspection;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class InspectionsTest extends WineryTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_winery_can_access_denomination_inspections(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('winery.denomination.inspections.index'))
            ->assertOk();
    }

    public function test_supervisor_cannot_access_denomination_inspections(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('winery.denomination.inspections.index'))
            ->assertForbidden();
    }

    // ── scoping ───────────────────────────────────────────────────────────────

    public function test_winery_sees_only_inspections_targeting_it(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();
        $other = $this->makeOtherWinery();

        $own = $this->makeInspection($supervisor, $winery);
        $theirs = $this->makeInspection($supervisor, $other);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertViewHas('inspections', fn ($i) => $i->contains('id', $own->id) && ! $i->contains('id', $theirs->id));
    }

    public function test_viticulturist_subject_inspections_not_visible_to_winery(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        DoInspection::create([
            'supervisor_id' => $supervisor->id,
            'subject_type' => 'viticulturist',
            'subject_id' => $viticulturist->id,
            'inspection_date' => now()->toDateString(),
        ]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertViewHas('inspections', fn ($i) => $i->isEmpty());
    }

    // ── counts ────────────────────────────────────────────────────────────────

    public function test_counts_reflect_inspection_statuses(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();

        $this->makeInspection($supervisor, $winery, ['status' => 'scheduled']);
        $this->makeInspection($supervisor, $winery, ['status' => 'completed']);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertViewHas('counts', fn ($c) => $c['all'] === 2 && $c['scheduled'] === 1 && $c['completed'] === 1);
    }

    // ── filter ────────────────────────────────────────────────────────────────

    public function test_status_filter_narrows_results(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();

        $scheduled = $this->makeInspection($supervisor, $winery, ['status' => 'scheduled']);
        $completed = $this->makeInspection($supervisor, $winery, ['status' => 'completed']);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('statusFilter', 'scheduled')
            ->assertViewHas('inspections', fn ($i) => $i->contains('id', $scheduled->id) && ! $i->contains('id', $completed->id));
    }

    private function makeSupervisor(): User
    {
        return User::factory()->create(['role' => 'supervisor', 'email_verified_at' => now()]);
    }

    private function makeInspection(User $supervisor, User $winery, array $attrs = []): DoInspection
    {
        return DoInspection::create(array_merge([
            'supervisor_id' => $supervisor->id,
            'subject_type' => 'winery',
            'subject_id' => $winery->id,
            'inspection_date' => now()->toDateString(),
            'status' => 'scheduled',
        ], $attrs));
    }
}
