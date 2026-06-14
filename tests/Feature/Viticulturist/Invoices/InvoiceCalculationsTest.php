<?php

namespace Tests\Feature\Viticulturist\Invoices;

use App\Livewire\Viticulturist\Invoices\Create;
use App\Livewire\Viticulturist\Invoices\Edit;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Tax;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

/**
 * Tests para lógica de cálculo de facturas:
 * - Subtotal, descuento, impuestos, total
 * - Múltiples tipos de IVA en la misma factura
 * - Edge cases: descuento 100%, sin impuesto, precio 0
 * - Computed properties del componente Edit
 */
class InvoiceCalculationsTest extends ViticulturistTestCase
{
    private User $viticulturist;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viticulturist = $this->makeViticulturist();
        $this->actingAs($this->viticulturist);
    }

    // ── Create: múltiples tipos de IVA ───────────────────────────────────────

    public function test_create_with_multiple_tax_rates(): void
    {
        // Item 1: 100 kg × 2 €/kg = 200, IVA 21% = 42
        // Item 2: 50 litros × 5 €/l = 250, IVA 4% = 10
        // Subtotal = 450, tax = 52, total = 502
        $client = $this->makeClient();
        $address = $this->makeAddress($client);
        $iva21 = $this->makeTax(21);
        $iva4 = $this->makeTax(4, 'IVA Superreducido 4%');

        Livewire::test(Create::class)
            ->set('client_id', $client->id)
            ->set('client_address_id', $address->id)
            ->set('invoice_date', '2024-10-15')
            ->set('delivery_note_date', '2024-10-15')
            ->set('items', [
                $this->makeItem('Uva Tempranillo', 100, 2.0, $iva21->id),
                $this->makeItem('Mosto', 50, 5.0, $iva4->id),
            ])
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::where('user_id', $this->viticulturist->id)->first();
        $this->assertEquals(450.0, (float) $invoice->subtotal);
        $this->assertEquals(52.0, (float) $invoice->tax_amount);
        $this->assertEquals(502.0, (float) $invoice->total_amount);
        $this->assertEquals(2, $invoice->items()->count());
    }

    public function test_create_with_100_percent_discount(): void
    {
        // 100 × 10 = 1000, 100% discount = 1000 off → base 0, tax 0, total 0
        $client = $this->makeClient();
        $address = $this->makeAddress($client);
        $tax = $this->makeTax(21);

        Livewire::test(Create::class)
            ->set('client_id', $client->id)
            ->set('client_address_id', $address->id)
            ->set('invoice_date', '2024-10-15')
            ->set('delivery_note_date', '2024-10-15')
            ->set('items', [
                $this->makeItem('Uva regalada', 100, 10.0, $tax->id, 100),
            ])
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::where('user_id', $this->viticulturist->id)->first();
        $this->assertEquals(0.0, (float) $invoice->subtotal);
        $this->assertEquals(1000.0, (float) $invoice->discount_amount);
        $this->assertEquals(0.0, (float) $invoice->tax_amount);
        $this->assertEquals(0.0, (float) $invoice->total_amount);
    }

    public function test_create_item_without_tax(): void
    {
        // 200 × 3 = 600 sin IVA → subtotal 600, tax 0, total 600
        $client = $this->makeClient();
        $address = $this->makeAddress($client);

        Livewire::test(Create::class)
            ->set('client_id', $client->id)
            ->set('client_address_id', $address->id)
            ->set('invoice_date', '2024-10-15')
            ->set('delivery_note_date', '2024-10-15')
            ->set('items', [
                $this->makeItem('Servicio exento', 200, 3.0, null),
            ])
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::where('user_id', $this->viticulturist->id)->first();
        $this->assertEquals(600.0, (float) $invoice->subtotal);
        $this->assertEquals(0.0, (float) $invoice->tax_amount);
        $this->assertEquals(600.0, (float) $invoice->total_amount);
    }

    public function test_create_item_with_zero_price(): void
    {
        // 100 × 0 = 0 → todo a 0
        $client = $this->makeClient();
        $address = $this->makeAddress($client);
        $tax = $this->makeTax(21);

        Livewire::test(Create::class)
            ->set('client_id', $client->id)
            ->set('client_address_id', $address->id)
            ->set('invoice_date', '2024-10-15')
            ->set('delivery_note_date', '2024-10-15')
            ->set('items', [
                $this->makeItem('Muestra gratuita', 100, 0.0, $tax->id),
            ])
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::where('user_id', $this->viticulturist->id)->first();
        $this->assertEquals(0.0, (float) $invoice->subtotal);
        $this->assertEquals(0.0, (float) $invoice->tax_amount);
        $this->assertEquals(0.0, (float) $invoice->total_amount);
    }

    public function test_create_mixed_items_discount_and_no_discount(): void
    {
        // Item 1: 100 × 10 = 1000, 5% disc = 50 → base 950, IVA 21% = 199.50
        // Item 2: 50  × 4  = 200,  0% disc     → base 200, IVA 10% = 20
        // Subtotal = 1150, discount = 50, tax = 219.50, total = 1369.50
        $client = $this->makeClient();
        $address = $this->makeAddress($client);
        $iva21 = $this->makeTax(21);
        $iva10 = $this->makeTax(10, 'IVA Reducido 10%');

        Livewire::test(Create::class)
            ->set('client_id', $client->id)
            ->set('client_address_id', $address->id)
            ->set('invoice_date', '2024-10-15')
            ->set('delivery_note_date', '2024-10-15')
            ->set('items', [
                $this->makeItem('Uva con descuento', 100, 10.0, $iva21->id, 5),
                $this->makeItem('Mosto sin descuento', 50, 4.0, $iva10->id, 0),
            ])
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::where('user_id', $this->viticulturist->id)->first();
        $this->assertEquals(1150.0, (float) $invoice->subtotal);
        $this->assertEquals(50.0, (float) $invoice->discount_amount);
        $this->assertEquals(219.5, (float) $invoice->tax_amount);
        $this->assertEquals(1369.5, (float) $invoice->total_amount);
    }

    // ── Edit: computed properties ────────────────────────────────────────────

    public function test_edit_computed_subtotal_with_multiple_items(): void
    {
        $tax21 = $this->makeTax(21);
        $tax4 = $this->makeTax(4, 'IVA Superreducido');

        $invoice = $this->makeInvoice([
            ['name' => 'Uva', 'quantity' => 100, 'unit' => 'kg', 'unit_price' => 2.5, 'tax_id' => $tax21->id, 'tax_rate' => 21],
            ['name' => 'Mosto', 'quantity' => 50, 'unit' => 'l', 'unit_price' => 5.0, 'tax_id' => $tax4->id, 'tax_rate' => 4],
        ]);

        $component = Livewire::test(Edit::class, ['invoice' => $invoice]);

        // Items loaded: subtotal = (100×2.5) + (50×5) = 250 + 250 = 500
        $this->assertEquals(500.0, $component->instance()->subtotal);
    }

    public function test_edit_computed_discount_amount(): void
    {
        $tax = $this->makeTax(21);

        $invoice = $this->makeInvoice([
            ['name' => 'Uva', 'quantity' => 100, 'unit' => 'kg', 'unit_price' => 10.0, 'discount_percentage' => 15, 'tax_id' => $tax->id, 'tax_rate' => 21],
        ]);

        $component = Livewire::test(Edit::class, ['invoice' => $invoice]);

        // 100 × 10 = 1000, 15% = 150
        $this->assertEquals(150.0, $component->instance()->discountAmount);
    }

    public function test_edit_computed_tax_amount_fetches_rates_from_db(): void
    {
        // Verifica que getTaxAmountProperty busca el rate en DB (no de $availableTaxes serializado)
        $tax21 = $this->makeTax(21);
        $tax10 = $this->makeTax(10, 'IVA Reducido');

        $invoice = $this->makeInvoice([
            ['name' => 'Item A', 'quantity' => 100, 'unit' => 'kg', 'unit_price' => 10.0, 'tax_id' => $tax21->id, 'tax_rate' => 21],
            ['name' => 'Item B', 'quantity' => 200, 'unit' => 'kg', 'unit_price' => 5.0, 'tax_id' => $tax10->id, 'tax_rate' => 10],
        ]);

        $component = Livewire::test(Edit::class, ['invoice' => $invoice]);

        // Item A: 100×10 = 1000, 21% = 210
        // Item B: 200×5 = 1000, 10% = 100
        // Total tax = 310
        $this->assertEquals(310.0, $component->instance()->taxAmount);
    }

    public function test_edit_computed_total_equals_subtotal_plus_tax(): void
    {
        $tax = $this->makeTax(21);

        $invoice = $this->makeInvoice([
            ['name' => 'Uva', 'quantity' => 100, 'unit' => 'kg', 'unit_price' => 2.5, 'tax_id' => $tax->id, 'tax_rate' => 21],
        ]);

        $component = Livewire::test(Edit::class, ['invoice' => $invoice]);

        // Subtotal = 250, tax = 52.5, total = 302.5
        $this->assertEquals(302.5, $component->instance()->totalAmount);
        $this->assertEquals(
            $component->instance()->subtotal + $component->instance()->taxAmount,
            $component->instance()->totalAmount
        );
    }

    public function test_edit_computed_with_discount_and_tax(): void
    {
        $tax = $this->makeTax(21);

        $invoice = $this->makeInvoice([
            ['name' => 'Uva', 'quantity' => 200, 'unit' => 'kg', 'unit_price' => 5.0, 'discount_percentage' => 10, 'tax_id' => $tax->id, 'tax_rate' => 21],
        ]);

        $component = Livewire::test(Edit::class, ['invoice' => $invoice]);

        // 200×5 = 1000, 10% disc = 100 → base 900, 21% tax = 189, total = 1089
        $this->assertEquals(900.0, $component->instance()->subtotal);
        $this->assertEquals(100.0, $component->instance()->discountAmount);
        $this->assertEquals(189.0, $component->instance()->taxAmount);
        $this->assertEquals(1089.0, $component->instance()->totalAmount);
    }

    public function test_edit_computed_with_no_tax_item(): void
    {
        $invoice = $this->makeInvoice([
            ['name' => 'Servicio', 'quantity' => 10, 'unit' => 'unidades', 'unit_price' => 50.0, 'tax_id' => null, 'tax_rate' => 0],
        ]);

        $component = Livewire::test(Edit::class, ['invoice' => $invoice]);

        $this->assertEquals(500.0, $component->instance()->subtotal);
        $this->assertEquals(0.0, $component->instance()->taxAmount);
        $this->assertEquals(500.0, $component->instance()->totalAmount);
    }

    // ── Edit: update recalcula con múltiples IVA ─────────────────────────────

    public function test_edit_update_recalculates_with_multiple_tax_rates(): void
    {
        $tax21 = $this->makeTax(21);
        $tax10 = $this->makeTax(10, 'IVA Reducido');

        $invoice = $this->makeInvoice([
            ['name' => 'Placeholder', 'quantity' => 1, 'unit' => 'kg', 'unit_price' => 1, 'tax_id' => $tax21->id, 'tax_rate' => 21],
        ]);

        // Reemplazar items: 2 items con IVA diferente
        // Item 1: 300 × 2 = 600, IVA 21% = 126
        // Item 2: 100 × 8 = 800, IVA 10% = 80
        // Subtotal = 1400, tax = 206, total = 1606
        Livewire::test(Edit::class, ['invoice' => $invoice])
            ->set('client_id', $invoice->client_id)
            ->set('client_address_id', $invoice->client_address_id)
            ->set('delivery_status', 'pending')
            ->set('payment_status', 'unpaid')
            ->set('items', [
                $this->makeItem('Uva Garnacha', 300, 2.0, $tax21->id),
                $this->makeItem('Mosto concentrado', 100, 8.0, $tax10->id),
            ])
            ->call('update')
            ->assertHasNoErrors();

        $fresh = $invoice->fresh();
        $this->assertEquals(1400.0, (float) $fresh->subtotal);
        $this->assertEquals(206.0, (float) $fresh->tax_amount);
        $this->assertEquals(1606.0, (float) $fresh->total_amount);
        $this->assertEquals(2, $fresh->items()->count());
    }

    // ── Decimal precision ────────────────────────────────────────────────────

    public function test_create_decimal_precision(): void
    {
        // 33.33 × 1.11 = 36.9963 → redondeado por línea a 3 decimales (InvoiceService::calculateVatLine)
        // IVA 21% sobre la base redondeada ≈ 7.769
        // Total ≈ 44.765 (dentro de la tolerancia de 0.01 frente al cálculo sin redondear)
        $client = $this->makeClient();
        $address = $this->makeAddress($client);
        $tax = $this->makeTax(21);

        Livewire::test(Create::class)
            ->set('client_id', $client->id)
            ->set('client_address_id', $address->id)
            ->set('invoice_date', '2024-10-15')
            ->set('delivery_note_date', '2024-10-15')
            ->set('items', [
                $this->makeItem('Uva selecta', 33.33, 1.11, $tax->id),
            ])
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::where('user_id', $this->viticulturist->id)->first();
        // Subtotal stored without per-item rounding
        $rawSubtotal = 33.33 * 1.11;
        $rawTax = $rawSubtotal * 0.21;

        // Verify stored values are close (DB decimal precision may truncate)
        $this->assertEqualsWithDelta($rawSubtotal, (float) $invoice->subtotal, 0.01);
        $this->assertEqualsWithDelta($rawTax, (float) $invoice->tax_amount, 0.01);
        $this->assertEqualsWithDelta($rawSubtotal + $rawTax, (float) $invoice->total_amount, 0.01);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeClient(): Client
    {
        return Client::create([
            'user_id' => $this->viticulturist->id,
            'client_type' => 'company',
            'company_name' => 'Distribuidora Test S.L.',
            'email' => 'calc-test@test.com',
            'active' => true,
        ]);
    }

    private function makeAddress(Client $client): ClientAddress
    {
        return ClientAddress::create([
            'client_id' => $client->id,
            'first_name' => 'Test',
            'address' => 'Calle Viñedo 1',
            'is_default' => true,
        ]);
    }

    private function makeTax(float $rate, ?string $name = null): Tax
    {
        return Tax::create([
            'name' => $name ?? "IVA {$rate}%",
            'code' => 'IVA',
            'rate' => $rate,
            'active' => true,
            'is_default' => false,
        ]);
    }

    private function makeItem(string $name, float $qty, float $price, ?int $taxId, float $discount = 0, string $concept = 'other'): array
    {
        return [
            'harvest_id' => null,
            'name' => $name,
            'description' => '',
            'sku' => '',
            'quantity' => $qty,
            'unit' => 'kg',
            'unit_price' => $price,
            'discount_percentage' => $discount,
            'tax_id' => $taxId,
            'concept_type' => $concept,
        ];
    }

    private function makeInvoice(array $items, array $invoiceAttrs = []): Invoice
    {
        $client = $this->makeClient();
        $address = $this->makeAddress($client);

        $invoice = Invoice::create(array_merge([
            'user_id' => $this->viticulturist->id,
            'client_id' => $client->id,
            'client_address_id' => $address->id,
            'invoice_type' => 'wine_sale',
            'delivery_note_code' => 'ALB-CALC-0001',
            'invoice_date' => '2024-10-15',
            'delivery_note_date' => '2024-10-15',
            'order_date' => '2024-10-15',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'delivery_status' => 'pending',
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_base' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
        ], $invoiceAttrs));

        foreach ($items as $itemData) {
            InvoiceItem::create(array_merge([
                'invoice_id' => $invoice->id,
                'concept_type' => 'other',
                'discount_percentage' => 0,
                'subtotal' => 0,
                'tax_base' => 0,
                'tax_amount' => 0,
                'total' => 0,
            ], $itemData));
        }

        // Reload with items so loadInvoiceData() sees them
        return $invoice->fresh()->load('items');
    }
}
