<?php

namespace Tests\Feature\Viticulturist\HarvestSale;

use App\Livewire\Viticulturist\Billing\HarvestSale\Index;
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

class IndexTest extends ViticulturistTestCase
{
    private User $viticulturist;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viticulturist = $this->makeViticulturist();
        $this->actingAs($this->viticulturist);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeHarvest(float $weight = 1000): Harvest
    {
        $campaign = Campaign::getOrCreateActiveForYear($this->viticulturist->id);

        $activity = AgriculturalActivity::create([
            'viticulturist_id' => $this->viticulturist->id,
            'campaign_id'      => $campaign->id,
            'activity_type'    => 'harvest',
            'activity_date'    => now()->toDateString(),
        ]);

        return Harvest::create([
            'activity_id'        => $activity->id,
            'total_weight'       => $weight,
            'vintage'            => now()->year,
            'harvest_start_date' => now()->toDateString(),
        ]);
    }

    private function makeInvoice(Harvest $harvest, float $qty = 500, array $attrs = []): Invoice
    {
        $invoice = Invoice::create(array_merge([
            'user_id'              => $this->viticulturist->id,
            'invoice_type'         => 'harvest_sale',
            'invoice_number'       => 'HS-2024-0001',
            'delivery_note_code'   => 'VEN-2024-0001',
            'invoice_date'         => '2024-10-01',
            'billing_company_name' => 'Bodega Test S.L.',
            'status'               => 'draft',
            'payment_status'       => 'unpaid',
            'delivery_status'      => 'pending',
            'subtotal'             => $qty * 0.45,
            'tax_base'             => $qty * 0.45,
            'tax_amount'           => $qty * 0.45 * 0.02,
            'total_amount'         => $qty * 0.45 * 0.98,
        ], $attrs));

        $item = InvoiceItem::create([
            'invoice_id'      => $invoice->id,
            'harvest_id'      => $harvest->id,
            'concept_type'    => 'harvest',
            'name'            => 'Cosecha test',
            'quantity'        => $qty,
            'unit_price'      => 0.45,
            'tax_rate'        => 2,
            'subtotal'        => $qty * 0.45,
            'tax_base'        => $qty * 0.45,
            'tax_amount'      => $qty * 0.45 * 0.02,
            'total'           => $qty * 0.45 * 0.98,
            'delivery_status' => 'pending',
        ]);

        // Simulate reservation in harvest_stocks
        HarvestStock::create([
            'harvest_id'      => $harvest->id,
            'user_id'         => $this->viticulturist->id,
            'movement_type'   => 'reserve',
            'quantity_change' => -$qty,
            'quantity_after'  => 1000 - $qty,
            'available_qty'   => 1000 - $qty,
            'reserved_qty'    => $qty,
            'sold_qty'        => 0,
            'gifted_qty'      => 0,
            'lost_qty'        => 0,
            'reference_number' => (string) $invoice->id,
        ]);

        return $invoice;
    }

    private function makeMarketedHarvest(Harvest $harvest, Invoice $invoice): MarketedHarvest
    {
        $campaign = Campaign::getOrCreateActiveForYear($this->viticulturist->id);

        return MarketedHarvest::create([
            'harvest_id'       => $harvest->id,
            'campaign_id'      => $campaign->id,
            'viticulturist_id' => $this->viticulturist->id,
            'delivery_date'    => '2024-10-01',
            'quantity_kg'      => 500,
            'destination_type' => 'third_party',
            'buyer_name'       => 'Bodega Test S.L.',
            'price_per_kg'     => 0.45,
            'total_value'      => 225,
            'invoice_id'       => $invoice->id,
        ]);
    }

    // ── Visibility ────────────────────────────────────────────────────────────

    public function test_shows_own_invoices_only(): void
    {
        $harvest = $this->makeHarvest();
        $this->makeInvoice($harvest, 500, ['invoice_number' => 'HS-2024-0001']);

        $other = Invoice::create([
            'user_id'              => $this->makeOtherViticulturist()->id,
            'invoice_type'         => 'harvest_sale',
            'invoice_number'       => 'HS-2024-0099',
            'invoice_date'         => '2024-10-01',
            'billing_company_name' => 'Otra Bodega',
            'status'               => 'draft',
            'payment_status'       => 'unpaid',
            'delivery_status'      => 'pending',
            'subtotal'             => 0,
            'tax_base'             => 0,
            'tax_amount'           => 0,
            'total_amount'         => 0,
        ]);

        Livewire::test(Index::class)
            ->assertSee('HS-2024-0001')
            ->assertDontSee('HS-2024-0099');
    }

    public function test_search_filters_by_invoice_number(): void
    {
        $harvestA = $this->makeHarvest();
        $harvestB = $this->makeHarvest();
        $this->makeInvoice($harvestA, 500, ['invoice_number' => 'HS-2024-0001', 'delivery_note_code' => 'VEN-2024-0001']);
        $this->makeInvoice($harvestB, 300, ['invoice_number' => 'HS-2024-0002', 'delivery_note_code' => 'VEN-2024-0002']);

        Livewire::test(Index::class)
            ->set('search', 'HS-2024-0001')
            ->assertSee('HS-2024-0001')
            ->assertDontSee('HS-2024-0002');
    }

    // ── markDelivered ─────────────────────────────────────────────────────────

    public function test_mark_delivered_sells_harvest_stock(): void
    {
        $harvest = $this->makeHarvest(1000);
        $invoice = $this->makeInvoice($harvest, 500);

        Livewire::test(Index::class)
            ->call('markDelivered', $invoice->id);

        $this->assertEquals('delivered', $invoice->fresh()->delivery_status);

        // After sell: latest stock entry should show sold_qty = 500
        $latest = HarvestStock::where('harvest_id', $harvest->id)->latest()->first();
        $this->assertEquals(500, (float) $latest->sold_qty);
        $this->assertEquals(0,   (float) $latest->reserved_qty);
    }

    public function test_mark_delivered_cancelled_invoice_does_nothing(): void
    {
        $harvest = $this->makeHarvest();
        $invoice = $this->makeInvoice($harvest, 500, ['status' => 'cancelled', 'delivery_status' => 'cancelled']);

        Livewire::test(Index::class)
            ->call('markDelivered', $invoice->id);

        $this->assertEquals('cancelled', $invoice->fresh()->delivery_status);
    }

    // ── Cancel ────────────────────────────────────────────────────────────────

    public function test_cancel_releases_harvest_stock(): void
    {
        $harvest = $this->makeHarvest(1000);
        $invoice = $this->makeInvoice($harvest, 500);

        Livewire::test(Index::class)
            ->call('cancel', $invoice->id);

        $this->assertEquals('cancelled', $invoice->fresh()->status);

        // After unreserve: latest entry should have available back to 1000
        $latest = HarvestStock::where('harvest_id', $harvest->id)->latest()->first();
        $this->assertEquals(1000, (float) $latest->available_qty);
        $this->assertEquals(0,    (float) $latest->reserved_qty);
    }

    public function test_cancel_clears_marketed_harvest_invoice_id(): void
    {
        $harvest = $this->makeHarvest();
        $invoice = $this->makeInvoice($harvest);
        $mh      = $this->makeMarketedHarvest($harvest, $invoice);

        Livewire::test(Index::class)
            ->call('cancel', $invoice->id);

        $this->assertNull($mh->fresh()->invoice_id);
    }

    public function test_cancel_paid_invoice_is_rejected(): void
    {
        $harvest = $this->makeHarvest();
        $invoice = $this->makeInvoice($harvest, 500, ['payment_status' => 'paid']);

        Livewire::test(Index::class)
            ->call('cancel', $invoice->id);

        $this->assertEquals('draft', $invoice->fresh()->status);
    }
}
