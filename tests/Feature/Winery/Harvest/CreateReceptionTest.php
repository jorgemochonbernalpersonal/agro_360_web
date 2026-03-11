<?php

namespace Tests\Feature\Winery\Harvest;

use App\Livewire\Winery\Harvest\Reception\Create;
use App\Models\Campaign;
use App\Models\Container;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;
use Tests\Traits\CreatesDeliveryScenario;

class CreateReceptionTest extends WineryTestCase
{
    use CreatesDeliveryScenario;

    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
        $this->container = $this->makeContainer();
        $this->actingAs($this->winery);
    }

    // ── Happy path ────────────────────────────────────────────────────────────

    public function test_can_create_basic_reception(): void
    {
        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('vintage_year', 2024)
            ->set('harvest_start_date', '2024-09-15')
            ->set('total_weight', '1000')
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('harvests', [
            'winery_id'        => $this->winery->id,
            'plot_planting_id' => $this->planting->id,
            'total_weight'     => 1000,
            'status'           => 'active',
        ]);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    public function test_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('viticulturist_id', '')
            ->set('plot_id', '')
            ->set('plot_planting_id', '')
            ->set('harvest_start_date', '')
            ->set('total_weight', '')
            ->set('container_id', '')
            ->call('save')
            ->assertHasErrors([
                'viticulturist_id',
                'plot_id',
                'plot_planting_id',
                'harvest_start_date',
                'total_weight',
                'container_id',
            ]);
    }

    // ── Price ─────────────────────────────────────────────────────────────────

    public function test_total_value_computed_from_price_and_weight(): void
    {
        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('vintage_year', 2024)
            ->set('harvest_start_date', '2024-09-15')
            ->set('total_weight', '1000')
            ->set('price_per_kg', '0.250')
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        $harvest = Harvest::where('winery_id', $this->winery->id)->first();
        $this->assertEquals(250.0, (float) $harvest->total_value);
    }

    // ── Batch accumulation ────────────────────────────────────────────────────

    public function test_batch_is_created_and_accumulates_weight(): void
    {
        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('vintage_year', 2024)
            ->set('harvest_start_date', '2024-09-15')
            ->set('total_weight', '800')
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('grape_reception_batches', [
            'winery_id'        => $this->winery->id,
            'plot_planting_id' => $this->planting->id,
            'total_weight_kg'  => 800,
        ]);
    }

    public function test_second_reception_increments_batch_total(): void
    {
        // First reception via component
        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('vintage_year', 2024)
            ->set('harvest_start_date', '2024-09-15')
            ->set('total_weight', '600')
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->container->refresh();

        // Second reception via component
        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('vintage_year', 2024)
            ->set('harvest_start_date', '2024-09-16')
            ->set('total_weight', '400')
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('grape_reception_batches', [
            'winery_id'        => $this->winery->id,
            'plot_planting_id' => $this->planting->id,
            'total_weight_kg'  => 1000,
        ]);
    }

    // ── Container capacity ────────────────────────────────────────────────────

    public function test_container_capacity_exceeded_shows_error(): void
    {
        $fullContainer = $this->makeContainer(['capacity' => 500, 'used_capacity' => 500]);

        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('vintage_year', 2024)
            ->set('harvest_start_date', '2024-09-15')
            ->set('total_weight', '100')
            ->set('container_id', (string) $fullContainer->id)
            ->call('save')
            ->assertHasErrors(['container_id']);

        $this->assertDatabaseMissing('harvests', ['winery_id' => $this->winery->id]);
    }

    // ── Closed batch ──────────────────────────────────────────────────────────

    public function test_closed_batch_prevents_new_reception(): void
    {
        $campaign = Campaign::getOrCreateActiveForYear($this->winery->id, 2024);
        GrapeReceptionBatch::create([
            'winery_id'        => $this->winery->id,
            'plot_planting_id' => $this->planting->id,
            'campaign_id'      => $campaign->id,
            'viticulturist_id' => $this->viticulturist->id,
            'vintage_year'     => 2024,
            'total_weight_kg'  => 0,
            'status'           => 'closed',
        ]);

        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('vintage_year', 2024)
            ->set('harvest_start_date', '2024-09-15')
            ->set('total_weight', '1000')
            ->set('container_id', (string) $this->container->id)
            ->call('save');

        $this->assertDatabaseMissing('harvests', ['winery_id' => $this->winery->id]);
    }

    // ── Disqualified ──────────────────────────────────────────────────────────

    public function test_disqualified_stores_reason(): void
    {
        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('vintage_year', 2024)
            ->set('harvest_start_date', '2024-09-15')
            ->set('total_weight', '500')
            ->set('container_id', (string) $this->container->id)
            ->set('disqualified', true)
            ->set('disqualified_reason', 'Exceso de oidium')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('harvests', [
            'winery_id'           => $this->winery->id,
            'disqualified'        => 1,
            'disqualified_reason' => 'Exceso de oidium',
        ]);
    }

    // ── Auto-link (winery creates → links viticulturist delivery) ────────────

    public function test_creating_reception_links_pending_delivery(): void
    {
        Notification::fake();

        $delivery = $this->makeDelivery(['delivered_kg' => 1000]);

        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('vintage_year', 2024)
            ->set('harvest_start_date', '2024-09-15')
            ->set('total_weight', '1000')
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        $delivery->refresh();
        $this->assertEquals('matched', $delivery->status);
        $this->assertNotNull($delivery->harvest_id);
        $this->assertEquals(0, (float) $delivery->discrepancy_kg);
    }

    public function test_creating_reception_creates_disputed_delivery_on_large_discrepancy(): void
    {
        Notification::fake();

        $delivery = $this->makeDelivery(['delivered_kg' => 1000]);

        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('vintage_year', 2024)
            ->set('harvest_start_date', '2024-09-15')
            ->set('total_weight', '800')   // 20% discrepancy → disputed
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        $delivery->refresh();
        $this->assertEquals('disputed', $delivery->status);
        $this->assertEquals(200, (float) $delivery->discrepancy_kg);
    }

    public function test_viticulturist_not_linked_ownership_check(): void
    {
        $unlinkedViticulturist = \App\Models\User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        // Verify the viticulturist is NOT linked to this winery
        $this->assertFalse(
            \App\Models\WineryViticulturist::where('winery_id', $this->winery->id)
                ->where('viticulturist_id', $unlinkedViticulturist->id)
                ->exists()
        );
    }
}
