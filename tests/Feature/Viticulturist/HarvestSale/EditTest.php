<?php

namespace Tests\Feature\Viticulturist\HarvestSale;

use App\Livewire\Viticulturist\Billing\HarvestSale\Edit;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\HarvestStock;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MarketedHarvest;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    private User $viticulturist;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viticulturist = $this->makeViticulturist();
        $this->actingAs($this->viticulturist);
    }

    // ── Tests ─────────────────────────────────────────────────────────────────

    public function test_save_recalculates_totals_and_adjusts_stock(): void
    {
        $harvest = $this->makeHarvest(1000);
        $invoice = $this->makeInvoice($harvest, 500);

        // Edit: change quantity to 300 kg at 0.50 €/kg
        // Expected: subtotal = 150, 2% IRPF = 3, total = 147
        Livewire::test(Edit::class, ['id' => $invoice->id])
            ->set('buyer_name', 'Nueva Bodega S.L.')
            ->set('destination_type', 'third_party')
            ->set('delivery_date', '2024-11-01')
            ->set('invoice_date', '2024-11-01')
            ->set('lines', $this->linesPayload($harvest, 300, 0.50))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.invoices.harvest-sale.index'));

        $fresh = $invoice->fresh();
        $this->assertEquals(150.0, (float) $fresh->subtotal);
        $this->assertEquals(3.0, (float) $fresh->tax_amount);
        $this->assertEquals(147.0, (float) $fresh->total_amount);

        // billing_company_name updated
        $this->assertEquals('Nueva Bodega S.L.', $fresh->billing_company_name);

        // MarketedHarvest re-created with new quantity
        $this->assertDatabaseHas('marketed_harvests', [
            'invoice_id' => $invoice->id,
            'quantity_kg' => 300,
            'buyer_name' => 'Nueva Bodega S.L.',
        ]);

        // Latest HarvestStock shows new reservation (300 reserved, 700 available)
        $latest = HarvestStock::where('harvest_id', $harvest->id)->orderByDesc('id')->first();
        $this->assertEquals(300, (float) $latest->reserved_qty);
        $this->assertEquals(700, (float) $latest->available_qty);
    }

    public function test_invoice_number_immutable_after_save(): void
    {
        $harvest = $this->makeHarvest(1000);
        $invoice = $this->makeInvoice($harvest, 500, ['invoice_number' => 'HS-2024-0001']);

        Livewire::test(Edit::class, ['id' => $invoice->id])
            ->set('buyer_name', 'Bodega Actualizada S.L.')
            ->set('destination_type', 'cooperative')
            ->set('delivery_date', '2024-11-01')
            ->set('invoice_date', '2024-11-01')
            ->set('lines', $this->linesPayload($harvest, 500))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('HS-2024-0001', $invoice->fresh()->invoice_number);
    }

    public function test_locked_paid_invoice_cannot_be_saved(): void
    {
        $harvest = $this->makeHarvest(1000);
        $invoice = $this->makeInvoice($harvest, 500, ['payment_status' => 'paid']);

        Livewire::test(Edit::class, ['id' => $invoice->id])
            ->set('buyer_name', 'Intento de cambio')
            ->set('lines', $this->linesPayload($harvest, 100, 9.99))
            ->call('save');

        // Totals unchanged (locked)
        $this->assertEquals(225.0, (float) $invoice->fresh()->subtotal);
    }

    public function test_locked_cancelled_invoice_cannot_be_saved(): void
    {
        $harvest = $this->makeHarvest(1000);
        $invoice = $this->makeInvoice($harvest, 500, ['status' => 'cancelled']);

        Livewire::test(Edit::class, ['id' => $invoice->id])
            ->set('buyer_name', 'Intento de cambio')
            ->set('lines', $this->linesPayload($harvest, 100, 9.99))
            ->call('save');

        $this->assertEquals(225.0, (float) $invoice->fresh()->subtotal);
    }

    public function test_harvest_from_other_viticulturist_is_rejected(): void
    {
        $harvest = $this->makeHarvest(1000);
        $invoice = $this->makeInvoice($harvest, 500);
        $other = $this->makeOtherViticulturist();
        $foreignHarvest = $this->makeHarvest(1000, $other);

        // Try to swap the line to use a foreign harvest
        Livewire::test(Edit::class, ['id' => $invoice->id])
            ->set('buyer_name', 'Bodega Test S.L.')
            ->set('destination_type', 'third_party')
            ->set('delivery_date', '2024-11-01')
            ->set('invoice_date', '2024-11-01')
            ->set('lines', [[
                'harvest_id' => $foreignHarvest->id,
                'quantity' => '500',
                'unit_price' => '0.45',
                'tax_rate' => '2',
                'description' => '',
            ]])
            ->call('save');

        // Invoice must remain unchanged (exception caught, no redirect)
        $this->assertEquals(225.0, (float) $invoice->fresh()->subtotal);
    }

    public function test_other_viticulturist_invoice_returns_404(): void
    {
        $other = $this->makeOtherViticulturist();
        $otherInvoice = Invoice::create([
            'user_id' => $other->id,
            'invoice_type' => 'harvest_sale',
            'invoice_number' => 'HS-2024-9999',
            'delivery_note_code' => 'VEN-2024-9999',
            'invoice_date' => '2024-10-01',
            'billing_company_name' => 'Otra Bodega S.L.',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'delivery_status' => 'pending',
            'subtotal' => 0,
            'tax_base' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Edit::class, ['id' => $otherInvoice->id]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeHarvest(float $weight = 1000, ?User $owner = null): Harvest
    {
        $owner = $owner ?? $this->viticulturist;
        $campaign = Campaign::getOrCreateActiveForYear($owner->id);

        $activity = AgriculturalActivity::create([
            'viticulturist_id' => $owner->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now()->toDateString(),
        ]);

        return Harvest::create([
            'activity_id' => $activity->id,
            'total_weight' => $weight,
            'vintage' => now()->year,
            'harvest_start_date' => now()->toDateString(),
        ]);
    }

    /** Creates a draft invoice + item + HarvestStock reservation */
    private function makeInvoice(Harvest $harvest, float $qty = 500, array $attrs = []): Invoice
    {
        $campaign = Campaign::getOrCreateActiveForYear($this->viticulturist->id);

        $invoice = Invoice::create(array_merge([
            'user_id' => $this->viticulturist->id,
            'invoice_type' => 'harvest_sale',
            'invoice_number' => 'HS-2024-0001',
            'delivery_note_code' => 'VEN-2024-0001',
            'invoice_date' => '2024-10-01',
            'billing_company_name' => 'Bodega Test S.L.',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'delivery_status' => 'pending',
            'subtotal' => $qty * 0.45,
            'tax_base' => $qty * 0.45,
            'tax_amount' => $qty * 0.45 * 0.02,
            'total_amount' => $qty * 0.45 * 0.98,
        ], $attrs));

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $harvest->id,
            'concept_type' => 'harvest',
            'name' => 'Cosecha test',
            'quantity' => $qty,
            'unit_price' => 0.45,
            'tax_rate' => 2,
            'subtotal' => $qty * 0.45,
            'tax_base' => $qty * 0.45,
            'tax_amount' => $qty * 0.45 * 0.02,
            'total' => $qty * 0.45 * 0.98,
            'delivery_status' => 'pending',
        ]);

        MarketedHarvest::create([
            'harvest_id' => $harvest->id,
            'campaign_id' => $campaign->id,
            'viticulturist_id' => $this->viticulturist->id,
            'delivery_date' => '2024-10-01',
            'quantity_kg' => $qty,
            'destination_type' => 'third_party',
            'buyer_name' => 'Bodega Test S.L.',
            'price_per_kg' => 0.45,
            'total_value' => round($qty * 0.45, 2),
            'invoice_id' => $invoice->id,
        ]);

        // Note: InvoiceItemObserver::created() already reserved stock via ContainerStockService
        // when InvoiceItem::create() was called above. No manual HarvestStock needed.

        return $invoice;
    }

    private function linesPayload(Harvest $harvest, float $qty = 300, float $price = 0.50): array
    {
        return [[
            'harvest_id' => $harvest->id,
            'quantity' => (string) $qty,
            'unit_price' => (string) $price,
            'tax_rate' => '2',
            'description' => '',
        ]];
    }
}
