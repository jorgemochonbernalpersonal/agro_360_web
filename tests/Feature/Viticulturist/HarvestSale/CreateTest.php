<?php

namespace Tests\Feature\Viticulturist\HarvestSale;

use App\Livewire\Viticulturist\Billing\HarvestSale\Create;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\Invoice;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    private User $viticulturist;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viticulturist = $this->makeViticulturist();
        $this->actingAs($this->viticulturist);
    }

    // ── Happy path ────────────────────────────────────────────────────────────

    public function test_creates_invoice_and_reserves_harvest_stock(): void
    {
        $harvest = $this->makeHarvest(1000);

        Livewire::test(Create::class)
            ->set('buyer_name', 'Bodegas Castillo S.L.')
            ->set('destination_type', 'third_party')
            ->set('delivery_date', '2024-10-31')
            ->set('invoice_date', '2024-10-31')
            ->set('lines', $this->validLines($harvest->id, 500))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.invoices.harvest-sale.index'));

        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->viticulturist->id,
            'invoice_type' => 'harvest_sale',
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'harvest_id' => $harvest->id,
            'concept_type' => 'harvest',
            'quantity' => 500,
        ]);

        // Stock: 500 kg reserved in harvest_stocks table
        $this->assertDatabaseHas('harvest_stocks', [
            'harvest_id' => $harvest->id,
            'movement_type' => 'reserve',
        ]);
    }

    public function test_invoice_number_format_and_seq_increments(): void
    {
        $harvest = $this->makeHarvest();

        Livewire::test(Create::class)
            ->set('buyer_name', 'Bodega Test')
            ->set('destination_type', 'cooperative')
            ->set('delivery_date', '2024-10-31')
            ->set('invoice_date', '2024-10-31')
            ->set('lines', $this->validLines($harvest->id))
            ->call('save')
            ->assertHasNoErrors();

        $year = now()->year;
        $invoice = Invoice::where('user_id', $this->viticulturist->id)->first();

        $this->assertStringStartsWith("HS-{$year}-", $invoice->invoice_number);
        $this->assertStringStartsWith("VEN-{$year}-", $invoice->delivery_note_code);
        $this->assertEquals(1, $this->viticulturist->fresh()->harvest_sale_seq);
    }

    public function test_total_is_subtotal_minus_irpf_retention(): void
    {
        // 500 kg × 0.45 = 225 base; 2% IRPF = 4.5; total = 220.5
        $harvest = $this->makeHarvest(1000);

        Livewire::test(Create::class)
            ->set('buyer_name', 'Bodega Test')
            ->set('destination_type', 'third_party')
            ->set('delivery_date', '2024-10-31')
            ->set('invoice_date', '2024-10-31')
            ->set('lines', $this->validLines($harvest->id, 500, 0.45))
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::where('user_id', $this->viticulturist->id)->first();
        $this->assertEquals(225.0, (float) $invoice->subtotal);
        $this->assertEquals(4.5, (float) $invoice->tax_amount);
        $this->assertEquals(220.5, (float) $invoice->total_amount);
    }

    public function test_creates_marketed_harvest_record(): void
    {
        $harvest = $this->makeHarvest();

        Livewire::test(Create::class)
            ->set('buyer_name', 'Bodega Castillo')
            ->set('buyer_rega_code', 'ES123456')
            ->set('destination_type', 'own_winery')
            ->set('delivery_date', '2024-10-31')
            ->set('invoice_date', '2024-10-31')
            ->set('lines', $this->validLines($harvest->id))
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::where('user_id', $this->viticulturist->id)->first();

        $this->assertDatabaseHas('marketed_harvests', [
            'harvest_id' => $harvest->id,
            'viticulturist_id' => $this->viticulturist->id,
            'buyer_name' => 'Bodega Castillo',
            'buyer_rega_code' => 'ES123456',
            'destination_type' => 'own_winery',
            'invoice_id' => $invoice->id,
        ]);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    public function test_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('buyer_name', '')
            ->set('destination_type', '')
            ->set('delivery_date', '')
            ->set('invoice_date', '')
            ->set('lines', [])
            ->call('save')
            ->assertHasErrors(['buyer_name', 'destination_type', 'delivery_date', 'invoice_date', 'lines']);
    }

    public function test_validates_line_fields(): void
    {
        $harvest = $this->makeHarvest();

        Livewire::test(Create::class)
            ->set('buyer_name', 'Bodega Test')
            ->set('destination_type', 'third_party')
            ->set('delivery_date', '2024-10-31')
            ->set('invoice_date', '2024-10-31')
            ->set('lines', [[
                'harvest_id' => $harvest->id,
                'quantity' => '',      // required
                'unit_price' => '',      // required
                'tax_rate' => '150',   // max:100
                'description' => '',
            ]])
            ->call('save')
            ->assertHasErrors([
                'lines.0.quantity',
                'lines.0.unit_price',
                'lines.0.tax_rate',
            ]);
    }

    // ── Ownership guard ───────────────────────────────────────────────────────

    public function test_harvest_from_other_viticulturist_is_rejected(): void
    {
        $other = $this->makeOtherViticulturist();
        $foreignHarvest = $this->makeHarvest(1000, $other);

        Livewire::test(Create::class)
            ->set('buyer_name', 'Bodega Test')
            ->set('destination_type', 'third_party')
            ->set('delivery_date', '2024-10-31')
            ->set('invoice_date', '2024-10-31')
            ->set('lines', $this->validLines($foreignHarvest->id))
            ->call('save');

        // No invoice should be created
        $this->assertDatabaseMissing('invoices', ['user_id' => $this->viticulturist->id]);
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

    private function validLines(int $harvestId, float $qty = 500, float $price = 0.45): array
    {
        return [[
            'harvest_id' => $harvestId,
            'quantity' => (string) $qty,
            'unit_price' => (string) $price,
            'tax_rate' => '2',
            'description' => '',
        ]];
    }
}
