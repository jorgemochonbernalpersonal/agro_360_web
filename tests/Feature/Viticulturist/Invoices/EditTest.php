<?php

namespace Tests\Feature\Viticulturist\Invoices;

use App\Livewire\Viticulturist\Invoices\Edit;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Tax;
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

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeClient(): Client
    {
        return Client::create([
            'user_id'      => $this->viticulturist->id,
            'client_type'  => 'company',
            'company_name' => 'Distribuidora Test S.L.',
            'email'        => 'cliente@test.com',
            'active'       => true,
        ]);
    }

    private function makeAddress(Client $client): ClientAddress
    {
        return ClientAddress::create([
            'client_id'  => $client->id,
            'first_name' => 'Test',
            'address'    => 'Calle Viñedo 1',
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

    /** Draft invoice with one 'other' item (no harvest, no stock) */
    private function makeInvoice(array $attrs = []): Invoice
    {
        $client  = $this->makeClient();
        $address = $this->makeAddress($client);
        $tax     = $this->makeTax(21);

        $invoice = Invoice::create(array_merge([
            'user_id'            => $this->viticulturist->id,
            'client_id'          => $client->id,
            'client_address_id'  => $address->id,
            'invoice_type'       => 'wine_sale',
            'delivery_note_code' => 'ALB-2024-0001',
            'invoice_date'       => '2024-10-15',
            'delivery_note_date' => '2024-10-15',
            'order_date'         => '2024-10-15',
            'status'             => 'draft',
            'payment_status'     => 'unpaid',
            'delivery_status'    => 'pending',
            'subtotal'           => 250,
            'discount_amount'    => 0,
            'tax_base'           => 250,
            'tax_amount'         => 52.5,
            'total_amount'       => 302.5,
        ], $attrs));

        InvoiceItem::create([
            'invoice_id'          => $invoice->id,
            'concept_type'        => 'other',
            'name'                => 'Uva Tempranillo',
            'quantity'            => 100,
            'unit'                => 'kg',
            'unit_price'          => 2.5,
            'discount_percentage' => 0,
            'tax_id'              => $tax->id,
            'tax_rate'            => 21,
            'subtotal'            => 250,
            'tax_base'            => 250,
            'tax_amount'          => 52.5,
            'total'               => 302.5,
        ]);

        return $invoice;
    }

    private function updatePayload(Invoice $invoice, Tax $tax, array $override = []): array
    {
        $client  = $invoice->client;
        $address = $client->addresses->first();

        return array_merge([
            'client_id'          => $client->id,
            'client_address_id'  => $address->id,
            'invoice_date'       => '2024-10-20',
            'delivery_note_date' => '2024-10-20',
            'delivery_status'    => 'pending',
            'payment_status'     => 'unpaid',
            'items'              => [[
                'id'                  => null,
                'harvest_id'          => null,
                'name'                => 'Uva Modificada',
                'description'         => '',
                'sku'                 => '',
                'quantity'            => 200,
                'unit'                => 'kg',
                'unit_price'          => 3.0,
                'discount_percentage' => 0,
                'tax_id'              => $tax->id,
                'concept_type'        => 'other',
            ]],
        ], $override);
    }

    // ── Happy path ────────────────────────────────────────────────────────────

    public function test_update_persists_changes_and_recalculates_totals(): void
    {
        // 200 kg × 3.00 €/kg = 600 base; IVA 21% = 126; total 726
        $invoice = $this->makeInvoice();
        $tax     = Tax::find($invoice->items()->value('tax_id'));

        Livewire::test(Edit::class, ['invoice' => $invoice])
            ->set('client_id',          $invoice->client_id)
            ->set('client_address_id',  $invoice->client_address_id)
            ->set('invoice_date',       '2024-10-20')
            ->set('delivery_note_date', '2024-10-20')
            ->set('delivery_status',    'pending')
            ->set('payment_status',     'unpaid')
            ->set('items', [[
                'id'                  => null,
                'harvest_id'          => null,
                'name'                => 'Uva Modificada',
                'description'         => '',
                'sku'                 => '',
                'quantity'            => 200,
                'unit'                => 'kg',
                'unit_price'          => 3.0,
                'discount_percentage' => 0,
                'tax_id'              => $tax->id,
                'concept_type'        => 'other',
            ]])
            ->call('update')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.invoices.index'));

        $fresh = $invoice->fresh();
        $this->assertEquals(600.0, (float) $fresh->subtotal);
        $this->assertEquals(126.0, (float) $fresh->tax_amount);
        $this->assertEquals(726.0, (float) $fresh->total_amount);
        $this->assertEquals(1, $fresh->items()->count());
        $this->assertEquals('Uva Modificada', $fresh->items()->first()->name);
    }

    public function test_update_applies_discount_before_tax(): void
    {
        // 100 × 10 = 1000; 10% disc = 100 off → base 900; IVA 21% = 189; total 1089
        $invoice = $this->makeInvoice();
        $tax     = Tax::find($invoice->items()->value('tax_id'));

        Livewire::test(Edit::class, ['invoice' => $invoice])
            ->set('client_id',          $invoice->client_id)
            ->set('client_address_id',  $invoice->client_address_id)
            ->set('invoice_date',       '2024-10-20')
            ->set('delivery_note_date', '2024-10-20')
            ->set('delivery_status',    'pending')
            ->set('payment_status',     'unpaid')
            ->set('items', [[
                'id'                  => null,
                'harvest_id'          => null,
                'name'                => 'Uva con descuento',
                'description'         => '',
                'sku'                 => '',
                'quantity'            => 100,
                'unit'                => 'kg',
                'unit_price'          => 10.0,
                'discount_percentage' => 10,
                'tax_id'              => $tax->id,
                'concept_type'        => 'other',
            ]])
            ->call('update')
            ->assertHasNoErrors();

        $fresh = $invoice->fresh();
        $this->assertEquals(900.0,  (float) $fresh->subtotal);
        $this->assertEquals(100.0,  (float) $fresh->discount_amount);
        $this->assertEquals(189.0,  (float) $fresh->tax_amount);
        $this->assertEquals(1089.0, (float) $fresh->total_amount);
    }

    public function test_update_without_items_fails_validation(): void
    {
        $invoice = $this->makeInvoice();

        Livewire::test(Edit::class, ['invoice' => $invoice])
            ->set('client_id',          $invoice->client_id)
            ->set('client_address_id',  $invoice->client_address_id)
            ->set('delivery_status',    'pending')
            ->set('payment_status',     'unpaid')
            ->set('items', [])
            ->call('update')
            ->assertHasErrors(['items']);
    }

    // ── Lock guards ───────────────────────────────────────────────────────────

    public function test_locked_delivered_invoice_cannot_be_updated(): void
    {
        $invoice = $this->makeInvoice(['delivery_status' => 'delivered']);
        $tax     = Tax::first();

        Livewire::test(Edit::class, ['invoice' => $invoice])
            ->set('client_id',          $invoice->client_id)
            ->set('client_address_id',  $invoice->client_address_id)
            ->set('delivery_status',    'delivered')
            ->set('payment_status',     'unpaid')
            ->set('items', [[
                'id'                  => null,
                'harvest_id'          => null,
                'name'                => 'Cambio no permitido',
                'description'         => '',
                'sku'                 => '',
                'quantity'            => 999,
                'unit'                => 'kg',
                'unit_price'          => 99.0,
                'discount_percentage' => 0,
                'tax_id'              => $tax->id,
                'concept_type'        => 'other',
            ]])
            ->call('update');

        // Totals must be unchanged
        $this->assertEquals(250.0, (float) $invoice->fresh()->subtotal);
    }

    public function test_locked_cancelled_invoice_cannot_be_updated(): void
    {
        $invoice = $this->makeInvoice(['status' => 'cancelled', 'delivery_status' => 'cancelled']);
        $tax     = Tax::first();

        Livewire::test(Edit::class, ['invoice' => $invoice])
            ->set('client_id',          $invoice->client_id)
            ->set('client_address_id',  $invoice->client_address_id)
            ->set('delivery_status',    'cancelled')
            ->set('payment_status',     'unpaid')
            ->set('items', [[
                'id'                  => null,
                'harvest_id'          => null,
                'name'                => 'Cambio no permitido',
                'description'         => '',
                'sku'                 => '',
                'quantity'            => 999,
                'unit'                => 'kg',
                'unit_price'          => 99.0,
                'discount_percentage' => 0,
                'tax_id'              => $tax->id,
                'concept_type'        => 'other',
            ]])
            ->call('update');

        $this->assertEquals(250.0, (float) $invoice->fresh()->subtotal);
    }

    // ── Authorization ─────────────────────────────────────────────────────────

    public function test_other_viticulturist_invoice_returns_404(): void
    {
        $other = $this->makeOtherViticulturist();
        $otherClient = Client::create([
            'user_id'     => $other->id,
            'client_type' => 'individual',
            'first_name'  => 'Otro',
            'active'      => true,
        ]);
        $otherInvoice = Invoice::create([
            'user_id'            => $other->id,
            'client_id'          => $otherClient->id,
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

        // HTTP-level check: the route enforces user scope via `Invoice::forUser()`
        $this->get(route('viticulturist.invoices.edit', $otherInvoice->id))
            ->assertStatus(404);
    }

    // ── markAsSent ────────────────────────────────────────────────────────────

    public function test_mark_as_sent_generates_invoice_number(): void
    {
        $invoice = $this->makeInvoice();

        Livewire::test(Edit::class, ['invoice' => $invoice])
            ->set('invoice_date_modal', '2024-10-31')
            ->call('markAsSent')
            ->assertHasNoErrors();

        $fresh = $invoice->fresh();
        $this->assertEquals('sent', $fresh->status);
        $this->assertNotNull($fresh->invoice_number);
        $this->assertNotEmpty($fresh->invoice_number);
    }

    public function test_open_invoice_modal_blocked_for_already_sent_invoice(): void
    {
        $invoice = $this->makeInvoice(['status' => 'sent', 'invoice_number' => 'FAC-2024-0001']);

        // openInvoiceModal() guards against non-draft invoices; modal must NOT open
        Livewire::test(Edit::class, ['invoice' => $invoice])
            ->call('openInvoiceModal')
            ->assertSet('showInvoiceModal', false);

        // Invoice number must be unchanged
        $this->assertEquals('FAC-2024-0001', $invoice->fresh()->invoice_number);
    }
}
