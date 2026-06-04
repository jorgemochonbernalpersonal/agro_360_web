<?php

namespace Tests\Feature\Viticulturist\Harvest;

use App\Models\Harvest;
use App\Models\HarvestDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\CreatesDeliveryScenario;

/**
 * Tests for the HarvestObserver auto-link logic (winery → viticulturist direction).
 *
 * When the winery creates a reception (Harvest with winery_id), the observer
 * tries to find and link a matching pending HarvestDelivery.
 * When the winery updates the weight, it re-syncs the discrepancy and status.
 * When the winery cancels or deletes a reception, it resets the linked delivery.
 */
class AutoLinkTest extends TestCase
{
    use CreatesDeliveryScenario;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
    }

    // ── Creating winery reception links delivery ──────────────────────────────

    public function test_reception_links_to_pending_delivery_by_ticket(): void
    {
        $delivery = $this->makeDelivery(['ticket_number' => 'TICKET-001', 'delivered_kg' => 1000]);

        $this->actingAs($this->winery);
        $this->makeWineryReception([
            'total_weight' => 1000,
            'harvest_ticket_number' => 'TICKET-001',
        ]);

        $delivery->refresh();
        $this->assertEquals('matched', $delivery->status);
        $this->assertNotNull($delivery->harvest_id);
        $this->assertEquals(0, (float) $delivery->discrepancy_kg);
    }

    public function test_reception_links_to_single_pending_delivery_as_fallback(): void
    {
        $delivery = $this->makeDelivery(['delivered_kg' => 1000]);

        $this->actingAs($this->winery);
        $this->makeWineryReception(['total_weight' => 1000]);

        $delivery->refresh();
        $this->assertEquals('matched', $delivery->status);
        $this->assertNotNull($delivery->harvest_id);
    }

    public function test_reception_does_not_link_when_multiple_pending_no_ticket(): void
    {
        $delivery1 = $this->makeDelivery(['delivered_kg' => 1000]);
        $delivery2 = $this->makeDelivery(['delivered_kg' => 800]);

        $this->actingAs($this->winery);
        $this->makeWineryReception(['total_weight' => 1000]);

        $delivery1->refresh();
        $delivery2->refresh();
        $this->assertEquals('pending', $delivery1->status);
        $this->assertEquals('pending', $delivery2->status);
    }

    public function test_reception_status_matched_when_discrepancy_le_5_percent(): void
    {
        $delivery = $this->makeDelivery(['delivered_kg' => 1000]);

        $this->actingAs($this->winery);
        $this->makeWineryReception(['total_weight' => 980]);  // 2% discrepancy

        $delivery->refresh();
        $this->assertEquals('matched', $delivery->status);
        $this->assertEquals(20, (float) $delivery->discrepancy_kg);
    }

    public function test_reception_status_disputed_when_discrepancy_gt_5_percent(): void
    {
        $delivery = $this->makeDelivery(['delivered_kg' => 1000]);

        $this->actingAs($this->winery);
        $this->makeWineryReception(['total_weight' => 800]);  // 20% discrepancy

        $delivery->refresh();
        $this->assertEquals('disputed', $delivery->status);
        $this->assertEquals(200, (float) $delivery->discrepancy_kg);
    }

    public function test_no_link_when_no_pending_delivery_exists(): void
    {
        $this->actingAs($this->winery);
        $reception = $this->makeWineryReception(['total_weight' => 1000]);

        // No delivery created → observer should not crash and reception is active
        $this->assertDatabaseHas('harvests', [
            'id' => $reception->id,
            'status' => 'active',
        ]);
    }

    // ── Updating winery reception weight resyncs delivery ────────────────────

    public function test_update_weight_resyncs_matched_to_disputed(): void
    {
        $delivery = $this->makeDelivery(['delivered_kg' => 1000]);

        $this->actingAs($this->winery);
        $reception = $this->makeWineryReception(['total_weight' => 1000]);

        $delivery->refresh();
        $this->assertEquals('matched', $delivery->status);

        // Winery corrects weight to 800 → 20% discrepancy → should become disputed
        $reception->update(['total_weight' => 800]);

        $delivery->refresh();
        $this->assertEquals('disputed', $delivery->status);
        $this->assertEquals(200, (float) $delivery->discrepancy_kg);
    }

    public function test_update_weight_resyncs_disputed_to_matched(): void
    {
        $delivery = $this->makeDelivery(['delivered_kg' => 1000]);

        $this->actingAs($this->winery);
        $reception = $this->makeWineryReception(['total_weight' => 800]);  // starts disputed

        $delivery->refresh();
        $this->assertEquals('disputed', $delivery->status);

        // Winery corrects weight to 990 → 1% discrepancy → should become matched
        $reception->update(['total_weight' => 990]);

        $delivery->refresh();
        $this->assertEquals('matched', $delivery->status);
    }

    public function test_update_weight_does_not_touch_resolved_delivery(): void
    {
        // A 'resolved' delivery is explicitly closed by both parties — must not be re-opened
        $delivery = $this->makeDelivery(['delivered_kg' => 1000]);

        $this->actingAs($this->winery);
        $reception = $this->makeWineryReception(['total_weight' => 800]);

        $delivery->update([
            'harvest_id' => $reception->id,
            'status' => 'resolved',
            'dispute_resolution_note' => 'Acordado entre partes',
            'dispute_resolved_at' => now(),
        ]);

        // Winery updates weight — observer must not touch resolved deliveries
        $reception->update(['total_weight' => 400]);

        $delivery->refresh();
        $this->assertEquals('resolved', $delivery->status);
    }

    // ── Cancelling / deleting reception resets delivery ──────────────────────

    public function test_cancelling_reception_resets_delivery_to_pending(): void
    {
        $delivery = $this->makeDelivery(['delivered_kg' => 1000]);

        $this->actingAs($this->winery);
        $reception = $this->makeWineryReception(['total_weight' => 1000]);

        $delivery->refresh();
        $this->assertEquals('matched', $delivery->status);

        $reception->update(['status' => 'cancelled']);

        $delivery->refresh();
        $this->assertEquals('pending', $delivery->status);
        $this->assertNull($delivery->harvest_id);
        $this->assertNull($delivery->discrepancy_kg);
    }

    public function test_deleting_reception_resets_delivery_to_pending(): void
    {
        $delivery = $this->makeDelivery(['delivered_kg' => 1000]);

        $this->actingAs($this->winery);
        $reception = $this->makeWineryReception(['total_weight' => 1000]);

        $delivery->refresh();
        $this->assertEquals('matched', $delivery->status);

        $reception->delete();

        $delivery->refresh();
        $this->assertEquals('pending', $delivery->status);
        $this->assertNull($delivery->harvest_id);
    }

    // ── Notifications on auto-link ────────────────────────────────────────────

    public function test_viticulturist_notified_on_auto_link_matched(): void
    {
        Notification::fake();

        $delivery = $this->makeDelivery(['delivered_kg' => 1000]);

        $this->actingAs($this->winery);
        $this->makeWineryReception(['total_weight' => 1000]);

        Notification::assertSentTo(
            $this->viticulturist,
            \App\Notifications\HarvestDeliveryMatchedNotification::class
        );
    }

    public function test_viticulturist_notified_on_auto_link_disputed(): void
    {
        Notification::fake();

        $delivery = $this->makeDelivery(['delivered_kg' => 1000]);

        $this->actingAs($this->winery);
        $this->makeWineryReception(['total_weight' => 800]);  // >5% → disputed

        Notification::assertSentTo(
            $this->viticulturist,
            \App\Notifications\HarvestDeliveryAutoDisputedNotification::class
        );
    }
}
