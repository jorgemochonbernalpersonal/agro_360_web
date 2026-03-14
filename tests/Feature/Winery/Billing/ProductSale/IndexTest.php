<?php

namespace Tests\Feature\Winery\Billing\ProductSale;

use App\Livewire\Winery\Billing\ProductSale\Index;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ProductLot;
use App\Models\Tax;
use App\Models\User;
use App\Services\ProductStockService;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class IndexTest extends WineryTestCase
{
    private User $winery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeClient(): Client
    {
        return Client::create([
            'user_id'      => $this->winery->id,
            'client_type'  => 'company',
            'company_name' => 'Restaurante Test S.L.',
            'email'        => 'cliente@test.com',
            'active'       => true,
        ]);
    }

    private function makeAddress(Client $client): ClientAddress
    {
        return ClientAddress::create([
            'client_id'  => $client->id,
            'first_name' => 'Test',
            'address'    => 'Calle Bodega 1',
            'is_default' => true,
        ]);
    }

    private function makeLot(int $available = 500, int $reserved = 0): ProductLot
    {
        return ProductLot::create([
            'user_id'            => $this->winery->id,
            'name'               => 'Rioja Reserva 2020',
            'available_quantity' => $available,
            'reserved_quantity'  => $reserved,
            'sold_quantity'      => 0,
            'unit'               => 'botellas',
            'price_per_unit'     => 12.50,
            'archived'           => false,
        ]);
    }

    /** Creates a draft wine_sale invoice with one item linked to $lot */
    private function makeInvoice(ProductLot $lot, array $attrs = []): Invoice
    {
        $client  = $this->makeClient();
        $address = $this->makeAddress($client);

        $invoice = Invoice::create(array_merge([
            'user_id'              => $this->winery->id,
            'client_id'            => $client->id,
            'invoice_type'         => 'wine_sale',
            'delivery_note_code'   => 'ALB-2024-0001',
            'order_date'           => '2024-10-01',
            'status'               => 'draft',
            'payment_status'       => 'unpaid',
            'delivery_status'      => 'pending',
            'subtotal'             => 75,
            'discount_amount'      => 0,
            'tax_base'             => 75,
            'tax_amount'           => 15.75,
            'total_amount'         => 90.75,
        ], $attrs));

        $item = InvoiceItem::create([
            'invoice_id'   => $invoice->id,
            'wine_lot_id'  => $lot->id,
            'concept_type' => 'wine',
            'name'         => 'Rioja Reserva 2020',
            'quantity'     => 6,
            'unit_price'   => 12.50,
            'tax_rate'     => 21,
            'subtotal'     => 75,
            'tax_base'     => 75,
            'tax_amount'   => 15.75,
            'total'        => 90.75,
        ]);

        // Simulate stock reservation
        $lot->decrement('available_quantity', 6);
        $lot->increment('reserved_quantity', 6);

        return $invoice;
    }

    // ── Visibility ────────────────────────────────────────────────────────────

    public function test_shows_own_invoices_only(): void
    {
        $lot   = $this->makeLot();
        $own   = $this->makeInvoice($lot, ['delivery_note_code' => 'ALB-2024-0001']);

        $other = Invoice::create([
            'user_id'            => $this->makeOtherWinery()->id,
            'invoice_type'       => 'wine_sale',
            'delivery_note_code' => 'ALB-2024-0099',
            'order_date'         => '2024-10-01',
            'status'             => 'draft',
            'payment_status'     => 'unpaid',
            'delivery_status'    => 'pending',
            'subtotal'           => 0,
            'discount_amount'    => 0,
            'tax_base'           => 0,
            'tax_amount'         => 0,
            'total_amount'       => 0,
        ]);

        Livewire::test(Index::class)
            ->assertSee('ALB-2024-0001')
            ->assertDontSee('ALB-2024-0099');
    }

    public function test_search_filters_by_delivery_note_code(): void
    {
        $lotA = $this->makeLot();
        $lotB = $this->makeLot();
        $this->makeInvoice($lotA, ['delivery_note_code' => 'ALB-2024-0001']);
        $this->makeInvoice($lotB, ['delivery_note_code' => 'ALB-2024-0002']);

        Livewire::test(Index::class)
            ->set('search', 'ALB-2024-0001')
            ->assertSee('ALB-2024-0001')
            ->assertDontSee('ALB-2024-0002');
    }

    // ── Emitir ────────────────────────────────────────────────────────────────

    public function test_emitir_generates_invoice_number(): void
    {
        $lot     = $this->makeLot();
        $invoice = $this->makeInvoice($lot);

        Livewire::test(Index::class)
            ->set('emitirId', $invoice->id)
            ->set('emitirDate', '2024-10-31')
            ->call('confirmEmitir');

        $fresh = $invoice->fresh();
        $this->assertEquals('sent', $fresh->status);
        $this->assertNotNull($fresh->invoice_number);
        $this->assertNotEmpty($fresh->invoice_number);
    }

    public function test_emitir_already_sent_invoice_does_nothing(): void
    {
        $lot     = $this->makeLot();
        $invoice = $this->makeInvoice($lot, [
            'status'         => 'sent',
            'invoice_number' => 'FAC-2024-0001',
        ]);

        Livewire::test(Index::class)
            ->set('emitirId', $invoice->id)
            ->set('emitirDate', '2024-10-31')
            ->call('confirmEmitir');

        // Number must remain unchanged
        $this->assertEquals('FAC-2024-0001', $invoice->fresh()->invoice_number);
    }

    // ── markDelivered ─────────────────────────────────────────────────────────

    public function test_mark_delivered_moves_stock_to_sold(): void
    {
        $lot     = $this->makeLot(500); // makeInvoice will reserve 6 → available=494, reserved=6
        $invoice = $this->makeInvoice($lot);

        Livewire::test(Index::class)
            ->call('markDelivered', $invoice->id);

        $this->assertEquals('delivered', $invoice->fresh()->delivery_status);
        $this->assertEquals(0,  (float) $lot->fresh()->reserved_quantity);
        $this->assertEquals(6,  (float) $lot->fresh()->sold_quantity);
    }

    public function test_mark_delivered_cancelled_invoice_does_nothing(): void
    {
        $lot     = $this->makeLot(494, 6);
        $invoice = $this->makeInvoice($lot, ['status' => 'cancelled', 'delivery_status' => 'cancelled']);

        Livewire::test(Index::class)
            ->call('markDelivered', $invoice->id);

        $this->assertEquals('cancelled', $invoice->fresh()->delivery_status);
    }

    // ── Cancel ────────────────────────────────────────────────────────────────

    public function test_cancel_restores_reserved_stock(): void
    {
        $lot     = $this->makeLot(500); // makeInvoice will reserve 6 → available=494, reserved=6
        $invoice = $this->makeInvoice($lot);

        Livewire::test(Index::class)
            ->call('cancel', $invoice->id);

        $this->assertEquals('cancelled', $invoice->fresh()->status);
        $this->assertEquals(500, (float) $lot->fresh()->available_quantity);
        $this->assertEquals(0,   (float) $lot->fresh()->reserved_quantity);
    }

    public function test_cancel_paid_invoice_is_rejected(): void
    {
        $lot     = $this->makeLot(494, 6);
        $invoice = $this->makeInvoice($lot, ['payment_status' => 'paid']);

        Livewire::test(Index::class)
            ->call('cancel', $invoice->id);

        $this->assertEquals('draft', $invoice->fresh()->status);
    }

    public function test_cancel_other_winerys_invoice_does_nothing(): void
    {
        $otherWinery = $this->makeOtherWinery();
        $foreign     = Invoice::create([
            'user_id'            => $otherWinery->id,
            'invoice_type'       => 'wine_sale',
            'delivery_note_code' => 'ALB-2024-0099',
            'order_date'         => '2024-10-01',
            'status'             => 'draft',
            'payment_status'     => 'unpaid',
            'delivery_status'    => 'pending',
            'subtotal'           => 0,
            'discount_amount'    => 0,
            'tax_base'           => 0,
            'tax_amount'         => 0,
            'total_amount'       => 0,
        ]);

        Livewire::test(Index::class)
            ->call('cancel', $foreign->id);

        $this->assertEquals('draft', $foreign->fresh()->status);
    }
}
