<?php

namespace Tests\Feature\Winery\Billing\GrapePurchase;

use App\Livewire\Winery\Billing\GrapePurchase\Create;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;
use Tests\Traits\CreatesDeliveryScenario;

class CreateTest extends WineryTestCase
{
    use CreatesDeliveryScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
        $this->actingAs($this->winery);
    }

    // ── Happy path ────────────────────────────────────────────────────────────

    public function test_creates_invoice_and_item_for_one_harvest(): void
    {
        $harvest = $this->makeWineryReception(['total_weight' => 500]);

        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('invoice_date', '2024-10-31')
            ->set('payment_type', 'transfer')
            ->set('lines', $this->validLines($harvest->id))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('winery.invoices.grape-purchase.index'));

        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'invoice_type' => 'grape_purchase',
            'status' => 'draft',
            'payment_status' => 'unpaid',
        ]);

        $invoice = Invoice::where('user_id', $this->winery->id)
            ->where('invoice_type', 'grape_purchase')
            ->first();

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'harvest_id' => $harvest->id,
            'concept_type' => 'harvest',
        ]);
    }

    public function test_invoice_number_format_and_seq_increments(): void
    {
        $harvest = $this->makeWineryReception();

        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('invoice_date', '2024-10-31')
            ->set('lines', $this->validLines($harvest->id))
            ->call('save')
            ->assertHasNoErrors();

        $year = now()->year;
        $invoice = Invoice::where('user_id', $this->winery->id)->first();

        $this->assertStringStartsWith("GP-{$year}-", $invoice->invoice_number);
        $this->assertStringStartsWith("LIQ-{$year}-", $invoice->delivery_note_code);
        $this->assertEquals(1, $this->winery->fresh()->grape_purchase_seq);
    }

    public function test_total_is_subtotal_minus_retention(): void
    {
        // 500 kg × 0.45 €/kg = 225 € base; 2% retención = 4.5 €; total = 220.5 €
        $harvest = $this->makeWineryReception(['total_weight' => 500]);

        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('invoice_date', '2024-10-31')
            ->set('lines', $this->validLines($harvest->id, '500', '0.450', '2'))
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::where('user_id', $this->winery->id)->first();
        $this->assertEquals(225.0, (float) $invoice->subtotal);
        $this->assertEquals(4.5, (float) $invoice->tax_amount);
        $this->assertEquals(220.5, (float) $invoice->total_amount);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    public function test_validates_required_header_fields(): void
    {
        Livewire::test(Create::class)
            ->set('viticulturist_id', '')
            ->set('invoice_date', '')
            ->set('lines', [])
            ->call('save')
            ->assertHasErrors(['viticulturist_id', 'invoice_date', 'lines']);
    }

    public function test_validates_line_fields(): void
    {
        $harvest = $this->makeWineryReception();

        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('invoice_date', '2024-10-31')
            ->set('lines', [[
                'harvest_id' => $harvest->id,
                'quantity' => '',        // required
                'unit_price' => '',        // required
                'tax_rate' => '150',     // max:100
                'description' => '',
            ]])
            ->call('save')
            ->assertHasErrors([
                'lines.0.quantity',
                'lines.0.unit_price',
                'lines.0.tax_rate',
            ]);
    }

    // ── Ownership guards ──────────────────────────────────────────────────────

    public function test_viticulturist_not_linked_to_winery_fails_validation(): void
    {
        $stranger = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);
        $harvest = $this->makeWineryReception();

        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $stranger->id)
            ->set('invoice_date', '2024-10-31')
            ->set('lines', $this->validLines($harvest->id))
            ->call('save')
            ->assertHasErrors(['viticulturist_id']);
    }

    public function test_harvest_belonging_to_other_winery_is_rejected(): void
    {
        $otherWinery = $this->makeOtherWinery();

        // Link same viticulturist to this winery too (but harvest belongs to otherWinery)
        $harvestOther = $this->makeWineryReception();
        $harvestOther->update(['winery_id' => $otherWinery->id]);

        $this->assertDatabaseMissing('invoices', ['user_id' => $this->winery->id]);

        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('invoice_date', '2024-10-31')
            ->set('lines', $this->validLines($harvestOther->id))
            ->call('save');

        // No invoice should be created — winery_id guard fires before DB commit
        $this->assertDatabaseMissing('invoices', ['user_id' => $this->winery->id]);
    }

    // ── Double-invoicing guard ────────────────────────────────────────────────

    public function test_harvest_already_invoiced_is_rejected(): void
    {
        $harvest = $this->makeWineryReception();

        // Create an existing active invoice for that harvest
        $existing = Invoice::create([
            'user_id' => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'invoice_type' => 'grape_purchase',
            'invoice_number' => 'GP-2024-0001',
            'invoice_date' => '2024-10-01',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'subtotal' => 0,
            'tax_base' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
        ]);
        // withoutEvents: bypass InvoiceItemObserver to avoid HarvestStock side-effects
        InvoiceItem::withoutEvents(fn () => InvoiceItem::create([
            'invoice_id' => $existing->id,
            'harvest_id' => $harvest->id,
            'concept_type' => 'harvest',
            'name' => 'Test',
            'quantity' => 500,
            'unit_price' => 0.45,
            'tax_rate' => 2,
            'subtotal' => 225,
            'tax_base' => 225,
            'tax_amount' => 4.5,
            'total' => 220.5,
        ]));

        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('invoice_date', '2024-10-31')
            ->set('lines', $this->validLines($harvest->id))
            ->call('save');

        // Only the pre-existing invoice should exist
        $this->assertDatabaseCount('invoices', 1);
    }

    public function test_harvest_in_cancelled_invoice_can_be_reinvoiced(): void
    {
        $harvest = $this->makeWineryReception();

        // Cancelled invoice — harvest should be free again
        $cancelled = Invoice::create([
            'user_id' => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'invoice_type' => 'grape_purchase',
            'invoice_number' => 'GP-2024-0001',
            'invoice_date' => '2024-10-01',
            'status' => 'cancelled',
            'payment_status' => 'unpaid',
            'subtotal' => 0,
            'tax_base' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
        ]);
        // withoutEvents: invoice is cancelled, observer would skip anyway, but explicit is clearer
        InvoiceItem::withoutEvents(fn () => InvoiceItem::create([
            'invoice_id' => $cancelled->id,
            'harvest_id' => $harvest->id,
            'concept_type' => 'harvest',
            'name' => 'Test',
            'quantity' => 500,
            'unit_price' => 0.45,
            'tax_rate' => 2,
            'subtotal' => 225,
            'tax_base' => 225,
            'tax_amount' => 4.5,
            'total' => 220.5,
        ]));

        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->viticulturist->id)
            ->set('invoice_date', '2024-10-31')
            ->set('lines', $this->validLines($harvest->id))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('invoices', 2);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function validLines(int $harvestId, string $qty = '500', string $price = '0.450', string $tax = '2'): array
    {
        return [[
            'harvest_id' => $harvestId,
            'quantity' => $qty,
            'unit_price' => $price,
            'tax_rate' => $tax,
            'description' => '',
        ]];
    }
}
