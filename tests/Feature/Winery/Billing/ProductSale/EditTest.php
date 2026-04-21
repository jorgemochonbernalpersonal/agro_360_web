<?php

namespace Tests\Feature\Winery\Billing\ProductSale;

use App\Livewire\Winery\Billing\ProductSale\Edit;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ProductLot;
use App\Models\Tax;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class EditTest extends WineryTestCase
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

    private function makeTax(float $rate = 21): Tax
    {
        return Tax::create([
            'name'       => "IVA {$rate}%",
            'code'       => 'IVA',
            'rate'       => $rate,
            'active'     => true,
            'is_default' => false,
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

    /** Draft invoice with 6 bottles reserved from $lot */
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

        InvoiceItem::create([
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

        // Simulate reservation
        $lot->decrement('available_quantity', 6);
        $lot->increment('reserved_quantity', 6);

        return $invoice;
    }

    private function itemsPayload(ProductLot $lot, Tax $tax, float $qty = 6): array
    {
        return [[
            'wine_lot_id'         => $lot->id,
            'name'                => 'Rioja Reserva 2020',
            'description'         => '',
            'sku'                 => '',
            'quantity'            => $qty,
            'available_qty'       => (float) $lot->available_quantity + 6, // add back the 6 already reserved
            'unit_price'          => 12.50,
            'discount_percentage' => 0,
            'tax_id'              => $tax->id,
            'concept_type'        => 'wine',
        ]];
    }

    // ── Tests ─────────────────────────────────────────────────────────────────

    public function test_save_recalculates_totals_and_adjusts_stock(): void
    {
        // Start with lot 500 available; makeInvoice reserves 6 → 494 available, 6 reserved
        $lot     = $this->makeLot(500);
        $invoice = $this->makeInvoice($lot);
        $tax     = $this->makeTax(21);
        $client  = Client::where('user_id', $this->winery->id)->first();

        // Edit: change quantity from 6 to 10 bottles
        Livewire::test(Edit::class, ['id' => $invoice->id])
            ->set('client_id', (string) $client->id)
            ->set('items', [[
                'wine_lot_id'         => $lot->id,
                'name'                => 'Rioja Reserva 2020',
                'description'         => '',
                'sku'                 => '',
                'quantity'            => 10,
                'available_qty'       => 500,
                'unit_price'          => 12.50,
                'discount_percentage' => 0,
                'tax_id'              => $tax->id,
                'concept_type'        => 'wine',
            ]])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('winery.invoices.products.index'));

        // 10 × 12.50 = 125 base; 21% = 26.25; total = 151.25
        $fresh = $invoice->fresh();
        $this->assertEquals(125.0,  (float) $fresh->subtotal);
        $this->assertEquals(26.25,  (float) $fresh->tax_amount);
        $this->assertEquals(151.25, (float) $fresh->total_amount);

        // Stock: old reservation released (6), new reservation created (10)
        // Net: available = 500 - 10 = 490, reserved = 10
        $this->assertEquals(490, (float) $lot->fresh()->available_quantity);
        $this->assertEquals(10,  (float) $lot->fresh()->reserved_quantity);
    }

    public function test_invoice_number_immutable_after_save(): void
    {
        $lot     = $this->makeLot(500);
        $invoice = $this->makeInvoice($lot, ['invoice_number' => 'FAC-2024-0001', 'status' => 'sent']);
        $tax     = $this->makeTax(21);
        $client  = Client::where('user_id', $this->winery->id)->first();

        Livewire::test(Edit::class, ['id' => $invoice->id])
            ->set('client_id', (string) $client->id)
            ->set('items', [[
                'wine_lot_id'         => $lot->id,
                'name'                => 'Rioja Reserva 2020',
                'description'         => '',
                'sku'                 => '',
                'quantity'            => 6,
                'available_qty'       => 500,
                'unit_price'          => 15.00,
                'discount_percentage' => 0,
                'tax_id'              => $tax->id,
                'concept_type'        => 'wine',
            ]])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('FAC-2024-0001', $invoice->fresh()->invoice_number);
    }

    public function test_locked_delivered_invoice_cannot_be_saved(): void
    {
        $lot     = $this->makeLot(494, 6);
        $invoice = $this->makeInvoice($lot, ['delivery_status' => 'delivered']);
        $tax     = $this->makeTax(21);
        $client  = Client::where('user_id', $this->winery->id)->first();

        Livewire::test(Edit::class, ['id' => $invoice->id])
            ->set('client_id', (string) $client->id)
            ->set('items', [[
                'wine_lot_id'         => $lot->id,
                'name'                => 'Rioja Reserva 2020',
                'description'         => '',
                'sku'                 => '',
                'quantity'            => 99,
                'available_qty'       => 500,
                'unit_price'          => 99.00,
                'discount_percentage' => 0,
                'tax_id'              => $tax->id,
                'concept_type'        => 'wine',
            ]])
            ->call('save');

        // Totals must be unchanged (locked invoice not saved)
        $this->assertEquals(75.0, (float) $invoice->fresh()->subtotal);
    }

    public function test_locked_cancelled_invoice_cannot_be_saved(): void
    {
        $lot     = $this->makeLot(494, 6);
        $invoice = $this->makeInvoice($lot, ['status' => 'cancelled', 'delivery_status' => 'cancelled']);
        $tax     = $this->makeTax(21);
        $client  = Client::where('user_id', $this->winery->id)->first();

        Livewire::test(Edit::class, ['id' => $invoice->id])
            ->set('client_id', (string) $client->id)
            ->set('items', [[
                'wine_lot_id'         => $lot->id,
                'name'                => 'test',
                'description'         => '',
                'sku'                 => '',
                'quantity'            => 99,
                'available_qty'       => 500,
                'unit_price'          => 99.00,
                'discount_percentage' => 0,
                'tax_id'              => $tax->id,
                'concept_type'        => 'wine',
            ]])
            ->call('save');

        $this->assertEquals(75.0, (float) $invoice->fresh()->subtotal);
    }

    public function test_lot_from_other_winery_is_rejected(): void
    {
        $lot         = $this->makeLot(500);
        $invoice     = $this->makeInvoice($lot);
        $tax         = $this->makeTax(21);
        $client      = Client::where('user_id', $this->winery->id)->first();
        $otherWinery = $this->makeOtherWinery();
        $foreignLot  = ProductLot::create([
            'user_id'            => $otherWinery->id,
            'name'               => 'Lote Ajeno',
            'available_quantity' => 100,
            'reserved_quantity'  => 0,
            'sold_quantity'      => 0,
            'unit'               => 'botellas',
            'archived'           => false,
        ]);

        Livewire::test(Edit::class, ['id' => $invoice->id])
            ->set('client_id', (string) $client->id)
            ->set('items', [[
                'wine_lot_id'         => $foreignLot->id,
                'name'                => 'Lote Ajeno',
                'description'         => '',
                'sku'                 => '',
                'quantity'            => 6,
                'available_qty'       => 100,
                'unit_price'          => 12.50,
                'discount_percentage' => 0,
                'tax_id'              => $tax->id,
                'concept_type'        => 'wine',
            ]])
            ->call('save')
            ->assertHasErrors(['items.0.wine_lot_id']);
    }

    public function test_other_winery_invoice_returns_404(): void
    {
        $otherWinery = $this->makeOtherWinery();
        $lot         = $this->makeLot(500);
        $otherInvoice = Invoice::create([
            'user_id'            => $otherWinery->id,
            'invoice_type'       => 'wine_sale',
            'delivery_note_code' => 'ALB-9999-0001',
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

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Edit::class, ['id' => $otherInvoice->id]);
    }

    // ── Stock transitions ─────────────────────────────────────────────────────

    public function test_adding_line_to_draft_reserves_new_lot(): void
    {
        // Start with full lots; makeInvoice will decrement lotA by 6 → available=494, reserved=6
        $lotA    = $this->makeLot(500);
        $lotB    = $this->makeLot(100);
        $invoice = $this->makeInvoice($lotA);
        $tax     = $this->makeTax(21);
        $client  = Client::where('user_id', $this->winery->id)->first();

        // Save the invoice with original line (lotA, 6) PLUS a new line (lotB, 4)
        Livewire::test(Edit::class, ['id' => $invoice->id])
            ->set('client_id', (string) $client->id)
            ->set('items', [
                [
                    'wine_lot_id'         => $lotA->id,
                    'name'                => 'Rioja Reserva 2020',
                    'description'         => '',
                    'sku'                 => '',
                    'quantity'            => 6,
                    'available_qty'       => 500,
                    'unit_price'          => 12.50,
                    'discount_percentage' => 0,
                    'tax_id'              => $tax->id,
                    'concept_type'        => 'wine',
                ],
                [
                    'wine_lot_id'         => $lotB->id,
                    'name'                => 'Otro Vino 2021',
                    'description'         => '',
                    'sku'                 => '',
                    'quantity'            => 4,
                    'available_qty'       => 100,
                    'unit_price'          => 10.00,
                    'discount_percentage' => 0,
                    'tax_id'              => $tax->id,
                    'concept_type'        => 'wine',
                ],
            ])
            ->call('save')
            ->assertHasNoErrors();

        // lotA unchanged (6 reserved → cancelled → re-reserved)
        $this->assertEquals(494, (float) $lotA->fresh()->available_quantity);
        $this->assertEquals(6,   (float) $lotA->fresh()->reserved_quantity);

        // lotB: 4 bottles now reserved
        $this->assertEquals(96, (float) $lotB->fresh()->available_quantity);
        $this->assertEquals(4,  (float) $lotB->fresh()->reserved_quantity);
    }

    public function test_removing_line_from_draft_returns_stock_to_available(): void
    {
        // lotA: 6 reserved by invoice; lotB: also 8 reserved by same invoice
        $lotA = $this->makeLot(494, 6);
        $lotB = $this->makeLot(92, 8);

        $client  = $this->makeClient();
        $address = $this->makeAddress($client);
        $invoice = Invoice::create([
            'user_id'            => $this->winery->id,
            'client_id'          => $client->id,
            'invoice_type'       => 'wine_sale',
            'delivery_note_code' => 'ALB-2024-0001',
            'order_date'         => '2024-10-01',
            'status'             => 'draft',
            'payment_status'     => 'unpaid',
            'delivery_status'    => 'pending',
            'subtotal'           => 155,
            'discount_amount'    => 0,
            'tax_base'           => 155,
            'tax_amount'         => 32.55,
            'total_amount'       => 187.55,
        ]);
        InvoiceItem::create([
            'invoice_id'   => $invoice->id,
            'wine_lot_id'  => $lotA->id,
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
        InvoiceItem::create([
            'invoice_id'   => $invoice->id,
            'wine_lot_id'  => $lotB->id,
            'concept_type' => 'wine',
            'name'         => 'Otro Vino 2021',
            'quantity'     => 8,
            'unit_price'   => 10.00,
            'tax_rate'     => 21,
            'subtotal'     => 80,
            'tax_base'     => 80,
            'tax_amount'   => 16.80,
            'total'        => 96.80,
        ]);

        $tax = $this->makeTax(21);

        // Save keeping only lotA (remove lotB line)
        Livewire::test(Edit::class, ['id' => $invoice->id])
            ->set('client_id', (string) $client->id)
            ->set('items', [[
                'wine_lot_id'         => $lotA->id,
                'name'                => 'Rioja Reserva 2020',
                'description'         => '',
                'sku'                 => '',
                'quantity'            => 6,
                'available_qty'       => 500,
                'unit_price'          => 12.50,
                'discount_percentage' => 0,
                'tax_id'              => $tax->id,
                'concept_type'        => 'wine',
            ]])
            ->call('save')
            ->assertHasNoErrors();

        // lotA: still reserved (6)
        $this->assertEquals(494, (float) $lotA->fresh()->available_quantity);
        $this->assertEquals(6,   (float) $lotA->fresh()->reserved_quantity);

        // lotB: 8 bottles returned to available, nothing reserved
        $this->assertEquals(100, (float) $lotB->fresh()->available_quantity);
        $this->assertEquals(0,   (float) $lotB->fresh()->reserved_quantity);
    }

    public function test_confirm_delivery_moves_reserved_to_sold(): void
    {
        // makeLot(500) + makeInvoice → available=494, reserved=6
        $lot     = $this->makeLot(500);
        $invoice = $this->makeInvoice($lot);

        Livewire::test(Edit::class, ['id' => $invoice->id])
            ->set('pendingDeliveryStatus', 'delivered')
            ->call('confirmDeliveryStatus')
            ->assertHasNoErrors();

        // reserved→sold: reserved back to 0, sold = 6
        $this->assertEquals(494, (float) $lot->fresh()->available_quantity);
        $this->assertEquals(0,   (float) $lot->fresh()->reserved_quantity);
        $this->assertEquals(6,   (float) $lot->fresh()->sold_quantity);

        $this->assertEquals('delivered', $invoice->fresh()->delivery_status);
    }

    public function test_confirm_cancel_moves_reserved_back_to_available(): void
    {
        // makeLot(500) + makeInvoice → available=494, reserved=6
        $lot     = $this->makeLot(500);
        $invoice = $this->makeInvoice($lot);

        Livewire::test(Edit::class, ['id' => $invoice->id])
            ->set('pendingDeliveryStatus', 'cancelled')
            ->call('confirmDeliveryStatus')
            ->assertHasNoErrors();

        // reserved→available: back to full 500, nothing reserved
        $this->assertEquals(500, (float) $lot->fresh()->available_quantity);
        $this->assertEquals(0,   (float) $lot->fresh()->reserved_quantity);

        $this->assertEquals('cancelled', $invoice->fresh()->delivery_status);
    }

}
