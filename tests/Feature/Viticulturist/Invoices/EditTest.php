<?php

namespace Tests\Feature\Viticulturist\Invoices;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Tax;
use App\Models\Harvest;
use App\Models\Container;
use App\Models\ContainerCurrentState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class EditTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Client $client;
    protected ClientAddress $clientAddress;
    protected Tax $tax;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'viticulturist']);
        $this->client = Client::factory()->create(['user_id' => $this->user->id]);
        $this->clientAddress = ClientAddress::create([
            'client_id' => $this->client->id,
            'name' => 'Dirección Principal',
            'address' => 'Calle Test 123',
            'postal_code' => '28001',
            'is_default' => true,
        ]);
        $this->tax = Tax::create([
            'name' => 'IVA',
            'code' => 'IVA',
            'rate' => 21.0,
        ]);
        
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_load_invoice_data()
    {
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'client_address_id' => $this->clientAddress->id,
        ]);

        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity' => 100,
            'unit_price' => 2.5,
            'discount_percentage' => 0,
            'tax_id' => $this->tax->id,
        ]);

        $component = Livewire::test(\App\Livewire\Viticulturist\Invoices\Edit::class, ['invoice' => $invoice->id]);

        $component->assertSet('client_id', $invoice->client_id);
        $component->assertSet('items.0.quantity', 100);
        $component->assertSet('items.0.unit_price', 2.5);
    }

    /** @test */
    public function it_calculates_subtotal_correctly()
    {
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'client_address_id' => $this->clientAddress->id,
        ]);

        $component = Livewire::test(\App\Livewire\Viticulturist\Invoices\Edit::class, ['invoice' => $invoice->id]);

        // Agregar items
        $component->set('items', [
            [
                'id' => null,
                'harvest_id' => null,
                'name' => 'Item 1',
                'quantity' => 10,
                'unit_price' => 5.0,
                'discount_percentage' => 0,
                'tax_id' => $this->tax->id,
                'concept_type' => 'other',
            ],
            [
                'id' => null,
                'harvest_id' => null,
                'name' => 'Item 2',
                'quantity' => 20,
                'unit_price' => 3.0,
                'discount_percentage' => 10, // 10% descuento
                'tax_id' => $this->tax->id,
                'concept_type' => 'other',
            ],
        ]);

        // Subtotal esperado:
        // Item 1: 10 * 5.0 = 50.0
        // Item 2: 20 * 3.0 = 60.0, descuento 10% = 6.0, subtotal = 54.0
        // Total: 50.0 + 54.0 = 104.0
        $component->assertSet('subtotal', 104.0);
    }

    /** @test */
    public function it_calculates_discount_amount_correctly()
    {
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'client_address_id' => $this->clientAddress->id,
        ]);

        $component = Livewire::test(\App\Livewire\Viticulturist\Invoices\Edit::class, ['invoice' => $invoice->id]);

        $component->set('items', [
            [
                'id' => null,
                'harvest_id' => null,
                'name' => 'Item con descuento',
                'quantity' => 100,
                'unit_price' => 10.0,
                'discount_percentage' => 15, // 15% descuento
                'tax_id' => $this->tax->id,
                'concept_type' => 'other',
            ],
        ]);

        // Descuento esperado: 100 * 10.0 * 0.15 = 150.0
        $component->assertSet('discountAmount', 150.0);
    }

    /** @test */
    public function it_calculates_tax_amount_correctly()
    {
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'client_address_id' => $this->clientAddress->id,
        ]);

        $component = Livewire::test(\App\Livewire\Viticulturist\Invoices\Edit::class, ['invoice' => $invoice->id]);

        $component->set('items', [
            [
                'id' => null,
                'harvest_id' => null,
                'name' => 'Item con IVA',
                'quantity' => 50,
                'unit_price' => 4.0,
                'discount_percentage' => 0,
                'tax_id' => $this->tax->id, // 21% IVA
                'concept_type' => 'other',
            ],
        ]);

        // Subtotal: 50 * 4.0 = 200.0
        // Tax amount: 200.0 * 0.21 = 42.0
        $component->assertSet('taxAmount', 42.0);
    }

    /** @test */
    public function it_calculates_total_amount_correctly()
    {
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'client_address_id' => $this->clientAddress->id,
        ]);

        $component = Livewire::test(\App\Livewire\Viticulturist\Invoices\Edit::class, ['invoice' => $invoice->id]);

        $component->set('items', [
            [
                'id' => null,
                'harvest_id' => null,
                'name' => 'Item completo',
                'quantity' => 100,
                'unit_price' => 10.0,
                'discount_percentage' => 10, // 10% descuento
                'tax_id' => $this->tax->id, // 21% IVA
                'concept_type' => 'other',
            ],
        ]);

        // Subtotal: 100 * 10.0 = 1000.0, descuento 10% = 100.0, subtotal final = 900.0
        // Tax: 900.0 * 0.21 = 189.0
        // Total: 900.0 + 189.0 = 1089.0
        $component->assertSet('subtotal', 900.0);
        $component->assertSet('taxAmount', 189.0);
        $component->assertSet('totalAmount', 1089.0);
    }

    /** @test */
    public function it_recalculates_totals_when_item_quantity_changes()
    {
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'client_address_id' => $this->clientAddress->id,
        ]);

        $component = Livewire::test(\App\Livewire\Viticulturist\Invoices\Edit::class, ['invoice' => $invoice->id]);

        $component->set('items', [
            [
                'id' => null,
                'harvest_id' => null,
                'name' => 'Item',
                'quantity' => 10,
                'unit_price' => 5.0,
                'discount_percentage' => 0,
                'tax_id' => $this->tax->id,
                'concept_type' => 'other',
            ],
        ]);

        // Subtotal inicial: 10 * 5.0 = 50.0
        $component->assertSet('subtotal', 50.0);

        // Cambiar cantidad
        $component->set('items.0.quantity', 20);

        // Subtotal actualizado: 20 * 5.0 = 100.0
        $component->assertSet('subtotal', 100.0);
    }

    /** @test */
    public function it_recalculates_totals_when_item_price_changes()
    {
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'client_address_id' => $this->clientAddress->id,
        ]);

        $component = Livewire::test(\App\Livewire\Viticulturist\Invoices\Edit::class, ['invoice' => $invoice->id]);

        $component->set('items', [
            [
                'id' => null,
                'harvest_id' => null,
                'name' => 'Item',
                'quantity' => 10,
                'unit_price' => 5.0,
                'discount_percentage' => 0,
                'tax_id' => $this->tax->id,
                'concept_type' => 'other',
            ],
        ]);

        // Cambiar precio
        $component->set('items.0.unit_price', 7.5);

        // Subtotal actualizado: 10 * 7.5 = 75.0
        $component->assertSet('subtotal', 75.0);
    }

    /** @test */
    public function it_recalculates_totals_when_item_discount_changes()
    {
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'client_address_id' => $this->clientAddress->id,
        ]);

        $component = Livewire::test(\App\Livewire\Viticulturist\Invoices\Edit::class, ['invoice' => $invoice->id]);

        $component->set('items', [
            [
                'id' => null,
                'harvest_id' => null,
                'name' => 'Item',
                'quantity' => 100,
                'unit_price' => 10.0,
                'discount_percentage' => 0,
                'tax_id' => $this->tax->id,
                'concept_type' => 'other',
            ],
        ]);

        // Subtotal inicial: 100 * 10.0 = 1000.0
        $component->assertSet('subtotal', 1000.0);
        $component->assertSet('discountAmount', 0.0);

        // Cambiar descuento
        $component->set('items.0.discount_percentage', 20);

        // Subtotal actualizado: 1000.0 - (1000.0 * 0.20) = 800.0
        // Descuento: 200.0
        $component->assertSet('subtotal', 800.0);
        $component->assertSet('discountAmount', 200.0);
    }

    /** @test */
    public function it_recalculates_totals_when_item_tax_changes()
    {
        $tax10 = Tax::create([
            'name' => 'IVA 10%',
            'code' => 'IVA10',
            'rate' => 10.0,
        ]);
        
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'client_address_id' => $this->clientAddress->id,
        ]);

        $component = Livewire::test(\App\Livewire\Viticulturist\Invoices\Edit::class, ['invoice' => $invoice->id]);

        $component->set('items', [
            [
                'id' => null,
                'harvest_id' => null,
                'name' => 'Item',
                'quantity' => 100,
                'unit_price' => 10.0,
                'discount_percentage' => 0,
                'tax_id' => $this->tax->id, // 21% IVA
                'concept_type' => 'other',
            ],
        ]);

        // Tax inicial: 1000.0 * 0.21 = 210.0
        $component->assertSet('taxAmount', 210.0);

        // Cambiar impuesto
        $component->set('items.0.tax_id', $tax10->id);

        // Tax actualizado: 1000.0 * 0.10 = 100.0
        $component->assertSet('taxAmount', 100.0);
    }

    /** @test */
    public function it_recalculates_totals_when_item_is_added()
    {
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'client_address_id' => $this->clientAddress->id,
        ]);

        $component = Livewire::test(\App\Livewire\Viticulturist\Invoices\Edit::class, ['invoice' => $invoice->id]);

        $component->set('items', [
            [
                'id' => null,
                'harvest_id' => null,
                'name' => 'Item 1',
                'quantity' => 10,
                'unit_price' => 5.0,
                'discount_percentage' => 0,
                'tax_id' => $this->tax->id,
                'concept_type' => 'other',
            ],
        ]);

        // Subtotal inicial: 50.0
        $component->assertSet('subtotal', 50.0);

        // Añadir segundo item
        $component->call('addItem');
        $component->set('items.1.name', 'Item 2');
        $component->set('items.1.quantity', 20);
        $component->set('items.1.unit_price', 3.0);
        $component->set('items.1.discount_percentage', 0);
        $component->set('items.1.tax_id', $this->tax->id);
        $component->set('items.1.concept_type', 'other');

        // Subtotal actualizado: 50.0 + (20 * 3.0) = 110.0
        $component->assertSet('subtotal', 110.0);
    }

    /** @test */
    public function it_recalculates_totals_when_item_is_removed()
    {
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'client_address_id' => $this->clientAddress->id,
        ]);

        $component = Livewire::test(\App\Livewire\Viticulturist\Invoices\Edit::class, ['invoice' => $invoice->id]);

        $component->set('items', [
            [
                'id' => null,
                'harvest_id' => null,
                'name' => 'Item 1',
                'quantity' => 10,
                'unit_price' => 5.0,
                'discount_percentage' => 0,
                'tax_id' => $this->tax->id,
                'concept_type' => 'other',
            ],
            [
                'id' => null,
                'harvest_id' => null,
                'name' => 'Item 2',
                'quantity' => 20,
                'unit_price' => 3.0,
                'discount_percentage' => 0,
                'tax_id' => $this->tax->id,
                'concept_type' => 'other',
            ],
        ]);

        // Subtotal inicial: 50.0 + 60.0 = 110.0
        $component->assertSet('subtotal', 110.0);

        // Eliminar segundo item
        $component->call('removeItem', 1);

        // Subtotal actualizado: 50.0
        $component->assertSet('subtotal', 50.0);
    }

    /** @test */
    public function it_resets_all_data_when_cancel_is_called()
    {
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'client_address_id' => $this->clientAddress->id,
            'delivery_status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $originalItem = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'name' => 'Item Original',
            'quantity' => 50,
            'unit_price' => 10.0,
            'discount_percentage' => 0,
            'tax_id' => $this->tax->id,
        ]);

        $component = Livewire::test(\App\Livewire\Viticulturist\Invoices\Edit::class, ['invoice' => $invoice->id]);

        // Guardar valores originales
        $originalClientId = $component->get('client_id');
        $originalItems = $component->get('items');
        $originalDeliveryStatus = $component->get('delivery_status');

        // Modificar datos
        $component->set('client_id', $this->client->id);
        $component->set('items.0.quantity', 999);
        $component->set('items.0.unit_price', 99.99);
        $component->set('delivery_status', 'delivered');
        $component->call('addItem');

        // Verificar que se modificaron
        $component->assertSet('items.0.quantity', 999);
        $component->assertCount('items', 2);

        // Cancelar
        $component->call('cancel');

        // Verificar que se restauraron los valores originales
        $component->assertSet('client_id', $originalClientId);
        $component->assertSet('delivery_status', $originalDeliveryStatus);
        $component->assertCount('items', 1);
        $component->assertSet('items.0.quantity', 50);
        $component->assertSet('items.0.unit_price', 10.0);
    }

    /** @test */
    public function it_updates_invoice_when_update_is_called()
    {
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'client_address_id' => $this->clientAddress->id,
            'delivery_status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'name' => 'Item Original',
            'quantity' => 50,
            'unit_price' => 10.0,
            'discount_percentage' => 0,
            'tax_id' => $this->tax->id,
        ]);

        $component = Livewire::test(\App\Livewire\Viticulturist\Invoices\Edit::class, ['invoice' => $invoice->id]);

        // Modificar items
        $component->set('items', [
            [
                'id' => null,
                'harvest_id' => null,
                'name' => 'Item Modificado',
                'quantity' => 100,
                'unit_price' => 15.0,
                'discount_percentage' => 10,
                'tax_id' => $this->tax->id,
                'concept_type' => 'other',
            ],
        ]);

        // Actualizar
        $component->call('update');

        // Verificar que se actualizó
        $invoice->refresh();
        $this->assertEquals(1, $invoice->items()->count());
        $this->assertEquals('Item Modificado', $invoice->items()->first()->name);
        $this->assertEquals(100, $invoice->items()->first()->quantity);
        
        // Verificar totales (100 * 15.0 = 1500, descuento 10% = 150, subtotal = 1350, tax 21% = 283.5, total = 1633.5)
        $this->assertEquals(1350.0, $invoice->subtotal);
        $this->assertEquals(150.0, $invoice->discount_amount);
        $this->assertEquals(283.5, round($invoice->tax_amount, 2));
    }

    /** @test */
    public function it_updates_container_state_when_invoice_item_with_harvest_is_modified()
    {
        // Crear contenedor y cosecha
        $container = Container::factory()->create([
            'user_id' => $this->user->id,
            'capacity' => 500,
            'used_capacity' => 400,
        ]);

        $harvest = Harvest::factory()->create([
            'container_id' => $container->id,
            'total_weight' => 400,
        ]);

        // Crear estado inicial del contenedor
        ContainerCurrentState::create([
            'container_id' => $container->id,
            'harvest_id' => $harvest->id,
            'current_quantity' => 400,
            'available_qty' => 300,
            'reserved_qty' => 100,
            'sold_qty' => 0,
        ]);

        // Crear stock inicial
        \App\Models\HarvestStock::create([
            'harvest_id' => $harvest->id,
            'container_id' => $container->id,
            'user_id' => $this->user->id,
            'movement_type' => 'initial',
            'quantity_change' => 400,
            'quantity_after' => 400,
            'available_qty' => 300,
            'reserved_qty' => 100,
            'sold_qty' => 0,
        ]);

        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'client_address_id' => $this->clientAddress->id,
        ]);

        // Crear item original con 100 kg
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $harvest->id,
            'name' => 'Uva',
            'quantity' => 100,
            'unit_price' => 2.0,
            'discount_percentage' => 0,
            'tax_id' => $this->tax->id,
            'concept_type' => 'harvest',
        ]);

        $component = Livewire::test(\App\Livewire\Viticulturist\Invoices\Edit::class, ['invoice' => $invoice->id]);

        // Modificar cantidad a 150 kg
        $component->set('items.0.quantity', 150);

        // Actualizar
        $component->call('update');

        // Verificar que el estado del contenedor se actualizó
        $containerState = ContainerCurrentState::where('container_id', $container->id)->first();
        $this->assertNotNull($containerState);
        // La cantidad disponible debería haber disminuido en 50 kg adicionales (de 100 a 150)
        // available_qty debería ser: 300 - 50 = 250
        // reserved_qty debería ser: 100 + 50 = 150
        $this->assertEquals(250, $containerState->available_qty);
        $this->assertEquals(150, $containerState->reserved_qty);
    }
}

