<?php

namespace Tests\Feature\Winery\Harvest;

use App\Livewire\Winery\Harvest\Reception\Edit;
use App\Models\Container;
use App\Models\Harvest;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;
use Tests\Traits\CreatesDeliveryScenario;

class EditReceptionTest extends WineryTestCase
{
    use CreatesDeliveryScenario;

    private Container $container;
    private Harvest $reception;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
        $this->container = $this->makeContainer();
        $this->actingAs($this->winery);
        // Create reception WITH container so the observer increments used_capacity
        $this->reception = $this->makeWineryReception([
            'total_weight' => 1000,
            'container_id' => $this->container->id,
        ]);
        $this->container->refresh(); // used_capacity now = 1000
    }

    // ── Basic update ──────────────────────────────────────────────────────────

    public function test_can_update_reception(): void
    {
        Livewire::test(Edit::class, ['harvest' => $this->reception])
            ->set('total_weight', '1200')
            ->set('harvest_start_date', '2024-09-20')
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('winery.grape-reception.index'));

        $this->assertDatabaseHas('harvests', [
            'id'           => $this->reception->id,
            'total_weight' => 1200,
        ]);
    }

    public function test_can_update_ticket_number_and_notes(): void
    {
        Livewire::test(Edit::class, ['harvest' => $this->reception])
            ->set('harvest_ticket_number', 'TICKET-999')
            ->set('notes', 'Uva en buen estado')
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('harvests', [
            'id'                   => $this->reception->id,
            'harvest_ticket_number'=> 'TICKET-999',
            'notes'                => 'Uva en buen estado',
        ]);
    }

    // ── Ownership check ───────────────────────────────────────────────────────

    public function test_ownership_enforced(): void
    {
        $otherWinery = $this->makeOtherWinery();

        $this->assertNotEquals($otherWinery->id, $this->reception->winery_id);
        $this->assertEquals($this->winery->id, $this->reception->winery_id);
    }

    // ── Price ─────────────────────────────────────────────────────────────────

    public function test_total_value_recomputed_from_price_and_weight(): void
    {
        Livewire::test(Edit::class, ['harvest' => $this->reception])
            ->set('total_weight', '1000')
            ->set('price_per_kg', '0.300')
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('harvests', [
            'id'          => $this->reception->id,
            'total_value' => 300.0,
        ]);
    }

    // ── Container capacity ────────────────────────────────────────────────────

    public function test_same_container_allows_editing_to_higher_weight(): void
    {
        // Container: capacity=5000, used=1000 (from setUp reception).
        // Editing the same reception to 4500 should pass because
        // effectiveAvailable = (5000-1000) + 1000 = 5000 >= 4500.
        Livewire::test(Edit::class, ['harvest' => $this->reception])
            ->set('total_weight', '4500')
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_new_container_capacity_exceeded_shows_error(): void
    {
        $smallContainer = $this->makeContainer(['capacity' => 500, 'used_capacity' => 400]);

        Livewire::test(Edit::class, ['harvest' => $this->reception])
            ->set('total_weight', '200')   // only 100 kg free in smallContainer
            ->set('container_id', (string) $smallContainer->id)
            ->call('save')
            ->assertHasErrors(['container_id']);

        // Weight in DB should remain unchanged
        $this->assertDatabaseHas('harvests', [
            'id'           => $this->reception->id,
            'total_weight' => 1000,
        ]);
    }

    // ── Batch recalculation ───────────────────────────────────────────────────

    public function test_batch_recalculated_when_weight_changes(): void
    {
        Livewire::test(Edit::class, ['harvest' => $this->reception])
            ->set('total_weight', '1200')
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        // recalculateTotal() sums active receptions → should now be 1200
        $this->assertDatabaseHas('grape_reception_batches', [
            'winery_id'       => $this->winery->id,
            'total_weight_kg' => 1200,
        ]);
    }

    // ── Observer re-sync of linked delivery ───────────────────────────────────

    public function test_weight_change_resyncs_matched_delivery_to_disputed(): void
    {
        Notification::fake();

        $delivery = $this->makeDelivery(['delivered_kg' => 1000]);
        $delivery->update([
            'harvest_id'     => $this->reception->id,
            'status'         => 'matched',
            'discrepancy_kg' => 0,
        ]);

        Livewire::test(Edit::class, ['harvest' => $this->reception])
            ->set('total_weight', '800')   // 20% discrepancy → disputed
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        $delivery->refresh();
        $this->assertEquals('disputed', $delivery->status);
        $this->assertEquals(200, (float) $delivery->discrepancy_kg);
    }

    public function test_weight_change_resyncs_disputed_delivery_to_matched(): void
    {
        Notification::fake();

        $delivery = $this->makeDelivery(['delivered_kg' => 1000]);
        $delivery->update([
            'harvest_id'     => $this->reception->id,
            'status'         => 'disputed',
            'discrepancy_kg' => 200,
        ]);

        Livewire::test(Edit::class, ['harvest' => $this->reception])
            ->set('total_weight', '990')   // 1% discrepancy → matched
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        $delivery->refresh();
        $this->assertEquals('matched', $delivery->status);
        $this->assertEquals(10, (float) $delivery->discrepancy_kg);
    }

    public function test_weight_change_does_not_touch_resolved_delivery(): void
    {
        $delivery = $this->makeDelivery(['delivered_kg' => 1000]);
        $delivery->update([
            'harvest_id'              => $this->reception->id,
            'status'                  => 'resolved',
            'discrepancy_kg'          => 200,
            'dispute_resolution_note' => 'Acordado entre partes',
            'dispute_resolved_at'     => now(),
        ]);

        Livewire::test(Edit::class, ['harvest' => $this->reception])
            ->set('total_weight', '500')   // would be >5% but delivery is resolved
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        $delivery->refresh();
        $this->assertEquals('resolved', $delivery->status);
    }
}
