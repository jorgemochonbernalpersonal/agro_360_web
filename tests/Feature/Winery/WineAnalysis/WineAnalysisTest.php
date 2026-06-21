<?php

namespace Tests\Feature\Winery\WineAnalysis;

use App\Livewire\Winery\WineAnalysis\Create;
use App\Livewire\Winery\WineAnalysis\Edit;
use App\Livewire\Winery\WineAnalysis\Index;
use App\Models\User;
use App\Models\Wine;
use App\Models\WineAnalysis;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class WineAnalysisTest extends WineryTestCase
{
    private User $winery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    public function test_winery_can_view_index(): void
    {
        $this->get(route('winery.wine-analysis.index'))->assertOk();
    }

    public function test_guest_cannot_access(): void
    {
        $this->app['auth']->guard()->logout();

        $this->get(route('winery.wine-analysis.index'))->assertRedirect();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('analysis_type', '')
            ->set('analysis_date', '')
            ->set('result', '')
            ->call('save')
            ->assertHasErrors(['analysis_type', 'analysis_date', 'result']);
    }

    public function test_winery_can_create_wine_analysis(): void
    {
        $firstType = array_key_first(WineAnalysis::ANALYSIS_TYPES);
        $firstResult = array_key_first(WineAnalysis::RESULTS);

        Livewire::test(Create::class)
            ->set('analysis_type', $firstType)
            ->set('analysis_date', '2026-01-15')
            ->set('result', $firstResult)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_analyses', [
            'user_id' => $this->winery->id,
            'analysis_type' => $firstType,
            'result' => $firstResult,
        ]);
    }

    public function test_winery_can_edit_wine_analysis(): void
    {
        $firstType = array_key_first(WineAnalysis::ANALYSIS_TYPES);
        $firstResult = array_key_first(WineAnalysis::RESULTS);

        $analysis = WineAnalysis::create([
            'user_id' => $this->winery->id,
            'analysis_type' => $firstType,
            'analysis_date' => '2026-01-15',
            'result' => $firstResult,
        ]);

        $secondType = array_key_last(WineAnalysis::ANALYSIS_TYPES);

        Livewire::test(Edit::class, ['analysis' => $analysis])
            ->set('analysis_type', $secondType)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_analyses', [
            'id' => $analysis->id,
            'analysis_type' => $secondType,
        ]);
    }

    public function test_create_rejects_wine_from_other_winery(): void
    {
        $otherWine = Wine::create([
            'user_id' => $this->makeOtherWinery()->id,
            'name' => 'Other Wine',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ]);

        $firstType = array_key_first(WineAnalysis::ANALYSIS_TYPES);
        $firstResult = array_key_first(WineAnalysis::RESULTS);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $otherWine->id)
            ->set('analysis_type', $firstType)
            ->set('analysis_date', '2026-01-15')
            ->set('result', $firstResult)
            ->call('save')
            ->assertHasErrors(['wine_id']);
    }

    public function test_winery_cannot_edit_other_winery_wine_analysis(): void
    {
        $firstType = array_key_first(WineAnalysis::ANALYSIS_TYPES);
        $firstResult = array_key_first(WineAnalysis::RESULTS);

        $analysis = WineAnalysis::create([
            'user_id' => $this->winery->id,
            'analysis_type' => $firstType,
            'analysis_date' => '2026-01-15',
            'result' => $firstResult,
        ]);

        $otherWinery = $this->makeOtherWinery();

        $this->actingAs($otherWinery)
            ->get(route('winery.wine-analysis.edit', $analysis))
            ->assertForbidden();
    }

    public function test_winery_can_delete_wine_analysis(): void
    {
        $firstType = array_key_first(WineAnalysis::ANALYSIS_TYPES);
        $firstResult = array_key_first(WineAnalysis::RESULTS);

        $analysis = WineAnalysis::create([
            'user_id' => $this->winery->id,
            'analysis_type' => $firstType,
            'analysis_date' => '2026-01-15',
            'result' => $firstResult,
        ]);

        Livewire::test(Index::class)
            ->call('delete', $analysis->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('wine_analyses', ['id' => $analysis->id]);
    }
}
