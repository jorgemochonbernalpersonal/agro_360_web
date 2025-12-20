<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Harvest;
use App\Models\HarvestStock;
use App\Models\ContainerState;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceStatusTransitionStockTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Client $client;
    protected Harvest $harvest;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->client = Client::factory()->create(['user_id' => $this->user->id]);
        $this->harvest = Harvest::factory()->withAvailableStock()->create([
            'activity' => fn() => ['viticulturist_id' => $this->user->id]
        ]);
        
        $this->actingAs($this->user);
    }

    /** @test */
    public function changing_draft_to_sent_converts_reservations_to_sales()
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
        $containerState = ContainerState::where('container_id', $this->harvest->container_id)->first();
        $this->assertEquals(0, $containerState->reserved_qty);
        $this->assertEquals($quantity, $containerState->sold_qty);
    }

    /** @test */
    public function changing_draft_to_approved_converts_reservations_to_sales()
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
        $invoice->update(['status' => 'approved']);

        // Assert
        $latestStock = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals('sale', $latestStock->movement_type);
        $this->assertEquals($quantity, $latestStock->sold_qty);
        $this->assertEquals(0, $latestStock->reserved_qty);
    }

    /** @test */
    public function changing_sent_back_to_draft_converts_sales_to_reservations()
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

    /** @test */
    public function cancelling_draft_invoice_releases_all_stock()
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

    /** @test */
    public function cancelling_sent_invoice_releases_sold_stock()
    {
        // Arrange
        $invoice = Invoice::factory()->sent()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $quantity = 300;
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva',
            'quantity' => $quantity,
            'unit_price' => 2.5,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 750,
            'tax_amount' => 0,
            'subtotal' => 750,
            'total' => 750,
            'concept_type' => 'harvest',
        ]);

        $initialStock = $this->harvest->stockMovements()->first();

        // Act - Cancel invoice
        $invoice->update(['status' => 'cancelled']);

        // Assert - Sold stock returned to available
        $latestStock = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals('return', $latestStock->movement_type);
        $this->assertEquals($initialStock->available_qty, $latestStock->available_qty);
        $this->assertEquals(0, $latestStock->sold_qty);
    }

    /** @test */
    public function deleting_invoice_releases_all_stock()
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

    /** @test */
    public function multiple_items_transition_correctly()
    {
        // Arrange - Create draft invoice with multiple harvest items
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $harvest2 = Harvest::factory()->withAvailableStock()->create([
            'activity' => fn() => ['viticulturist_id' => $this->user->id]
        ]);

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

        // Act - Approve invoice
        $invoice->update(['status' => 'approved']);

        // Assert - Both harvests transitioned
        $stock1 = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals($quantity1, $stock1->sold_qty);
        $this->assertEquals(0, $stock1->reserved_qty);

        $stock2 = $harvest2->stockMovements()->latest()->first();
        $this->assertEquals($quantity2, $stock2->sold_qty);
        $this->assertEquals(0, $stock2->reserved_qty);
    }
}
