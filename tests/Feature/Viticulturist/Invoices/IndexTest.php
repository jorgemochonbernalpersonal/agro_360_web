<?php

namespace Tests\Feature\Viticulturist\Invoices;

use App\Livewire\Viticulturist\Invoices\Index;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Invoice;
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

    private function makeClient(): Client
    {
        return Client::create([
            'user_id'      => $this->viticulturist->id,
            'client_type'  => 'individual',
            'first_name'   => 'Cliente',
            'last_name'    => 'Test',
            'active'       => true,
        ]);
    }

    private function makeAddress(Client $client): ClientAddress
    {
        return ClientAddress::create([
            'client_id'  => $client->id,
            'first_name' => 'Test',
            'address'    => 'Calle Test 1',
            'is_default' => true,
        ]);
    }

    private function makeInvoice(array $attrs = []): Invoice
    {
        $client  = $this->makeClient();
        $address = $this->makeAddress($client);

        return Invoice::create(array_merge([
            'user_id'            => $this->viticulturist->id,
            'client_id'          => $client->id,
            'client_address_id'  => $address->id,
            'invoice_type'       => 'wine_sale',
            'delivery_note_code' => 'ALB-2024-0001',
            'order_date'         => '2024-10-01',
            'status'             => 'draft',
            'payment_status'     => 'unpaid',
            'delivery_status'    => 'pending',
            'subtotal'           => 250,
            'discount_amount'    => 0,
            'tax_base'           => 250,
            'tax_amount'         => 52.5,
            'total_amount'       => 302.5,
        ], $attrs));
    }

    // ── Visibility ────────────────────────────────────────────────────────────

    public function test_shows_own_invoices_only(): void
    {
        $own   = $this->makeInvoice(['delivery_note_code' => 'ALB-2024-0001']);
        $other = $this->makeOtherViticulturist();
        Invoice::create([
            'user_id'            => $other->id,
            'invoice_type'       => 'wine_sale',
            'delivery_note_code' => 'ALB-2024-9999',
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
            ->assertDontSee('ALB-2024-9999');
    }

    public function test_search_filters_by_delivery_note_code(): void
    {
        $this->makeInvoice(['delivery_note_code' => 'ALB-2024-0001']);
        $this->makeInvoice(['delivery_note_code' => 'ALB-2024-0002']);

        Livewire::test(Index::class)
            ->set('search', 'ALB-2024-0001')
            ->assertSee('ALB-2024-0001')
            ->assertDontSee('ALB-2024-0002');
    }

    // ── Emitir (draft → sent) ─────────────────────────────────────────────────

    public function test_confirm_emitir_generates_invoice_number(): void
    {
        $invoice = $this->makeInvoice();

        Livewire::test(Index::class)
            ->set('emitirInvoiceId', $invoice->id)
            ->set('emitirDate', '2024-10-31')
            ->call('confirmEmitir')
            ->assertHasNoErrors();

        $fresh = $invoice->fresh();
        $this->assertEquals('sent', $fresh->status);
        $this->assertNotNull($fresh->invoice_number);
        $this->assertStringStartsWith('FAC-', $fresh->invoice_number);
    }

    public function test_confirm_emitir_already_sent_does_nothing(): void
    {
        $invoice = $this->makeInvoice([
            'status'         => 'sent',
            'invoice_number' => 'FAC-2024-0001',
        ]);

        Livewire::test(Index::class)
            ->set('emitirInvoiceId', $invoice->id)
            ->set('emitirDate', '2024-10-31')
            ->call('confirmEmitir');

        $this->assertEquals('FAC-2024-0001', $invoice->fresh()->invoice_number);
    }

    // ── Factura Rectificativa ─────────────────────────────────────────────────

    public function test_confirm_corrective_creates_negative_invoice(): void
    {
        $invoice = $this->makeInvoice([
            'status'         => 'sent',
            'invoice_number' => 'FAC-2024-0001',
            'subtotal'       => 302.5,
            'tax_amount'     => 63.525,
            'total_amount'   => 366.025,
        ]);

        Livewire::test(Index::class)
            ->set('correctiveId',     $invoice->id)
            ->set('correctiveDate',   '2024-11-01')
            ->set('correctiveReason', 'Error en la factura')
            ->call('confirmCorrective')
            ->assertHasNoErrors();

        $corrective = Invoice::where('corrected_invoice_id', $invoice->id)->first();
        $this->assertNotNull($corrective);
        $this->assertTrue((bool) $corrective->corrective);
        $this->assertEquals('sent', $corrective->status);
        $this->assertLessThan(0, (float) $corrective->total_amount);
        $this->assertNotNull($corrective->invoice_number);
    }

    public function test_corrective_only_allowed_for_sent_invoices(): void
    {
        $draft = $this->makeInvoice(['status' => 'draft']);

        Livewire::test(Index::class)
            ->set('correctiveId',   $draft->id)
            ->set('correctiveDate', '2024-11-01')
            ->call('confirmCorrective');

        $this->assertDatabaseMissing('invoices', ['corrected_invoice_id' => $draft->id]);
    }

    public function test_corrective_cannot_be_created_twice_for_same_invoice(): void
    {
        $invoice = $this->makeInvoice([
            'status'         => 'sent',
            'invoice_number' => 'FAC-2024-0001',
        ]);

        // Create first corrective
        Invoice::withoutEvents(fn () => Invoice::create([
            'user_id'              => $this->viticulturist->id,
            'client_id'            => $invoice->client_id,
            'corrected_invoice_id' => $invoice->id,
            'corrective'           => true,
            'invoice_type'         => 'wine_sale',
            'invoice_number'       => 'FAC-2024-0002',
            'order_date'           => now(),
            'status'               => 'sent',
            'payment_status'       => 'unpaid',
            'delivery_status'      => 'cancelled',
            'subtotal'             => -302.5,
            'discount_amount'      => 0,
            'tax_base'             => -302.5,
            'tax_amount'           => -63.525,
            'total_amount'         => -366.025,
        ]));

        Livewire::test(Index::class)
            ->set('correctiveId',   $invoice->id)
            ->set('correctiveDate', '2024-11-15')
            ->call('confirmCorrective');

        // Only the pre-existing corrective should exist
        $this->assertEquals(1, Invoice::where('corrected_invoice_id', $invoice->id)->count());
    }
}
