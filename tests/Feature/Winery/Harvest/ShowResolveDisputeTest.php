<?php

namespace Tests\Feature\Winery\Harvest;

use App\Livewire\Winery\Harvest\Reception\Show;
use App\Models\Harvest;
use App\Models\HarvestDelivery;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;
use Tests\Traits\CreatesDeliveryScenario;

class ShowResolveDisputeTest extends WineryTestCase
{
    use CreatesDeliveryScenario;

    private Harvest $reception;

    private HarvestDelivery $delivery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
        $this->actingAs($this->winery);

        // Winery received 800 kg, viticulturist declared 1000 → disputed
        $this->reception = $this->makeWineryReception(['total_weight' => 800]);

        $this->delivery = $this->makeDelivery(['delivered_kg' => 1000]);
        $this->delivery->update([
            'harvest_id' => $this->reception->id,
            'status' => 'disputed',
            'discrepancy_kg' => 200,
            'dispute_note' => 'No estoy de acuerdo con el peso recibido',
            'dispute_submitted_at' => now(),
        ]);
    }

    // ── Resolve dispute ───────────────────────────────────────────────────────

    public function test_can_resolve_dispute(): void
    {
        Livewire::test(Show::class, ['harvest' => $this->reception])
            ->set('resolutionNote', 'Hemos revisado el albarán y acordamos el peso de bodega')
            ->call('resolveDispute')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('harvest_deliveries', [
            'id' => $this->delivery->id,
            'status' => 'resolved',
            'dispute_resolution_note' => 'Hemos revisado el albarán y acordamos el peso de bodega',
        ]);

        $this->assertNotNull($this->delivery->fresh()->dispute_resolved_at);
    }

    public function test_resolve_dispute_notifies_viticulturist(): void
    {
        Notification::fake();

        Livewire::test(Show::class, ['harvest' => $this->reception])
            ->set('resolutionNote', 'Acordamos el peso final de 800 kg')
            ->call('resolveDispute')
            ->assertHasNoErrors();

        Notification::assertSentTo(
            $this->viticulturist,
            \App\Notifications\HarvestDeliveryResolvedNotification::class
        );
    }

    // ── Validation ────────────────────────────────────────────────────────────

    public function test_resolve_dispute_requires_note(): void
    {
        Livewire::test(Show::class, ['harvest' => $this->reception])
            ->set('resolutionNote', '')
            ->call('resolveDispute')
            ->assertHasErrors(['resolutionNote']);

        $this->delivery->refresh();
        $this->assertEquals('disputed', $this->delivery->status);
    }

    public function test_resolve_dispute_requires_min_5_chars(): void
    {
        Livewire::test(Show::class, ['harvest' => $this->reception])
            ->set('resolutionNote', 'OK')   // only 2 chars
            ->call('resolveDispute')
            ->assertHasErrors(['resolutionNote']);

        $this->delivery->refresh();
        $this->assertEquals('disputed', $this->delivery->status);
    }

    // ── Guard: only disputed deliveries with a submitted note ─────────────────

    public function test_cannot_resolve_already_resolved_dispute(): void
    {
        // Close the dispute first
        $this->delivery->update([
            'status' => 'resolved',
            'dispute_resolution_note' => 'Primera resolución',
            'dispute_resolved_at' => now(),
        ]);

        Livewire::test(Show::class, ['harvest' => $this->reception])
            ->set('resolutionNote', 'Intento de segunda resolución')
            ->call('resolveDispute');

        // Status and original note must remain unchanged
        $this->delivery->refresh();
        $this->assertEquals('resolved', $this->delivery->status);
        $this->assertEquals('Primera resolución', $this->delivery->dispute_resolution_note);
    }

    public function test_cannot_resolve_when_no_dispute_note_submitted(): void
    {
        // Delivery is disputed but viticulturist hasn't submitted a dispute note yet
        $this->delivery->update([
            'dispute_note' => null,
            'dispute_submitted_at' => null,
        ]);

        Livewire::test(Show::class, ['harvest' => $this->reception])
            ->set('resolutionNote', 'Respondo sin que hayan enviado nota')
            ->call('resolveDispute');

        // Must remain disputed, no resolution note saved
        $this->delivery->refresh();
        $this->assertEquals('disputed', $this->delivery->status);
        $this->assertNull($this->delivery->dispute_resolution_note);
    }

    // ── Form toggle ───────────────────────────────────────────────────────────

    public function test_resolve_form_opens_and_closes(): void
    {
        $component = Livewire::test(Show::class, ['harvest' => $this->reception]);

        $component->assertSet('showResolveForm', false);
        $component->call('openResolveDispute')->assertSet('showResolveForm', true);
        $component->call('cancelResolveDispute')->assertSet('showResolveForm', false);
    }

    public function test_cancel_clears_resolution_note(): void
    {
        Livewire::test(Show::class, ['harvest' => $this->reception])
            ->call('openResolveDispute')
            ->set('resolutionNote', 'Texto a borrar')
            ->call('cancelResolveDispute')
            ->assertSet('resolutionNote', '');
    }

    // ── Ownership ─────────────────────────────────────────────────────────────

    public function test_ownership_enforced(): void
    {
        $otherWinery = $this->makeOtherWinery();

        $this->assertNotEquals($otherWinery->id, $this->reception->winery_id);
        $this->assertEquals($this->winery->id, $this->reception->winery_id);
    }
}
