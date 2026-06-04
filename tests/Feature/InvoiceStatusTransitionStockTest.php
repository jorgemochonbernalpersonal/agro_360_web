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

    public function test_changing_draft_to_sent_converts_reservations_to_sales()
    {
        // Arrange - Create draft invoice with items
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $quantity = 200;
        $item = InvoiceItem::create([
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

        // Assert - stock is converted to sale
        $latestStock = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals('sale', $latestStock->movement_type);
        $this->assertEquals($quantity, $latestStock->sold_qty);
        $this->assertEquals(0, $latestStock->reserved_qty);
    }

    public function test_changing_sent_back_to_draft_converts_sales_to_reservations()
    {
        // Arrange - Create sent invoice
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

        $stockAfterSale = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals($quantity, $stockAfterSale->sold_qty);

        // Act - Change back to draft
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

        // Act - Send invoice (converts reservations to sales)
        $invoice->update(['status' => 'sent']);

        // Assert - Both harvests transitioned
        $stock1 = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals($quantity1, $stock1->sold_qty);
        $this->assertEquals(0, $stock1->reserved_qty);

        $stock2 = $harvest2->stockMovements()->latest()->first();
        $this->assertEquals($quantity2, $stock2->sold_qty);
        $this->assertEquals(0, $stock2->reserved_qty);
    }
}
