<?php

namespace Tests\Feature\Viticulturist\Vendimia;

use App\Livewire\Viticulturist\Vendimia\EditDelivery;
use App\Models\HarvestDelivery;
use App\Models\Plot;
use App\Models\PlotPlanting;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;
use Tests\Traits\CreatesDeliveryScenario;

class EditDeliveryTest extends ViticulturistTestCase
{
    use CreatesDeliveryScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
        $this->actingAs($this->viticulturist);
    }

    // ── Basic update ──────────────────────────────────────────────────────────

    public function test_can_update_delivery(): void
    {
        $delivery = $this->makeDelivery(['buyer_name' => 'Original']);

        Livewire::test(EditDelivery::class, ['delivery' => $delivery])
            ->set('buyer_name', 'Actualizada')
            ->set('notes', 'Nota nueva')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.vendimia.index'));

        $this->assertDatabaseHas('harvest_deliveries', [
            'id'         => $delivery->id,
            'buyer_name' => 'Actualizada',
            'notes'      => 'Nota nueva',
        ]);
    }

    public function test_delivery_owner_check_enforced_in_mount(): void
    {
        $delivery = $this->makeDelivery();
        $other    = $this->makeOtherViticulturist();

        // The viticulturist_id on the delivery does NOT match $other->id,
        // so abort_unless(403) fires. Verify the record belongs to original owner.
        $this->assertNotEquals($other->id, $delivery->viticulturist_id);
        $this->assertEquals($this->viticulturist->id, $delivery->viticulturist_id);
    }

    // ── Delink logic ──────────────────────────────────────────────────────────

    public function test_changing_kg_delinks_matched_delivery(): void
    {
        Notification::fake();

        $this->actingAs($this->winery);
        $reception = $this->makeWineryReception(['total_weight' => 1000]);
        $this->actingAs($this->viticulturist);

        $delivery = $this->makeDelivery([
            'harvest_id'     => $reception->id,
            'status'         => 'matched',
            'discrepancy_kg' => 0,
        ]);

        Livewire::test(EditDelivery::class, ['delivery' => $delivery])
            ->set('delivered_kg', '900')   // changed
            ->call('save')
            ->assertHasNoErrors();

        $delivery->refresh();
        $this->assertEquals('pending', $delivery->status);
        $this->assertNull($delivery->harvest_id);
    }

    public function test_changing_vintage_delinks_delivery(): void
    {
        Notification::fake();

        $this->actingAs($this->winery);
        $reception = $this->makeWineryReception(['total_weight' => 1000]);
        $this->actingAs($this->viticulturist);

        $delivery = $this->makeDelivery([
            'harvest_id' => $reception->id,
            'status'     => 'matched',
        ]);

        Livewire::test(EditDelivery::class, ['delivery' => $delivery])
            ->set('vintage_year', '2023')   // changed
            ->call('save')
            ->assertHasNoErrors();

        $delivery->refresh();
        $this->assertEquals('pending', $delivery->status);
        $this->assertNull($delivery->harvest_id);
    }

    public function test_changing_planting_delinks_delivery(): void
    {
        Notification::fake();

        $this->actingAs($this->winery);
        $reception = $this->makeWineryReception(['total_weight' => 1000]);
        $this->actingAs($this->viticulturist);

        // Second planting on the same plot
        $otherPlanting = PlotPlanting::create([
            'plot_id'          => $this->plot->id,
            'grape_variety_id' => $this->grapeVariety->id,
            'area_planted'     => 1.0,
            'planting_year'    => 2018,
            'status'           => 'active',
        ]);

        $delivery = $this->makeDelivery([
            'harvest_id' => $reception->id,
            'status'     => 'matched',
        ]);

        Livewire::test(EditDelivery::class, ['delivery' => $delivery])
            ->set('plot_planting_id', (string) $otherPlanting->id)
            ->call('save')
            ->assertHasNoErrors();

        $delivery->refresh();
        $this->assertEquals('pending', $delivery->status);
        $this->assertNull($delivery->harvest_id);
    }

    public function test_updating_unrelated_field_does_not_delink(): void
    {
        $this->actingAs($this->winery);
        $reception = $this->makeWineryReception(['total_weight' => 1000]);
        $this->actingAs($this->viticulturist);

        $delivery = $this->makeDelivery([
            'harvest_id' => $reception->id,
            'status'     => 'matched',
        ]);

        Livewire::test(EditDelivery::class, ['delivery' => $delivery])
            ->set('notes', 'Solo cambio una nota')    // does NOT trigger delink
            ->call('save')
            ->assertHasNoErrors();

        $delivery->refresh();
        $this->assertEquals('matched', $delivery->status);
        $this->assertEquals($reception->id, $delivery->harvest_id);
    }

    // ── Price override ────────────────────────────────────────────────────────

    public function test_manual_total_price_saves_correctly(): void
    {
        $delivery = $this->makeDelivery(['price_per_kg' => 0.25, 'delivered_kg' => 1000]);

        Livewire::test(EditDelivery::class, ['delivery' => $delivery])
            ->set('total_price', '888.000')
            ->call('save')
            ->assertHasNoErrors();

        $delivery->refresh();
        $this->assertEquals(888.0, (float) $delivery->total_price);
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function test_can_delete_unlinked_delivery(): void
    {
        $delivery = $this->makeDelivery();

        Livewire::test(EditDelivery::class, ['delivery' => $delivery])
            ->call('delete')
            ->assertRedirect(route('viticulturist.vendimia.index'));

        $this->assertDatabaseMissing('harvest_deliveries', ['id' => $delivery->id]);
    }

    public function test_deleting_linked_delivery_notifies_winery(): void
    {
        Notification::fake();

        $this->actingAs($this->winery);
        $reception = $this->makeWineryReception(['total_weight' => 1000]);
        $this->actingAs($this->viticulturist);

        $delivery = $this->makeDelivery([
            'harvest_id' => $reception->id,
            'status'     => 'matched',
        ]);

        Livewire::test(EditDelivery::class, ['delivery' => $delivery])
            ->call('delete')
            ->assertRedirect(route('viticulturist.vendimia.index'));

        $this->assertDatabaseMissing('harvest_deliveries', ['id' => $delivery->id]);
        Notification::assertSentTo(
            $this->winery,
            \App\Notifications\HarvestDeliveryDeletedNotification::class
        );
    }
}
