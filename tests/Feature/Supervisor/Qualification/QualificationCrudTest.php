<?php

namespace Tests\Feature\Supervisor\Qualification;

use App\Livewire\Supervisor\Qualification\Index;
use App\Models\DoQualification;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class QualificationCrudTest extends SupervisorTestCase
{
    // ── saveQualification ─────────────────────────────────────────────────────

    public function test_supervisor_can_create_qualification(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('winery_id', $winery->id)
            ->set('vintage', now()->year)
            ->set('wine_name', 'Ribera Selección')
            ->set('qualification_date', now()->format('Y-m-d'))
            ->call('saveQualification')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('do_qualifications', [
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'wine_name' => 'Ribera Selección',
            'result' => DoQualification::RESULT_PENDING,
        ]);
    }

    public function test_save_qualification_requires_wine_name(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('winery_id', $winery->id)
            ->set('vintage', now()->year)
            ->set('wine_name', '')
            ->set('qualification_date', now()->format('Y-m-d'))
            ->call('saveQualification')
            ->assertHasErrors(['wine_name']);
    }

    public function test_save_qualification_requires_valid_vintage(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('winery_id', $winery->id)
            ->set('vintage', 1950)
            ->set('wine_name', 'Test')
            ->set('qualification_date', now()->format('Y-m-d'))
            ->call('saveQualification')
            ->assertHasErrors(['vintage']);
    }

    public function test_save_qualification_rejects_unlinked_winery(): void
    {
        $supervisor = $this->makeSupervisor();
        $otherWinery = $this->makeWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('winery_id', $otherWinery->id)
            ->set('vintage', now()->year)
            ->set('wine_name', 'Vino Ajeno')
            ->set('qualification_date', now()->format('Y-m-d'))
            ->call('saveQualification')
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('do_qualifications', [
            'supervisor_id' => $supervisor->id,
            'wine_name' => 'Vino Ajeno',
        ]);
    }

    public function test_save_qualification_closes_create_form(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('toggleCreate')
            ->set('winery_id', $winery->id)
            ->set('vintage', now()->year)
            ->set('wine_name', 'Vino Test')
            ->set('qualification_date', now()->format('Y-m-d'))
            ->call('saveQualification')
            ->assertSet('showCreate', false);
    }

    public function test_save_qualification_resets_form_fields_on_success(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('winery_id', $winery->id)
            ->set('vintage', now()->year)
            ->set('wine_name', 'Borrarse')
            ->set('color', 'tinto')
            ->set('qualification_date', now()->format('Y-m-d'))
            ->call('saveQualification')
            ->assertSet('wine_name', '')
            ->assertSet('color', '');
    }

    // ── qualify ───────────────────────────────────────────────────────────────

    public function test_supervisor_can_qualify_a_wine(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $q = $this->makeQualification($supervisor->id, $winery->id);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('qualify', $q->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('do_qualifications', [
            'id' => $q->id,
            'result' => DoQualification::RESULT_QUALIFIED,
            'qualified_by' => $supervisor->id,
        ]);
    }

    public function test_another_supervisor_cannot_qualify(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $other = $this->makeSupervisor();
        $q = $this->makeQualification($supervisor->id, $winery->id);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($other)
            ->test(Index::class)
            ->call('qualify', $q->id);
    }

    // ── disqualify ────────────────────────────────────────────────────────────

    public function test_supervisor_can_disqualify_a_wine(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $q = $this->makeQualification($supervisor->id, $winery->id);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('disqualify', $q->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('do_qualifications', [
            'id' => $q->id,
            'result' => DoQualification::RESULT_DISQUALIFIED,
            'qualified_by' => $supervisor->id,
        ]);
    }

    public function test_another_supervisor_cannot_disqualify(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $other = $this->makeSupervisor();
        $q = $this->makeQualification($supervisor->id, $winery->id);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($other)
            ->test(Index::class)
            ->call('disqualify', $q->id);
    }

    public function test_qualify_and_disqualify_are_reversible(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $q = $this->makeQualification($supervisor->id, $winery->id);

        $component = Livewire::actingAs($supervisor)->test(Index::class);

        $component->call('qualify', $q->id);
        $this->assertEquals(DoQualification::RESULT_QUALIFIED, $q->fresh()->result);

        $component->call('disqualify', $q->id);
        $this->assertEquals(DoQualification::RESULT_DISQUALIFIED, $q->fresh()->result);
    }

    // ── tab counts ────────────────────────────────────────────────────────────

    public function test_counts_reflect_qualification_results(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeQualification($supervisor->id, $winery->id, ['result' => DoQualification::RESULT_QUALIFIED]);
        $this->makeQualification($supervisor->id, $winery->id, ['result' => DoQualification::RESULT_DISQUALIFIED]);
        $this->makeQualification($supervisor->id, $winery->id, ['result' => DoQualification::RESULT_PENDING]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('tabs', fn ($tabs) => $tabs['qualified']['count'] === 1 &&
                $tabs['disqualified']['count'] === 1 &&
                $tabs['pending']['count'] === 1 &&
                $tabs['all']['count'] === 3
            );
    }
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeQualification(int $supervisorId, int $wineryId, array $attrs = []): DoQualification
    {
        return DoQualification::create(array_merge([
            'supervisor_id' => $supervisorId,
            'winery_id' => $wineryId,
            'vintage' => now()->year,
            'wine_name' => 'Vino Test',
            'qualification_date' => now()->format('Y-m-d'),
            'result' => DoQualification::RESULT_PENDING,
        ], $attrs));
    }
}
