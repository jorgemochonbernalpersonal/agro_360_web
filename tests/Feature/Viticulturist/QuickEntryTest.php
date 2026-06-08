<?php

namespace Tests\Feature\Viticulturist;

use App\Livewire\Viticulturist\QuickEntry;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\Province;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

/**
 * Tests for the Viticulturist Quick-Entry component.
 *
 * Covers: access, step navigation, successful save, validation,
 * campaign assignment, and authorization (cannot save to other user's plot).
 */
class QuickEntryTest extends ViticulturistTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_viticulturist_can_access_quick_entry(): void
    {
        $viticulturist = $this->makeViticulturist();

        $this->actingAs($viticulturist)
            ->get(route('viticulturist.quick-entry'))
            ->assertOk();
    }

    public function test_winery_cannot_access_quick_entry(): void
    {
        $winery = User::factory()->create(['role' => 'winery', 'email_verified_at' => now()]);

        $this->actingAs($winery)
            ->get(route('viticulturist.quick-entry'))
            ->assertForbidden();
    }

    // ── step navigation ───────────────────────────────────────────────────────

    public function test_initial_step_is_one(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(QuickEntry::class)
            ->assertSet('step', 1);
    }

    public function test_selecting_activity_type_advances_to_step_two(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(QuickEntry::class)
            ->call('selectType', 'observation')
            ->assertSet('step', 2)
            ->assertSet('activityType', 'observation');
    }

    public function test_back_from_step_two_returns_to_step_one(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(QuickEntry::class)
            ->call('selectType', 'irrigation')
            ->assertSet('step', 2)
            ->call('back')
            ->assertSet('step', 1);
    }

    public function test_back_on_step_one_stays_on_step_one(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(QuickEntry::class)
            ->call('back')
            ->assertSet('step', 1);
    }

    // ── save ──────────────────────────────────────────────────────────────────

    public function test_can_save_activity_with_valid_data(): void
    {
        $viticulturist = $this->makeViticulturist();
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(QuickEntry::class)
            ->call('selectType', 'observation')
            ->set('plotId', $plot->id)
            ->set('activityDate', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('agricultural_activities', [
            'viticulturist_id' => $viticulturist->id,
            'plot_id' => $plot->id,
            'activity_type' => 'observation',
        ]);
    }

    public function test_save_resets_form_to_step_one(): void
    {
        $viticulturist = $this->makeViticulturist();
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(QuickEntry::class)
            ->call('selectType', 'irrigation')
            ->set('plotId', $plot->id)
            ->set('activityDate', now()->toDateString())
            ->call('save')
            ->assertSet('step', 1)
            ->assertSet('activityType', '')
            ->assertSet('plotId', null);
    }

    public function test_save_with_notes_persists_notes(): void
    {
        $viticulturist = $this->makeViticulturist();
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(QuickEntry::class)
            ->call('selectType', 'pruning')
            ->set('plotId', $plot->id)
            ->set('activityDate', now()->toDateString())
            ->set('notes', 'Poda de formación fila 3')
            ->call('save');

        $this->assertDatabaseHas('agricultural_activities', [
            'viticulturist_id' => $viticulturist->id,
            'notes' => 'Poda de formación fila 3',
        ]);
    }

    public function test_save_assigns_active_campaign(): void
    {
        $viticulturist = $this->makeViticulturist();
        $plot = $this->makePlot($viticulturist);

        // Remove auto-created campaign so the factory one is the only active campaign
        Campaign::where('viticulturist_id', $viticulturist->id)->delete();

        $campaign = Campaign::factory()
            ->forViticulturist($viticulturist)
            ->active()
            ->create();

        $this->actingAs($viticulturist);

        Livewire::test(QuickEntry::class)
            ->call('selectType', 'fertilization')
            ->set('plotId', $plot->id)
            ->set('activityDate', now()->toDateString())
            ->call('save');

        $this->assertDatabaseHas('agricultural_activities', [
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
        ]);
    }

    public function test_save_without_campaign_leaves_campaign_null(): void
    {
        $viticulturist = $this->makeViticulturist();
        $plot = $this->makePlot($viticulturist);

        // Remove any auto-created campaign so the component finds none active
        Campaign::where('viticulturist_id', $viticulturist->id)->delete();

        $this->actingAs($viticulturist);

        Livewire::test(QuickEntry::class)
            ->call('selectType', 'observation')
            ->set('plotId', $plot->id)
            ->set('activityDate', now()->toDateString())
            ->call('save');

        $this->assertDatabaseHas('agricultural_activities', [
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => null,
        ]);
    }

    // ── validation ────────────────────────────────────────────────────────────

    public function test_save_requires_plot(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(QuickEntry::class)
            ->call('selectType', 'observation')
            ->set('activityDate', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['plotId']);

        $this->assertSame(0, AgriculturalActivity::count());
    }

    public function test_save_requires_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $plot = $this->makePlot($viticulturist);
        $this->actingAs($viticulturist);

        Livewire::test(QuickEntry::class)
            ->call('selectType', 'observation')
            ->set('plotId', $plot->id)
            ->set('activityDate', '')
            ->call('save')
            ->assertHasErrors(['activityDate']);

        $this->assertSame(0, AgriculturalActivity::count());
    }

    public function test_save_rejects_invalid_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $plot = $this->makePlot($viticulturist);
        $this->actingAs($viticulturist);

        Livewire::test(QuickEntry::class)
            ->call('selectType', 'observation')
            ->set('plotId', $plot->id)
            ->set('activityDate', 'not-a-date')
            ->call('save')
            ->assertHasErrors(['activityDate']);
    }

    // ── authorization ─────────────────────────────────────────────────────────

    public function test_cannot_save_activity_for_another_users_plot(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $otherPlot = $this->makePlot($other);

        $this->actingAs($viticulturist);

        Livewire::test(QuickEntry::class)
            ->call('selectType', 'observation')
            ->set('plotId', $otherPlot->id)
            ->set('activityDate', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['plotId']);

        $this->assertSame(0, AgriculturalActivity::count());
    }

    // ── render ────────────────────────────────────────────────────────────────

    public function test_render_passes_only_active_plots(): void
    {
        $viticulturist = $this->makeViticulturist();
        $activePlot = $this->makePlot($viticulturist);
        $inactivePlot = $this->makePlot($viticulturist);
        $inactivePlot->update(['active' => false]);

        $this->actingAs($viticulturist);

        Livewire::test(QuickEntry::class)
            ->assertViewHas('plots', fn ($plots) => $plots->contains('id', $activePlot->id) &&
                ! $plots->contains('id', $inactivePlot->id)
            );
    }

    public function test_render_passes_all_eight_activity_types(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(QuickEntry::class)
            ->assertViewHas('activityTypes', fn ($types) => count($types) === 8);
    }

    // ── geographic helper ─────────────────────────────────────────────────────

    /**
     * Creates a Plot with valid geographic FK references.
     * PlotFactory defaults to id=1 for geographic FKs which may not exist
     * in a fresh test database — this helper ensures the records exist first.
     */
    private function makePlot(User $viticulturist): Plot
    {
        $community = AutonomousCommunity::firstOrCreate(
            ['code' => 'TEST'],
            ['name' => 'Test Community']
        );
        $province = Province::firstOrCreate(
            ['code' => 'T0', 'autonomous_community_id' => $community->id],
            ['name' => 'Test Province']
        );
        $municipality = Municipality::firstOrCreate(
            ['code' => '00000', 'province_id' => $province->id],
            ['name' => 'Test Municipality']
        );

        return Plot::factory()->forViticulturist($viticulturist)->create([
            'autonomous_community_id' => $community->id,
            'province_id' => $province->id,
            'municipality_id' => $municipality->id,
        ]);
    }
}
