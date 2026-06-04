<?php

namespace Tests\Feature\Winery\Billing\GrapePurchase;

use App\Livewire\Winery\Billing\GrapePurchase\Edit;
use App\Models\Harvest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;
use Tests\Traits\CreatesDeliveryScenario;

class EditTest extends WineryTestCase
{
    use CreatesDeliveryScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
        $this->actingAs($this->winery);
    }

    // ── Happy path ────────────────────────────────────────────────────────────

    public function test_updates_invoice_and_recalculates_totals(): void
    {
        // total_weight=2000 so HarvestStock has enough available to reserve the new qty (1000kg)
        $harvest = $this->makeWineryReception(['total_weight' => 2000]);
        $invoice = $this->makeInvoice();
        $this->attachHarvest($invoice, $harvest);

        // Change to 1000 kg × 0.50 = 500 base; 2% retención = 10; total = 490
        Livewire::test(Edit::class, ['id' => $invoice->id])
            ->set('invoice_date', '2024-11-15')
            ->set('payment_type', 'cash')
            ->set('lines', [[
                'harvest_id' => $harvest->id,
                'quantity' => '1000',
                'unit_price' => '0.500',
                'tax_rate' => '2',
                'description' => '',
            ]])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('winery.invoices.grape-purchase.index'));

        $invoice->refresh();
        $this->assertEquals(500.0, (float) $invoice->subtotal);
        $this->assertEquals(10.0, (float) $invoice->tax_amount);
        $this->assertEquals(490.0, (float) $invoice->total_amount);
        $this->assertEquals('2024-11-15', $invoice->invoice_date->format('Y-m-d'));
    }

    public function test_invoice_number_unchanged_after_edit(): void
    {
        $harvest = $this->makeWineryReception();
        $invoice = $this->makeInvoice(['invoice_number' => 'GP-2024-0001']);
        $this->attachHarvest($invoice, $harvest);

        Livewire::test(Edit::class, ['id' => $invoice->id])
            ->set('invoice_date', '2024-11-01')
            ->set('lines', [[
                'harvest_id' => $harvest->id,
                'quantity' => '500',
                'unit_price' => '0.450',
                'tax_rate' => '2',
                'description' => '',
            ]])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('GP-2024-0001', $invoice->fresh()->invoice_number);
    }

    // ── Lock guards ───────────────────────────────────────────────────────────

    public function test_paid_invoice_cannot_be_edited(): void
    {
        $harvest = $this->makeWineryReception();
        $invoice = $this->makeInvoice(['payment_status' => 'paid']);
        $this->attachHarvest($invoice, $harvest);

        Livewire::test(Edit::class, ['id' => $invoice->id])
            ->set('invoice_date', '2024-12-01')
            ->set('lines', [[
                'harvest_id' => $harvest->id,
                'quantity' => '999',
                'unit_price' => '0.999',
                'tax_rate' => '0',
                'description' => '',
            ]])
            ->call('save');

        // Totals must remain unchanged
        $this->assertEquals(225.0, (float) $invoice->fresh()->subtotal);
    }

    public function test_cancelled_invoice_cannot_be_edited(): void
    {
        $harvest = $this->makeWineryReception();
        $invoice = $this->makeInvoice(['status' => 'cancelled']);
        $this->attachHarvest($invoice, $harvest);

        Livewire::test(Edit::class, ['id' => $invoice->id])
            ->set('invoice_date', '2024-12-01')
            ->set('lines', [[
                'harvest_id' => $harvest->id,
                'quantity' => '999',
                'unit_price' => '0.999',
                'tax_rate' => '0',
                'description' => '',
            ]])
            ->call('save');

        $this->assertEquals(225.0, (float) $invoice->fresh()->subtotal);
    }

    // ── Double-invoicing guard ────────────────────────────────────────────────

    public function test_adding_already_invoiced_harvest_is_rejected(): void
    {
        $harvestA = $this->makeWineryReception(['total_weight' => 500]);
        $harvestB = $this->makeWineryReception(['total_weight' => 300]);

        // Invoice A already invoices harvestB
        $otherInvoice = $this->makeInvoice(['invoice_number' => 'GP-2024-0002']);
        $this->attachHarvest($otherInvoice, $harvestB);

        // Invoice being edited only has harvestA
        $invoice = $this->makeInvoice();
        $this->attachHarvest($invoice, $harvestA);

        // Try to add harvestB (already in otherInvoice) to this invoice
        Livewire::test(Edit::class, ['id' => $invoice->id])
            ->set('invoice_date', '2024-10-31')
            ->set('lines', [
                ['harvest_id' => $harvestA->id, 'quantity' => '500', 'unit_price' => '0.45', 'tax_rate' => '2', 'description' => ''],
                ['harvest_id' => $harvestB->id, 'quantity' => '300', 'unit_price' => '0.45', 'tax_rate' => '2', 'description' => ''],
            ])
            ->call('save');

        // Invoice should still only have harvestA
        $this->assertEquals(1, $invoice->fresh()->items()->count());
    }

    // ── Ownership: other winery cannot load the invoice ───────────────────────

    public function test_other_winery_cannot_access_invoice(): void
    {
        $otherWinery = $this->makeOtherWinery();
        $harvest = $this->makeWineryReception();
        $invoice = $this->makeInvoice();
        $this->attachHarvest($invoice, $harvest);

        $this->actingAs($otherWinery);

        $this->get(route('winery.invoices.grape-purchase.edit', $invoice->id))
            ->assertStatus(404);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeInvoice(array $attrs = []): Invoice
    {
        return Invoice::create(array_merge([
            'user_id' => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'invoice_type' => 'grape_purchase',
            'invoice_number' => 'GP-2024-0001',
            'delivery_note_code' => 'LIQ-2024-0001',
            'invoice_date' => '2024-10-01',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'subtotal' => 225,
            'tax_base' => 225,
            'tax_amount' => 4.5,
            'total_amount' => 220.5,
        ], $attrs));
    }

    private function attachHarvest(Invoice $invoice, Harvest $harvest): InvoiceItem
    {
        // withoutEvents: GrapePurchase tests cover invoice state, not HarvestStock movements.
        return InvoiceItem::withoutEvents(fn () => InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $harvest->id,
            'concept_type' => 'harvest',
            'name' => 'Vendimia test',
            'quantity' => 500,
            'unit_price' => 0.45,
            'tax_rate' => 2,
            'subtotal' => 225,
            'tax_base' => 225,
            'tax_amount' => 4.5,
            'total' => 220.5,
        ]));
    }
}
