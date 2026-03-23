<?php

namespace Tests\Feature\Winery\Harvest;

use App\Livewire\Winery\Harvest\Forecasts\Create;
use App\Livewire\Winery\Harvest\Forecasts\Edit;
use App\Livewire\Winery\Harvest\Forecasts\Index;
use App\Models\Campaign;
use App\Models\GrapeVariety;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Models\WineryYieldForecast;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

/**
 * Tests for the Winery Harvest Forecasts module.
 *
 * Covers: Create (happy path, validation, duplicate guard),
 *         Edit (happy path, authorization),
 *         Index actions (confirm, delete).
 */
class ForecastsTest extends WineryTestCase
{
    private User        $winery;
    private User        $viticulturist;
    private Plot        $plot;
    private PlotPlanting $planting;
    private Campaign    $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        $this->winery = $this->makeWinery();

        $this->viticulturist = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        WineryViticulturist::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->winery->id,
        ]);

        $grapeVariety = GrapeVariety::firstOrCreate(
            ['code' => 'TEMP'],
            ['name' => 'Tempranillo', 'color' => 'red']
        );

        $this->plot = Plot::create([
            'viticulturist_id' => $this->viticulturist->id,
            'name'             => 'Parcela Test',
            'reference'        => 'FT-001',
            'area'             => 1.5,
            'active'           => true,
        ]);

        $this->planting = PlotPlanting::create([
            'plot_id'          => $this->plot->id,
            'grape_variety_id' => $grapeVariety->id,
            'area_planted'     => 1.5,
            'planting_year'    => now()->year - 8,
            'status'           => 'active',
        ]);

        $this->campaign = Campaign::create([
            'viticulturist_id' => $this->winery->id,
            'name'             => 'Vendimia Test',
            'year'             => now()->year,
            'start_date'       => now()->setMonth(8)->startOfMonth()->format('Y-m-d'),
            'end_date'         => now()->setMonth(11)->endOfMonth()->format('Y-m-d'),
            'active'           => true,
        ]);

        $this->actingAs($this->winery);
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function makeForecast(array $attrs = []): WineryYieldForecast
    {
        return WineryYieldForecast::create(array_merge([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'plot_planting_id' => $this->planting->id,
            'campaign_id'      => $this->campaign->id,
            'vintage_year'     => $this->campaign->year,
            'estimated_kg'     => 500,
            'estimation_date'  => now()->toDateString(),
            'status'           => 'draft',
        ], $attrs));
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_index_renders(): void
    {
        $this->get(route('winery.harvest-forecasts.index'))
            ->assertOk();
    }

    // ── Create — happy path ───────────────────────────────────────────────────

    public function test_can_create_forecast(): void
    {
        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('campaign_id', (string) $this->campaign->id)
            ->set('vintage_year', (string) $this->campaign->year)
            ->set('estimated_kg', '800')
            ->set('estimation_date', now()->toDateString())
            ->set('status', 'draft')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('winery_yield_forecasts', [
            'winery_id'        => $this->winery->id,
            'plot_planting_id' => $this->planting->id,
            'estimated_kg'     => 800,
            'status'           => 'draft',
        ]);
    }

    // ── Create — validation ───────────────────────────────────────────────────

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('viticulturist_id', '')
            ->set('plot_planting_id', '')
            ->set('campaign_id', '')
            ->set('estimated_kg', '')
            ->set('estimation_date', '')
            ->call('save')
            ->assertHasErrors(['viticulturist_id', 'plot_planting_id', 'campaign_id', 'estimated_kg', 'estimation_date']);
    }

    public function test_create_rejects_zero_kg(): void
    {
        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('campaign_id', (string) $this->campaign->id)
            ->set('vintage_year', (string) $this->campaign->year)
            ->set('estimated_kg', '0')
            ->set('estimation_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['estimated_kg']);
    }

    // ── Create — duplicate guard ──────────────────────────────────────────────

    public function test_create_rejects_duplicate_planting_and_campaign(): void
    {
        $this->makeForecast(); // already exists

        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('campaign_id', (string) $this->campaign->id)
            ->set('vintage_year', (string) $this->campaign->year)
            ->set('estimated_kg', '500')
            ->set('estimation_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['plot_planting_id']);
    }

    // ── Edit — happy path ─────────────────────────────────────────────────────

    public function test_can_edit_forecast(): void
    {
        $forecast = $this->makeForecast(['estimated_kg' => 400]);

        Livewire::test(Edit::class, ['forecast' => $forecast])
            ->set('estimated_kg', '750')
            ->set('status', 'confirmed')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('winery_yield_forecasts', [
            'id'           => $forecast->id,
            'estimated_kg' => 750,
            'status'       => 'confirmed',
        ]);
    }

    // ── Edit — authorization ──────────────────────────────────────────────────

    public function test_other_winery_cannot_edit_forecast(): void
    {
        $forecast = $this->makeForecast();

        $this->actingAs($this->makeOtherWinery())
            ->get(route('winery.harvest-forecasts.edit', $forecast))
            ->assertForbidden();
    }

    // ── Index — confirm action ────────────────────────────────────────────────

    public function test_can_confirm_forecast(): void
    {
        $forecast = $this->makeForecast(['status' => 'draft']);

        Livewire::test(Index::class)
            ->call('confirm', $forecast->id);

        $this->assertDatabaseHas('winery_yield_forecasts', [
            'id'     => $forecast->id,
            'status' => 'confirmed',
        ]);
    }

    // ── Index — delete action ─────────────────────────────────────────────────

    public function test_can_delete_forecast(): void
    {
        $forecast = $this->makeForecast();

        Livewire::test(Index::class)
            ->call('delete', $forecast->id);

        $this->assertDatabaseMissing('winery_yield_forecasts', ['id' => $forecast->id]);
    }

    public function test_cannot_delete_another_winerys_forecast(): void
    {
        $forecast = $this->makeForecast();

        $this->actingAs($this->makeOtherWinery());

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('delete', $forecast->id);
    }
}
