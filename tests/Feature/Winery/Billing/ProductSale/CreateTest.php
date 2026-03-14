<?php

namespace Tests\Feature\Winery\Billing\ProductSale;

use App\Livewire\Winery\Billing\ProductSale\Create;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ProductLot;
use App\Models\Tax;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class CreateTest extends WineryTestCase
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

    private function makeLot(int $available = 500): ProductLot
    {
        return ProductLot::create([
            'user_id'            => $this->winery->id,
            'name'               => 'Rioja Reserva 2020',
            'available_quantity' => $available,
            'reserved_quantity'  => 0,
            'sold_quantity'      => 0,
            'unit'               => 'botellas',
            'price_per_unit'     => 12.50,
            'archived'           => false,
        ]);
    }

    private function validItems(int $lotId, int $taxId, float $qty = 6): array
    {
        return [[
            'wine_lot_id'         => $lotId,
            'name'                => 'Rioja Reserva 2020',
            'description'         => '',
            'sku'                 => '',
            'quantity'            => $qty,
            'available_qty'       => 500,
            'unit_price'          => 12.50,
            'discount_percentage' => 0,
            'tax_id'              => $taxId,
            'concept_type'        => 'wine',
        ]];
    }

    // ── Happy path ────────────────────────────────────────────────────────────

    public function test_creates_invoice_and_reserves_stock(): void
    {
        $client  = $this->makeClient();
        $address = $this->makeAddress($client);
        $tax     = $this->makeTax();
        $lot     = $this->makeLot(500);

        Livewire::test(Create::class)
            ->set('client_id', (string) $client->id)
            ->set('client_address_id', (string) $address->id)
            ->set('order_date', '2024-10-31')
            ->set('items', $this->validItems($lot->id, $tax->id, 6))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('winery.invoices.products.index'));

        $this->assertDatabaseHas('invoices', [
            'user_id'      => $this->winery->id,
            'invoice_type' => 'wine_sale',
            'status'       => 'draft',
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'wine_lot_id'  => $lot->id,
            'concept_type' => 'wine',
            'quantity'     => 6,
        ]);

        // Stock reserved: 500 - 6 = 494 available, 6 reserved
        $this->assertEquals(494, (float) $lot->fresh()->available_quantity);
        $this->assertEquals(6,   (float) $lot->fresh()->reserved_quantity);
    }

    public function test_delivery_note_code_is_auto_generated(): void
    {
        $client  = $this->makeClient();
        $address = $this->makeAddress($client);
        $tax     = $this->makeTax();
        $lot     = $this->makeLot();

        Livewire::test(Create::class)
            ->set('client_id', (string) $client->id)
            ->set('client_address_id', (string) $address->id)
            ->set('order_date', '2024-10-31')
            ->set('items', $this->validItems($lot->id, $tax->id))
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::where('user_id', $this->winery->id)->first();
        $this->assertNotNull($invoice->delivery_note_code);
        $this->assertNotEmpty($invoice->delivery_note_code);
    }

    public function test_total_calculation_with_tax(): void
    {
        // 6 bottles × 12.50 = 75 base; 21% IVA = 15.75; total = 90.75
        $client  = $this->makeClient();
        $address = $this->makeAddress($client);
        $tax     = $this->makeTax(21);
        $lot     = $this->makeLot();

        Livewire::test(Create::class)
            ->set('client_id', (string) $client->id)
            ->set('client_address_id', (string) $address->id)
            ->set('order_date', '2024-10-31')
            ->set('items', $this->validItems($lot->id, $tax->id, 6))
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::where('user_id', $this->winery->id)->first();
        $this->assertEquals(75.0,    (float) $invoice->subtotal);
        $this->assertEquals(15.750,  (float) $invoice->tax_amount);
        $this->assertEquals(90.750,  (float) $invoice->total_amount);
    }

    public function test_gift_invoice_has_zero_totals(): void
    {
        $client  = $this->makeClient();
        $address = $this->makeAddress($client);
        $tax     = $this->makeTax();
        $lot     = $this->makeLot();

        Livewire::test(Create::class)
            ->set('client_id', (string) $client->id)
            ->set('client_address_id', (string) $address->id)
            ->set('order_date', '2024-10-31')
            ->set('is_gift', true)
            ->set('items', $this->validItems($lot->id, $tax->id))
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::where('user_id', $this->winery->id)->first();
        $this->assertEquals(0.0, (float) $invoice->subtotal);
        $this->assertEquals(0.0, (float) $invoice->tax_amount);
        $this->assertEquals(0.0, (float) $invoice->total_amount);
        $this->assertTrue((bool) $invoice->gift);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    public function test_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('client_id', '')
            ->set('order_date', '')
            ->set('items', [])
            ->call('save')
            ->assertHasErrors(['client_id', 'order_date', 'items']);
    }

    public function test_validates_line_fields(): void
    {
        $client  = $this->makeClient();
        $address = $this->makeAddress($client);

        Livewire::test(Create::class)
            ->set('client_id', (string) $client->id)
            ->set('client_address_id', (string) $address->id)
            ->set('order_date', '2024-10-31')
            ->set('items', [[
                'wine_lot_id'         => null,
                'name'                => '',      // required
                'description'         => '',
                'sku'                 => '',
                'quantity'            => 0,       // min:0.001
                'available_qty'       => null,
                'unit_price'          => -1,      // min:0
                'discount_percentage' => 150,     // max:100
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

    // ── Ownership guards ──────────────────────────────────────────────────────

    public function test_client_from_other_winery_is_rejected(): void
    {
        $otherWinery = $this->makeOtherWinery();
        $stranger    = Client::create([
            'user_id'      => $otherWinery->id,
            'client_type'  => 'company',
            'company_name' => 'Empresa Ajena S.L.',
            'active'       => true,
        ]);
        $tax = $this->makeTax();
        $lot = $this->makeLot();

        Livewire::test(Create::class)
            ->set('client_id', (string) $stranger->id)
            ->set('order_date', '2024-10-31')
            ->set('items', $this->validItems($lot->id, $tax->id))
            ->call('save')
            ->assertHasErrors(['client_id']);
    }

    public function test_lot_from_other_winery_is_rejected(): void
    {
        $otherWinery = $this->makeOtherWinery();
        $client      = $this->makeClient();
        $address     = $this->makeAddress($client);
        $tax         = $this->makeTax();
        $foreignLot  = ProductLot::create([
            'user_id'            => $otherWinery->id,
            'name'               => 'Lote Ajeno',
            'available_quantity' => 100,
            'reserved_quantity'  => 0,
            'sold_quantity'      => 0,
            'unit'               => 'botellas',
            'archived'           => false,
        ]);

        Livewire::test(Create::class)
            ->set('client_id', (string) $client->id)
            ->set('client_address_id', (string) $address->id)
            ->set('order_date', '2024-10-31')
            ->set('items', $this->validItems($foreignLot->id, $tax->id))
            ->call('save')
            ->assertHasErrors(['items.0.wine_lot_id']);
    }
}
