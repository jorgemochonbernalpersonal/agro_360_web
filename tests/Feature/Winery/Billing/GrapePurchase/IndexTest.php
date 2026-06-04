<?php

namespace Tests\Feature\Winery\Billing\GrapePurchase;

use App\Livewire\Winery\Billing\GrapePurchase\Index;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;
use Tests\Traits\CreatesDeliveryScenario;

class IndexTest extends WineryTestCase
{
    use CreatesDeliveryScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
        $this->actingAs($this->winery);
    }

    // ── Visibility ────────────────────────────────────────────────────────────

    public function test_shows_own_invoices_only(): void
    {
        $own = $this->makeInvoice(['invoice_number' => 'GP-2024-0001']);
        $other = Invoice::create([
            'user_id' => $this->makeOtherWinery()->id,
            'invoice_type' => 'grape_purchase',
            'invoice_number' => 'GP-2024-0099',
            'invoice_date' => '2024-10-01',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'subtotal' => 0,
            'tax_base' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
        ]);

        Livewire::test(Index::class)
            ->assertSee('GP-2024-0001')
            ->assertDontSee('GP-2024-0099');
    }

    public function test_search_filters_by_invoice_number(): void
    {
        $this->makeInvoice(['invoice_number' => 'GP-2024-0001']);
        $this->makeInvoice(['invoice_number' => 'GP-2024-0002']);

        Livewire::test(Index::class)
            ->set('search', 'GP-2024-0001')
            ->assertSee('GP-2024-0001')
            ->assertDontSee('GP-2024-0002');
    }

    // ── markPaid ──────────────────────────────────────────────────────────────

    public function test_mark_paid_sets_payment_status(): void
    {
        $invoice = $this->makeInvoice();

        Livewire::test(Index::class)
            ->call('markPaid', $invoice->id);

        $this->assertEquals('paid', $invoice->fresh()->payment_status);
    }

    public function test_mark_paid_on_cancelled_invoice_does_nothing(): void
    {
        $invoice = $this->makeInvoice(['status' => 'cancelled']);

        Livewire::test(Index::class)
            ->call('markPaid', $invoice->id);

        $this->assertEquals('unpaid', $invoice->fresh()->payment_status);
    }

    public function test_mark_paid_on_other_winerys_invoice_does_nothing(): void
    {
        $otherWinery = $this->makeOtherWinery();
        $foreign = Invoice::create([
            'user_id' => $otherWinery->id,
            'invoice_type' => 'grape_purchase',
            'invoice_number' => 'GP-2024-0099',
            'invoice_date' => '2024-10-01',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'subtotal' => 0,
            'tax_base' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
        ]);

        Livewire::test(Index::class)
            ->call('markPaid', $foreign->id);

        $this->assertEquals('unpaid', $foreign->fresh()->payment_status);
    }

    // ── cancel ────────────────────────────────────────────────────────────────

    public function test_cancel_sets_status_cancelled(): void
    {
        $invoice = $this->makeInvoice();
        $item = $this->attachHarvest($invoice);

        Livewire::test(Index::class)
            ->call('cancel', $invoice->id);

        $fresh = $invoice->fresh();
        $this->assertEquals('cancelled', $fresh->status);
        // New flow: delivery_status must also be set to cancelled
        // so InvoiceObserver::restoreDeliveryStock() is triggered
        $this->assertEquals('cancelled', $fresh->delivery_status);
    }

    public function test_cancel_frees_harvest_for_new_invoice(): void
    {
        $invoice = $this->makeInvoice();
        $item = $this->attachHarvest($invoice);
        $harvestId = $item->harvest_id;

        Livewire::test(Index::class)
            ->call('cancel', $invoice->id);

        // The harvest should no longer be blocked by an active invoice
        $stillBlocked = InvoiceItem::where('harvest_id', $harvestId)
            ->where('concept_type', 'harvest')
            ->whereHas('invoice', fn ($q) => $q->where('status', '!=', 'cancelled'))
            ->exists();

        $this->assertFalse($stillBlocked);
    }

    public function test_cancel_paid_invoice_is_rejected(): void
    {
        $invoice = $this->makeInvoice(['payment_status' => 'paid']);

        Livewire::test(Index::class)
            ->call('cancel', $invoice->id);

        $this->assertEquals('draft', $invoice->fresh()->status);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeInvoice(array $attrs = []): Invoice
    {
        return Invoice::create(array_merge([
            'user_id' => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'invoice_type' => 'grape_purchase',
            'invoice_number' => 'GP-2024-'.rand(1, 9999),
            'invoice_date' => '2024-10-01',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'subtotal' => 225,
            'tax_base' => 225,
            'tax_amount' => 4.5,
            'total_amount' => 220.5,
        ], $attrs));
    }

    private function attachHarvest(Invoice $invoice): InvoiceItem
    {
        $harvest = $this->makeWineryReception();

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
