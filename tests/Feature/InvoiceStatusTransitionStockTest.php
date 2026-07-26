<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Container;
use App\Models\ContainerCurrentState;
use App\Models\Harvest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceStatusTransitionStockTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Traits\CreatesTestHarvest;

    protected User $user;

    protected Client $client;

    protected Harvest $harvest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->client = Client::factory()->create(['user_id' => $this->user->id]);
        $this->harvest = $this->createHarvestWithStock($this->user);

        $this->actingAs($this->user);
    }

    public function test_changing_draft_to_sent_keeps_stock_reserved()
    {
        // Modelo de único disparador: draft→sent solo asigna número/snapshot.
        // El stock no se confirma como venta hasta que se entrega (delivery_status→delivered).
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $quantity = 200;
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva Tempranillo',
            'quantity' => $quantity,
            'unit_price' => 2.0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 400,
            'tax_amount' => 0,
            'subtotal' => 400,
            'total' => 400,
            'concept_type' => 'harvest',
        ]);

        $stockAfterReserve = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals($quantity, $stockAfterReserve->reserved_qty);

        // Act - Change status to sent
        $invoice->update(['status' => 'sent']);

        // Assert - Stock remains reserved (aún no se ha entregado)
        $latestStock = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals('reserve', $latestStock->movement_type);
        $this->assertEquals($quantity, $latestStock->reserved_qty);
        $this->assertEquals(0, $latestStock->sold_qty);

        // Assert - Container state también sigue reservado
        $container = Container::find($this->harvest->container_id);
        $this->assertNotNull($container, 'Container should exist');
        $containerState = ContainerCurrentState::where('container_id', $container->id)->first();
        $this->assertNotNull($containerState, 'ContainerCurrentState should exist');
        $this->assertEquals($quantity, $containerState->reserved_qty);
        $this->assertEquals(0, $containerState->sold_qty);
    }

    public function test_confirming_delivery_converts_reservation_to_sale()
    {
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $quantity = 200;
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva Tempranillo',
            'quantity' => $quantity,
            'unit_price' => 2.0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 400,
            'tax_amount' => 0,
            'subtotal' => 400,
            'total' => 400,
            'concept_type' => 'harvest',
        ]);

        $invoice->update(['status' => 'sent']);

        // Act - Confirmar entrega (único disparador de venta)
        $invoice->refresh()->update(['delivery_status' => 'delivered']);

        // Assert - Stock converted from reserved to sold
        $latestStock = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals('sale', $latestStock->movement_type);
        $this->assertEquals(0, $latestStock->reserved_qty);
        $this->assertEquals($quantity, $latestStock->sold_qty);

        // Assert - Container state updated
        $container = Container::find($this->harvest->container_id);
        $this->assertNotNull($container, 'Container should exist');
        $containerState = ContainerCurrentState::where('container_id', $container->id)->first();
        $this->assertNotNull($containerState, 'ContainerCurrentState should exist');
        $this->assertEquals(0, $containerState->reserved_qty);
        $this->assertEquals($quantity, $containerState->sold_qty);
    }

    public function test_changing_draft_to_sent_also_sets_invoice_number()
    {
        // Arrange
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'invoice_number' => null,
        ]);

        $quantity = 150;
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva',
            'quantity' => $quantity,
            'unit_price' => 1.5,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 225,
            'tax_amount' => 0,
            'subtotal' => 225,
            'total' => 225,
            'concept_type' => 'harvest',
        ]);

        // Act
        $invoice->update(['status' => 'sent']);

        // Assert - se genera el número de factura al emitir
        $this->assertNotNull($invoice->fresh()->invoice_number);

        // Assert - el stock permanece reservado (la venta se confirma con la entrega)
        $latestStock = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals('reserve', $latestStock->movement_type);
        $this->assertEquals($quantity, $latestStock->reserved_qty);
        $this->assertEquals(0, $latestStock->sold_qty);
    }

    public function test_adding_item_to_sent_invoice_reserves_until_delivery()
    {
        // Añadir un item a una factura ya 'sent' (pero no entregada) reserva,
        // igual que en un borrador: delivery_status es el único disparador de venta.
        $invoice = Invoice::factory()->sent()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $quantity = 180;
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva',
            'quantity' => $quantity,
            'unit_price' => 1.8,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 324,
            'tax_amount' => 0,
            'subtotal' => 324,
            'total' => 324,
            'concept_type' => 'harvest',
        ]);

        $stockAfterCreate = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals('reserve', $stockAfterCreate->movement_type);
        $this->assertEquals($quantity, $stockAfterCreate->reserved_qty);
        $this->assertEquals(0, $stockAfterCreate->sold_qty);

        // Act - Change back to draft: delivery_status no cambia → no-op de stock
        $invoice->update(['status' => 'draft']);

        $latestStock = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals($quantity, $latestStock->reserved_qty);
        $this->assertEquals(0, $latestStock->sold_qty);
    }

    public function test_reverting_status_after_delivery_converts_sale_back_to_reservation()
    {
        // Si la entrega ya se confirmó (delivery_status=delivered) y luego el
        // documento se reabre a borrador, la venta sí debe revertirse a reserva.
        $invoice = Invoice::factory()->sent()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $quantity = 180;
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva',
            'quantity' => $quantity,
            'unit_price' => 1.8,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 324,
            'tax_amount' => 0,
            'subtotal' => 324,
            'total' => 324,
            'concept_type' => 'harvest',
        ]);

        $invoice->refresh()->update(['delivery_status' => 'delivered']);

        $stockAfterDelivery = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals($quantity, $stockAfterDelivery->sold_qty);

        // Act - Change back to draft (delivery_status se mantiene 'delivered')
        $invoice->update(['status' => 'draft']);

        // Assert - Stock converted back to reserved
        $latestStock = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals('reserve', $latestStock->movement_type);
        $this->assertEquals($quantity, $latestStock->reserved_qty);
        $this->assertEquals(0, $latestStock->sold_qty);
    }

    public function test_cancelling_draft_invoice_releases_all_stock()
    {
        // Arrange
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $quantity = 250;
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva',
            'quantity' => $quantity,
            'unit_price' => 1.2,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 300,
            'tax_amount' => 0,
            'subtotal' => 300,
            'total' => 300,
            'concept_type' => 'harvest',
        ]);

        $initialStock = $this->harvest->stockMovements()->first();
        $stockAfterReserve = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals($quantity, $stockAfterReserve->reserved_qty);

        // Act - Cancel invoice
        $invoice->update(['status' => 'cancelled']);

        // Assert - All stock released back to available
        $latestStock = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals('unreserve', $latestStock->movement_type);
        $this->assertEquals($initialStock->available_qty, $latestStock->available_qty);
        $this->assertEquals(0, $latestStock->reserved_qty);
    }

    public function test_cancelling_sent_invoice_is_forbidden()
    {
        // Una factura ya enviada NO puede cancelarse directamente (requiere rectificativa).
        $invoice = Invoice::factory()->sent()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'FAC-2026-0099',
        ]);

        $stockBefore = $this->harvest->stockMovements()->latest()->first();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/rectificativa/i');

        $invoice->update(['status' => 'cancelled']);

        // El stock no debe cambiar
        $stockAfter = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals($stockBefore->available_qty, $stockAfter->available_qty);
        $this->assertEquals($stockBefore->sold_qty, $stockAfter->sold_qty);
    }

    public function test_deleting_invoice_releases_all_stock()
    {
        // Arrange
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $quantity = 150;
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva',
            'quantity' => $quantity,
            'unit_price' => 1.0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 150,
            'tax_amount' => 0,
            'subtotal' => 150,
            'total' => 150,
            'concept_type' => 'harvest',
        ]);

        $initialStock = $this->harvest->stockMovements()->first();

        // Act - Delete invoice (cascade deletes items, triggers observers)
        $invoice->delete();

        // Assert - Stock released
        $latestStock = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals($initialStock->available_qty, $latestStock->available_qty);
        $this->assertEquals(0, $latestStock->reserved_qty);
        $this->assertEquals(0, $latestStock->sold_qty);
    }

    public function test_multiple_items_transition_correctly()
    {
        // Arrange - Create draft invoice with multiple harvest items
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $harvest2 = $this->createHarvestWithStock($this->user);

        $quantity1 = 100;
        $quantity2 = 150;

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva 1',
            'quantity' => $quantity1,
            'unit_price' => 1.0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 100,
            'tax_amount' => 0,
            'subtotal' => 100,
            'total' => 100,
            'concept_type' => 'harvest',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $harvest2->id,
            'name' => 'Uva 2',
            'quantity' => $quantity2,
            'unit_price' => 1.5,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 225,
            'tax_amount' => 0,
            'subtotal' => 225,
            'total' => 225,
            'concept_type' => 'harvest',
        ]);

        // Act - Send invoice, then confirm delivery (converts reservations to sales)
        $invoice->update(['status' => 'sent']);
        $invoice->refresh()->update(['delivery_status' => 'delivered']);

        // Assert - Both harvests transitioned
        $stock1 = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals($quantity1, $stock1->sold_qty);
        $this->assertEquals(0, $stock1->reserved_qty);

        $stock2 = $harvest2->stockMovements()->latest()->first();
        $this->assertEquals($quantity2, $stock2->sold_qty);
        $this->assertEquals(0, $stock2->reserved_qty);
    }
}
