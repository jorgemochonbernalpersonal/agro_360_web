<?php

namespace Tests\Feature\Winery\Denomination;

use App\Livewire\Winery\Denomination\Qualifications\Index;
use App\Models\DoQualification;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class QualificationsTest extends WineryTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_winery_can_access_denomination_qualifications(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('winery.denomination.qualifications.index'))
            ->assertOk();
    }

    public function test_supervisor_cannot_access_denomination_qualifications(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('winery.denomination.qualifications.index'))
            ->assertForbidden();
    }

    // ── scoping ───────────────────────────────────────────────────────────────

    public function test_winery_sees_only_own_qualifications(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();
        $other = $this->makeOtherWinery();

        $own = $this->makeQualification($supervisor, $winery);
        $theirs = $this->makeQualification($supervisor, $other);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertViewHas('qualifications', fn ($q) => $q->contains('id', $own->id) && ! $q->contains('id', $theirs->id));
    }

    // ── counts ────────────────────────────────────────────────────────────────

    public function test_counts_reflect_qualification_results(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();

        $this->makeQualification($supervisor, $winery, ['result' => 'qualified']);
        $this->makeQualification($supervisor, $winery, ['result' => 'pending']);
        $this->makeQualification($supervisor, $winery, ['result' => 'disqualified']);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertViewHas('counts', fn ($c) => $c['all'] === 3 &&
                $c['qualified'] === 1 &&
                $c['pending'] === 1 &&
                $c['disqualified'] === 1
            );
    }

    // ── filters ───────────────────────────────────────────────────────────────

    public function test_result_filter_narrows_results(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();

        $qualified = $this->makeQualification($supervisor, $winery, ['result' => 'qualified']);
        $disqualified = $this->makeQualification($supervisor, $winery, ['result' => 'disqualified']);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('resultFilter', 'qualified')
            ->assertViewHas('qualifications', fn ($q) => $q->contains('id', $qualified->id) && ! $q->contains('id', $disqualified->id));
    }

    public function test_vintage_filter_narrows_results(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();

        $v2024 = $this->makeQualification($supervisor, $winery, ['vintage' => 2024]);
        $v2025 = $this->makeQualification($supervisor, $winery, ['vintage' => 2025]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('vintageFilter', '2024')
            ->assertViewHas('qualifications', fn ($q) => $q->contains('id', $v2024->id) && ! $q->contains('id', $v2025->id));
    }

    private function makeSupervisor(): User
    {
        return User::factory()->create(['role' => 'supervisor', 'email_verified_at' => now()]);
    }

    private function makeQualification(User $supervisor, User $winery, array $attrs = []): DoQualification
    {
        return DoQualification::create(array_merge([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'vintage' => 2025,
            'wine_name' => 'Vino Test',
            'qualification_date' => now()->toDateString(),
        ], $attrs));
    }
}
