<?php

namespace Tests\Feature\Viticulturist\Harvest;

use App\Livewire\Viticulturist\Harvests\CreateDelivery;
use App\Models\HarvestDelivery;
use App\Models\Plot;
use App\Models\PlotPlanting;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;
use Tests\Traits\CreatesDeliveryScenario;

class CreateDeliveryTest extends ViticulturistTestCase
{
    use CreatesDeliveryScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
        $this->actingAs($this->viticulturist);
    }

    // ── Happy path ────────────────────────────────────────────────────────────

    public function test_can_create_basic_delivery(): void
    {
        Livewire::test(CreateDelivery::class)
            ->set('buyer_name', 'Bodega Rioja')
            ->set('vintage_year', '2024')
            ->set('delivered_kg', '1000')
            ->set('delivery_date', '2024-09-15')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.harvests.index'));

        $this->assertDatabaseHas('harvest_deliveries', [
            'viticulturist_id' => $this->viticulturist->id,
            'buyer_name' => 'Bodega Rioja',
            'status' => 'pending',
        ]);
    }

    public function test_can_create_delivery_with_planting(): void
    {
        Livewire::test(CreateDelivery::class)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('buyer_name', 'Cooperativa')
            ->set('vintage_year', '2024')
            ->set('delivered_kg', '500')
            ->set('delivery_date', '2024-09-10')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('harvest_deliveries', [
            'viticulturist_id' => $this->viticulturist->id,
            'plot_planting_id' => $this->planting->id,
        ]);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    public function test_validates_required_fields(): void
    {
        Livewire::test(CreateDelivery::class)
            ->set('buyer_name', '')
            ->set('vintage_year', '')
            ->set('delivered_kg', '')
            ->set('delivery_date', '')
            ->call('save')
            ->assertHasErrors(['buyer_name', 'vintage_year', 'delivered_kg', 'delivery_date']);
    }

    public function test_planting_must_belong_to_viticulturist(): void
    {
        $otherUser = $this->makeOtherViticulturist();
        $otherPlot = Plot::create([
            'viticulturist_id' => $otherUser->id,
            'name' => 'Parcela Ajena',
            'reference' => 'OTHER-001',
            'area' => 2.0,
            'active' => true,
        ]);
        $otherPlanting = PlotPlanting::create([
            'plot_id' => $otherPlot->id,
            'grape_variety_id' => $this->grapeVariety->id,
            'area_planted' => 2.0,
            'planting_year' => 2015,
            'status' => 'active',
        ]);

        Livewire::test(CreateDelivery::class)
            ->set('plot_planting_id', (string) $otherPlanting->id)
            ->set('buyer_name', 'Alguien')
            ->set('vintage_year', '2024')
            ->set('delivered_kg', '500')
            ->set('delivery_date', '2024-09-15')
            ->call('save')
            ->assertHasErrors(['plot_planting_id']);
    }

    // ── Price calculation ─────────────────────────────────────────────────────

    public function test_total_price_computed_from_price_and_kg(): void
    {
        Livewire::test(CreateDelivery::class)
            ->set('buyer_name', 'Bodega')
            ->set('vintage_year', '2024')
            ->set('delivered_kg', '1000')
            ->set('price_per_kg', '0.250')
            ->set('delivery_date', '2024-09-15')
            ->call('save')
            ->assertHasNoErrors();

        $delivery = HarvestDelivery::where('viticulturist_id', $this->viticulturist->id)->first();
        $this->assertEquals(250.0, (float) $delivery->total_price);
    }

    public function test_manual_total_price_overrides_computed(): void
    {
        Livewire::test(CreateDelivery::class)
            ->set('buyer_name', 'Bodega')
            ->set('vintage_year', '2024')
            ->set('delivered_kg', '1000')
            ->set('price_per_kg', '0.250')
            ->set('total_price', '999.000')
            ->set('delivery_date', '2024-09-15')
            ->call('save')
            ->assertHasNoErrors();

        $delivery = HarvestDelivery::where('viticulturist_id', $this->viticulturist->id)->first();
        $this->assertEquals(999.0, (float) $delivery->total_price);
    }

    // ── Descarte ──────────────────────────────────────────────────────────────

    public function test_disqualified_stores_reason(): void
    {
        Livewire::test(CreateDelivery::class)
            ->set('buyer_name', 'Bodega')
            ->set('vintage_year', '2024')
            ->set('delivered_kg', '500')
            ->set('delivery_date', '2024-09-15')
            ->set('disqualified', true)
            ->set('disqualified_reason', 'Exceso de botrytis')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('harvest_deliveries', [
            'viticulturist_id' => $this->viticulturist->id,
            'disqualified' => 1,
            'disqualified_reason' => 'Exceso de botrytis',
        ]);
    }

    public function test_disqualified_reason_cleared_when_not_disqualified(): void
    {
        Livewire::test(CreateDelivery::class)
            ->set('buyer_name', 'Bodega')
            ->set('vintage_year', '2024')
            ->set('delivered_kg', '500')
            ->set('delivery_date', '2024-09-15')
            ->set('disqualified', false)
            ->set('disqualified_reason', 'Texto residual')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('harvest_deliveries', [
            'viticulturist_id' => $this->viticulturist->id,
            'disqualified' => 0,
            'disqualified_reason' => null,
        ]);
    }

    // ── Auto-link (reverse: viticulturist creates after winery) ──────────────

    public function test_auto_link_matched_when_winery_reception_exists(): void
    {
        $this->actingAs($this->winery);
        $this->makeWineryReception(['total_weight' => 1000]);
        $this->actingAs($this->viticulturist);

        Livewire::test(CreateDelivery::class)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('buyer_name', 'Bodega Test')
            ->set('vintage_year', '2024')
            ->set('delivered_kg', '1000')   // same → 0% discrepancy → matched
            ->set('delivery_date', '2024-09-15')
            ->call('save')
            ->assertHasNoErrors();

        $delivery = HarvestDelivery::where('viticulturist_id', $this->viticulturist->id)->first();
        $this->assertEquals('matched', $delivery->status);
        $this->assertNotNull($delivery->harvest_id);
    }

    public function test_auto_link_disputed_when_large_discrepancy(): void
    {
        $this->actingAs($this->winery);
        $this->makeWineryReception(['total_weight' => 800]);  // winery: 800 kg
        $this->actingAs($this->viticulturist);

        Livewire::test(CreateDelivery::class)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('buyer_name', 'Bodega Test')
            ->set('vintage_year', '2024')
            ->set('delivered_kg', '1000')   // viticulturist: 1000 → 20% discrepancy → disputed
            ->set('delivery_date', '2024-09-15')
            ->call('save')
            ->assertHasNoErrors();

        $delivery = HarvestDelivery::where('viticulturist_id', $this->viticulturist->id)->first();
        $this->assertEquals('disputed', $delivery->status);
        $this->assertNotNull($delivery->harvest_id);
    }

    public function test_auto_link_by_ticket_number(): void
    {
        // Two unlinked receptions: only the one with matching ticket should link
        $this->actingAs($this->winery);
        $this->makeWineryReception(['total_weight' => 1000, 'harvest_ticket_number' => 'TICKET-001']);
        $this->makeWineryReception(['total_weight' => 500,  'harvest_ticket_number' => 'TICKET-002']);
        $this->actingAs($this->viticulturist);

        Livewire::test(CreateDelivery::class)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('buyer_name', 'Bodega Test')
            ->set('vintage_year', '2024')
            ->set('delivered_kg', '1000')
            ->set('ticket_number', 'TICKET-001')
            ->set('delivery_date', '2024-09-15')
            ->call('save')
            ->assertHasNoErrors();

        $delivery = HarvestDelivery::where('viticulturist_id', $this->viticulturist->id)->first();
        $this->assertEquals('matched', $delivery->status);
    }

    public function test_no_auto_link_when_no_winery_reception(): void
    {
        Livewire::test(CreateDelivery::class)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('buyer_name', 'Bodega Test')
            ->set('vintage_year', '2024')
            ->set('delivered_kg', '1000')
            ->set('delivery_date', '2024-09-15')
            ->call('save')
            ->assertHasNoErrors();

        $delivery = HarvestDelivery::where('viticulturist_id', $this->viticulturist->id)->first();
        $this->assertEquals('pending', $delivery->status);
        $this->assertNull($delivery->harvest_id);
    }

    public function test_disqualified_delivery_skips_auto_link(): void
    {
        $this->actingAs($this->winery);
        $this->makeWineryReception(['total_weight' => 1000]);
        $this->actingAs($this->viticulturist);

        Livewire::test(CreateDelivery::class)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('buyer_name', 'Bodega Test')
            ->set('vintage_year', '2024')
            ->set('delivered_kg', '1000')
            ->set('delivery_date', '2024-09-15')
            ->set('disqualified', true)
            ->call('save')
            ->assertHasNoErrors();

        $delivery = HarvestDelivery::where('viticulturist_id', $this->viticulturist->id)->first();
        $this->assertEquals('pending', $delivery->status);
        $this->assertNull($delivery->harvest_id);
    }

    // ── Winery notification ───────────────────────────────────────────────────

    public function test_creating_delivery_notifies_linked_winery(): void
    {
        Notification::fake();

        Livewire::test(CreateDelivery::class)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('buyer_name', 'Bodega Test')
            ->set('vintage_year', '2024')
            ->set('delivered_kg', '1000')
            ->set('delivery_date', '2024-09-15')
            ->call('save')
            ->assertHasNoErrors();

        Notification::assertSentTo(
            $this->winery,
            \App\Notifications\HarvestDeliveryCreatedNotification::class
        );
    }

    public function test_creating_delivery_does_not_notify_unlinked_winery(): void
    {
        Notification::fake();

        $otherWinery = \App\Models\User::factory()->create([
            'role' => 'winery',
            'email_verified_at' => now(),
        ]);

        Livewire::test(CreateDelivery::class)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('buyer_name', 'Otra Bodega')
            ->set('vintage_year', '2024')
            ->set('delivered_kg', '1000')
            ->set('delivery_date', '2024-09-15')
            ->call('save')
            ->assertHasNoErrors();

        Notification::assertNotSentTo(
            $otherWinery,
            \App\Notifications\HarvestDeliveryCreatedNotification::class
        );
    }
}
