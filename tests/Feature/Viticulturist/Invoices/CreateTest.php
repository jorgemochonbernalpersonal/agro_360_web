<?php

namespace Tests\Feature\Viticulturist\Invoices;

use App\Livewire\Viticulturist\Invoices\Create;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Invoice;
use App\Models\Tax;
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

    private function itemPayload(Tax $tax, float $qty = 100, float $price = 2.5): array
    {
        return [[
            'harvest_id'          => null,
            'name'                => 'Uva Tempranillo',
            'description'         => '',
            'sku'                 => '',
            'quantity'            => $qty,
            'unit'                => 'kg',
            'unit_price'          => $price,
            'discount_percentage' => 0,
            'tax_id'              => $tax->id,
            'concept_type'        => 'other',
        ]];
    }

    // ── Happy path ────────────────────────────────────────────────────────────

    public function test_creates_invoice_with_manual_item(): void
    {
        $client  = $this->makeClient();
        $address = $this->makeAddress($client);
        $tax     = $this->makeTax(21);

        Livewire::test(Create::class)
            ->set('client_id', $client->id)
            ->set('client_address_id', $address->id)
            ->set('invoice_date', '2024-10-15')
            ->set('delivery_note_date', '2024-10-15')
            ->set('items', $this->itemPayload($tax))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.invoices.index'));

        $invoice = Invoice::where('user_id', $this->viticulturist->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('draft',   $invoice->status);
        $this->assertEquals('unpaid',  $invoice->payment_status);
        $this->assertEquals(1,         $invoice->items()->count());
        $this->assertNotEmpty($invoice->delivery_note_code);
    }

    public function test_totals_calculated_correctly(): void
    {
        // 100 kg × 2.50 €/kg = 250 € base; IVA 21% = 52.50 €; total = 302.50 €
        $client  = $this->makeClient();
        $address = $this->makeAddress($client);
        $tax     = $this->makeTax(21);

        Livewire::test(Create::class)
            ->set('client_id', $client->id)
            ->set('client_address_id', $address->id)
            ->set('invoice_date', '2024-10-15')
            ->set('delivery_note_date', '2024-10-15')
            ->set('items', $this->itemPayload($tax, 100, 2.5))
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::where('user_id', $this->viticulturist->id)->first();
        $this->assertEquals(250.0,  (float) $invoice->subtotal);
        $this->assertEquals(52.5,   (float) $invoice->tax_amount);
        $this->assertEquals(302.5,  (float) $invoice->total_amount);
    }

    public function test_discount_applied_before_tax(): void
    {
        // 100 × 10 = 1000; 10% disc = 100 off → base 900; IVA 21% = 189; total 1089
        $client  = $this->makeClient();
        $address = $this->makeAddress($client);
        $tax     = $this->makeTax(21);

        Livewire::test(Create::class)
            ->set('client_id', $client->id)
            ->set('client_address_id', $address->id)
            ->set('invoice_date', '2024-10-15')
            ->set('delivery_note_date', '2024-10-15')
            ->set('items', [[
                'harvest_id'          => null,
                'name'                => 'Uva',
                'description'         => '',
                'sku'                 => '',
                'quantity'            => 100,
                'unit'                => 'kg',
                'unit_price'          => 10.0,
                'discount_percentage' => 10,
                'tax_id'              => $tax->id,
                'concept_type'        => 'other',
            ]])
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::where('user_id', $this->viticulturist->id)->first();
        $this->assertEquals(900.0,  (float) $invoice->subtotal);
        $this->assertEquals(100.0,  (float) $invoice->discount_amount);
        $this->assertEquals(189.0,  (float) $invoice->tax_amount);
        $this->assertEquals(1089.0, (float) $invoice->total_amount);
    }

    public function test_delivery_note_code_auto_generated(): void
    {
        $client  = $this->makeClient();
        $address = $this->makeAddress($client);
        $tax     = $this->makeTax(21);

        Livewire::test(Create::class)
            ->set('client_id', $client->id)
            ->set('client_address_id', $address->id)
            ->set('invoice_date', '2024-10-15')
            ->set('delivery_note_date', '2024-10-15')
            ->set('items', $this->itemPayload($tax))
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::where('user_id', $this->viticulturist->id)->first();
        $year = now()->year;
        $this->assertStringContainsString((string) $year, $invoice->delivery_note_code);
        $this->assertNull($invoice->invoice_number);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    public function test_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('client_id', '')
            ->set('client_address_id', '')
            ->set('invoice_date', '')
            ->set('delivery_note_date', '')
            ->set('items', [])
            ->call('save')
            ->assertHasErrors(['client_id', 'invoice_date', 'delivery_note_date', 'items']);
    }

    public function test_validates_item_fields(): void
    {
        $client  = $this->makeClient();
        $address = $this->makeAddress($client);

        Livewire::test(Create::class)
            ->set('client_id', $client->id)
            ->set('client_address_id', $address->id)
            ->set('invoice_date', '2024-10-15')
            ->set('delivery_note_date', '2024-10-15')
            ->set('items', [[
                'harvest_id'          => null,
                'name'                => '',   // required
                'description'         => '',
                'sku'                 => '',
                'quantity'            => 0,    // min:0.001
                'unit'                => 'kg',
                'unit_price'          => -1,   // min:0
                'discount_percentage' => 110,  // max:100
                'tax_id'              => null,
                'concept_type'        => 'other',
            ]])
            ->call('save')
            ->assertHasErrors([
                'items.0.name',
                'items.0.quantity',
                'items.0.unit_price',
                'items.0.discount_percentage',
            ]);
    }

    // ── Ownership guard ───────────────────────────────────────────────────────

    public function test_client_from_other_viticulturist_is_rejected(): void
    {
        $other        = $this->makeOtherViticulturist();
        $foreignClient = Client::create([
            'user_id'      => $other->id,
            'client_type'  => 'individual',
            'first_name'   => 'Ajeno',
            'last_name'    => 'Cliente',
            'active'       => true,
        ]);
        $foreignAddress = ClientAddress::create([
            'client_id'  => $foreignClient->id,
            'first_name' => 'Test',
            'address'    => 'Otra Calle 2',
            'is_default' => true,
        ]);
        $tax = $this->makeTax(21);

        Livewire::test(Create::class)
            ->set('client_id', $foreignClient->id)
            ->set('client_address_id', $foreignAddress->id)
            ->set('invoice_date', '2024-10-15')
            ->set('delivery_note_date', '2024-10-15')
            ->set('items', $this->itemPayload($tax))
            ->call('save')
            ->assertHasErrors(['client_id']);
    }
}
