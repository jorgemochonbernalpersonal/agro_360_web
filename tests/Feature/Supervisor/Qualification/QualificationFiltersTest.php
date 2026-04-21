<?php

namespace Tests\Feature\Supervisor\Qualification;

use App\Livewire\Supervisor\Qualification\Index;
use App\Models\DoQualification;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class QualificationFiltersTest extends SupervisorTestCase
{
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeQualification(int $supervisorId, int $wineryId, array $attrs = []): DoQualification
    {
        return DoQualification::create(array_merge([
            'supervisor_id'      => $supervisorId,
            'winery_id'          => $wineryId,
            'vintage'            => now()->year,
            'wine_name'          => 'Vino Test',
            'qualification_date' => now()->format('Y-m-d'),
            'result'             => DoQualification::RESULT_PENDING,
        ], $attrs));
    }

    // ── tabs ──────────────────────────────────────────────────────────────────

    public function test_switch_tab_changes_current_tab(): void
    {
        [$supervisor] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertSet('currentTab', 'all')
            ->call('switchTab', 'pending')
            ->assertSet('currentTab', 'pending');
    }

    public function test_tab_all_shows_every_result(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeQualification($supervisor->id, $winery->id, ['result' => DoQualification::RESULT_PENDING,      'wine_name' => 'Vino A']);
        $this->makeQualification($supervisor->id, $winery->id, ['result' => DoQualification::RESULT_QUALIFIED,    'wine_name' => 'Vino B']);
        $this->makeQualification($supervisor->id, $winery->id, ['result' => DoQualification::RESULT_DISQUALIFIED, 'wine_name' => 'Vino C']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('switchTab', 'all')
            ->assertViewHas('qualifications', fn ($q) => $q->total() === 3);
    }

    public function test_tab_pending_shows_only_pending(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeQualification($supervisor->id, $winery->id, ['result' => DoQualification::RESULT_PENDING,   'wine_name' => 'Pendiente']);
        $this->makeQualification($supervisor->id, $winery->id, ['result' => DoQualification::RESULT_QUALIFIED, 'wine_name' => 'Calificado']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('switchTab', 'pending')
            ->assertViewHas('qualifications', fn ($q) =>
                $q->total() === 1 &&
                collect($q->items())->first()->wine_name === 'Pendiente'
            );
    }

    public function test_tab_qualified_shows_only_qualified(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeQualification($supervisor->id, $winery->id, ['result' => DoQualification::RESULT_QUALIFIED,    'wine_name' => 'Calificado']);
        $this->makeQualification($supervisor->id, $winery->id, ['result' => DoQualification::RESULT_DISQUALIFIED, 'wine_name' => 'Descalificado']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('switchTab', 'qualified')
            ->assertViewHas('qualifications', fn ($q) =>
                $q->total() === 1 &&
                collect($q->items())->first()->wine_name === 'Calificado'
            );
    }

    public function test_tab_disqualified_shows_only_disqualified(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeQualification($supervisor->id, $winery->id, ['result' => DoQualification::RESULT_PENDING,      'wine_name' => 'Pendiente']);
        $this->makeQualification($supervisor->id, $winery->id, ['result' => DoQualification::RESULT_DISQUALIFIED, 'wine_name' => 'Descalificado']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('switchTab', 'disqualified')
            ->assertViewHas('qualifications', fn ($q) =>
                $q->total() === 1 &&
                collect($q->items())->first()->wine_name === 'Descalificado'
            );
    }

    // ── tab counts ────────────────────────────────────────────────────────────

    public function test_tabs_counts_reflect_actual_results(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeQualification($supervisor->id, $winery->id, ['result' => DoQualification::RESULT_PENDING]);
        $this->makeQualification($supervisor->id, $winery->id, ['result' => DoQualification::RESULT_PENDING]);
        $this->makeQualification($supervisor->id, $winery->id, ['result' => DoQualification::RESULT_QUALIFIED]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('tabs', fn ($tabs) =>
                $tabs['all']['count']          === 3 &&
                $tabs['pending']['count']      === 2 &&
                $tabs['qualified']['count']    === 1 &&
                $tabs['disqualified']['count'] === 0
            );
    }

    public function test_tabs_counts_isolated_from_other_supervisors(): void
    {
        [$supervisor]                    = $this->makeSupervisorWithWinery();
        [$otherSupervisor, $otherWinery] = $this->makeSupervisorWithWinery();

        $this->makeQualification($otherSupervisor->id, $otherWinery->id, ['result' => DoQualification::RESULT_PENDING]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('tabs', fn ($tabs) => $tabs['all']['count'] === 0);
    }

    // ── vintageFilter ─────────────────────────────────────────────────────────

    public function test_vintage_filter_narrows_results(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeQualification($supervisor->id, $winery->id, ['vintage' => 2022, 'wine_name' => 'Añada 2022']);
        $this->makeQualification($supervisor->id, $winery->id, ['vintage' => 2023, 'wine_name' => 'Añada 2023']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('vintageFilter', '2022')
            ->assertViewHas('qualifications', fn ($q) =>
                $q->total() === 1 &&
                collect($q->items())->first()->wine_name === 'Añada 2022'
            );
    }

    public function test_clearing_vintage_filter_shows_all(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeQualification($supervisor->id, $winery->id, ['vintage' => 2022]);
        $this->makeQualification($supervisor->id, $winery->id, ['vintage' => 2023]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('vintageFilter', '2022')
            ->assertViewHas('qualifications', fn ($q) => $q->total() === 1)
            ->set('vintageFilter', '')
            ->assertViewHas('qualifications', fn ($q) => $q->total() === 2);
    }

    // ── colorFilter ───────────────────────────────────────────────────────────

    public function test_color_filter_narrows_results(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeQualification($supervisor->id, $winery->id, ['color' => 'tinto',  'wine_name' => 'Tinto']);
        $this->makeQualification($supervisor->id, $winery->id, ['color' => 'blanco', 'wine_name' => 'Blanco']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('colorFilter', 'tinto')
            ->assertViewHas('qualifications', fn ($q) =>
                $q->total() === 1 &&
                collect($q->items())->first()->wine_name === 'Tinto'
            );
    }

    // ── availableVintages ─────────────────────────────────────────────────────

    public function test_available_vintages_lists_distinct_years(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->makeQualification($supervisor->id, $winery->id, ['vintage' => 2021]);
        $this->makeQualification($supervisor->id, $winery->id, ['vintage' => 2021]);
        $this->makeQualification($supervisor->id, $winery->id, ['vintage' => 2023]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('availableVintages', fn ($v) =>
                $v->count() === 2 &&
                $v->contains(2021) &&
                $v->contains(2023)
            );
    }

    // ── openEdit / closeEdit ──────────────────────────────────────────────────

    public function test_open_edit_populates_edit_fields(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $q = $this->makeQualification($supervisor->id, $winery->id, [
            'wine_name'          => 'Ribera Especial',
            'vintage'            => 2022,
            'color'              => 'tinto',
            'alcohol_percentage' => 13.5,
            'qualification_date' => '2022-06-15',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openEdit', $q->id)
            ->assertSet('showEdit', true)
            ->assertSet('editId', $q->id)
            ->assertSet('editWineName', 'Ribera Especial')
            ->assertSet('editVintage', '2022')
            ->assertSet('editColor', 'tinto')
            ->assertSet('editAlcohol', '13.50');
    }

    public function test_close_edit_hides_modal(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $q = $this->makeQualification($supervisor->id, $winery->id);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openEdit', $q->id)
            ->assertSet('showEdit', true)
            ->call('closeEdit')
            ->assertSet('showEdit', false)
            ->assertSet('editId', null);
    }

    public function test_open_edit_blocked_for_other_supervisor_qualification(): void
    {
        $supervisor              = $this->makeSupervisor();
        [$otherSup, $otherWinery] = $this->makeSupervisorWithWinery();

        $q = $this->makeQualification($otherSup->id, $otherWinery->id);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openEdit', $q->id);
    }

    // ── updateQualification ───────────────────────────────────────────────────

    public function test_update_qualification_persists_changes(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $q = $this->makeQualification($supervisor->id, $winery->id, [
            'wine_name' => 'Original',
            'vintage'   => 2022,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openEdit', $q->id)
            ->set('editWineName', 'Actualizado')
            ->set('editVintage', '2023')
            ->set('editQualificationDate', '2023-05-10')
            ->call('updateQualification')
            ->assertHasNoErrors()
            ->assertSet('showEdit', false)
            ->assertDispatched('toast');

        $fresh = $q->fresh();
        $this->assertEquals('Actualizado', $fresh->wine_name);
        $this->assertEquals(2023, $fresh->vintage);
    }

    public function test_update_qualification_validates_scores_range(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $q = $this->makeQualification($supervisor->id, $winery->id);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openEdit', $q->id)
            ->set('editWineName', 'Vino Test')
            ->set('editVintage', '2022')
            ->set('editQualificationDate', '2022-06-01')
            ->set('editVisualScore', '15')  // max is 10
            ->call('updateQualification')
            ->assertHasErrors(['editVisualScore']);
    }

    public function test_update_qualification_validates_ph_range(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $q = $this->makeQualification($supervisor->id, $winery->id);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openEdit', $q->id)
            ->set('editWineName', 'Vino Test')
            ->set('editVintage', '2022')
            ->set('editQualificationDate', '2022-06-01')
            ->set('editPh', '1.5')  // min is 2
            ->call('updateQualification')
            ->assertHasErrors(['editPh']);
    }

    // ── qualify / disqualify ──────────────────────────────────────────────────

    public function test_qualify_sets_result_to_qualified(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $q = $this->makeQualification($supervisor->id, $winery->id, [
            'result' => DoQualification::RESULT_PENDING,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('qualify', $q->id)
            ->assertDispatched('toast');

        $this->assertEquals(DoQualification::RESULT_QUALIFIED, $q->fresh()->result);
    }

    public function test_disqualify_sets_result_to_disqualified(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $q = $this->makeQualification($supervisor->id, $winery->id, [
            'result' => DoQualification::RESULT_PENDING,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('disqualify', $q->id)
            ->assertDispatched('toast');

        $this->assertEquals(DoQualification::RESULT_DISQUALIFIED, $q->fresh()->result);
    }

    public function test_qualify_blocked_for_other_supervisor(): void
    {
        $supervisor              = $this->makeSupervisor();
        [$otherSup, $otherWinery] = $this->makeSupervisorWithWinery();

        $q = $this->makeQualification($otherSup->id, $otherWinery->id);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('qualify', $q->id);
    }

    // ── toggleCreate ─────────────────────────────────────────────────────────

    public function test_toggle_create_shows_and_hides_form(): void
    {
        [$supervisor] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertSet('showCreate', false)
            ->call('toggleCreate')
            ->assertSet('showCreate', true)
            ->call('toggleCreate')
            ->assertSet('showCreate', false);
    }

    // ── wineries list ─────────────────────────────────────────────────────────

    public function test_wineries_view_data_contains_only_own_wineries(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $otherWinery           = $this->makeWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('wineries', fn ($w) =>
                $w->pluck('id')->contains($winery->id) &&
                $w->pluck('id')->doesntContain($otherWinery->id)
            );
    }
}
