<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Harvest;
use App\Models\HarvestStock;
use App\Models\Container;
use App\Models\ContainerCurrentState;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceItemStockManagementTest extends TestCase
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

    /** @test */
    public function creating_draft_invoice_item_reserves_stock()
    {
        // Arrange
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $initialStock = $this->harvest->stockMovements()->latest()->first();
        $quantity = 100;

        // Act
        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva Tempranillo',
            'quantity' => $quantity,
            'unit_price' => 1.5,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 150,
            'tax_amount' => 0,
            'subtotal' => 150,
            'total' => 150,
            'concept_type' => 'harvest',
        ]);

        // Assert
        $this->assertDatabaseHas('harvest_stocks', [
            'harvest_id' => $this->harvest->id,
            'movement_type' => 'reserve',
            'available_qty' => $initialStock->available_qty - $quantity,
            'reserved_qty' => $initialStock->reserved_qty + $quantity,
            'sold_qty' => 0,
        ]);

        $container = Container::find($this->harvest->container_id);
        $this->assertNotNull($container, 'Container should exist');
        $containerState = ContainerCurrentState::where('container_id', $container->id)->first();
        $this->assertNotNull($containerState, 'ContainerCurrentState should exist');
        $this->assertEquals($initialStock->available_qty - $quantity, $containerState->available_qty);
        $this->assertEquals($quantity, $containerState->reserved_qty);
    }

    /** @test */
    public function creating_approved_invoice_item_marks_as_sold()
    {
        // Arrange
        $invoice = Invoice::factory()->approved()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $initialStock = $this->harvest->stockMovements()->latest()->first();
        $quantity = 150;

        // Act
        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva Tempranillo',
            'quantity' => $quantity,
            'unit_price' => 2.0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 300,
            'tax_amount' => 0,
            'subtotal' => 300,
            'total' => 300,
            'concept_type' => 'harvest',
        ]);

        // Assert
        $this->assertDatabaseHas('harvest_stocks', [
            'harvest_id' => $this->harvest->id,
            'movement_type' => 'sale',
            'available_qty' => $initialStock->available_qty - $quantity,
            'reserved_qty' => 0,
            'sold_qty' => $quantity,
        ]);

        $container = Container::find($this->harvest->container_id);
        $this->assertNotNull($container, 'Container should exist');
        $containerState = ContainerCurrentState::where('container_id', $container->id)->first();
        $this->assertNotNull($containerState, 'ContainerCurrentState should exist');
        $this->assertEquals($quantity, $containerState->sold_qty);
    }

    /** @test */
    public function updating_quantity_in_draft_adjusts_reservation()
    {
        // Arrange
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva',
            'quantity' => 100,
            'unit_price' => 1.0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 100,
            'tax_amount' => 0,
            'subtotal' => 100,
            'total' => 100,
            'concept_type' => 'harvest',
        ]);

        $stockAfterCreate = $this->harvest->stockMovements()->latest()->first();

        // Act - Aumentar cantidad
        $item->update(['quantity' => 200]);

        // Assert
        $latestStock = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals($stockAfterCreate->reserved_qty + 100, $latestStock->reserved_qty);
        $this->assertEquals($stockAfterCreate->available_qty - 100, $latestStock->available_qty);
    }

    /** @test */
    public function updating_quantity_in_sent_invoice_adjusts_sale()
    {
        // Arrange
        $invoice = Invoice::factory()->sent()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva',
            'quantity' => 100,
            'unit_price' => 1.0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 100,
            'tax_amount' => 0,
            'subtotal' => 100,
            'total' => 100,
            'concept_type' => 'harvest',
        ]);

        $stockAfterCreate = $this->harvest->stockMovements()->latest()->first();

        // Act - Reducir cantidad
        $item->update(['quantity' => 50]);

        // Assert
        $latestStock = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals($stockAfterCreate->sold_qty - 50, $latestStock->sold_qty);
        $this->assertEquals($stockAfterCreate->available_qty + 50, $latestStock->available_qty);
    }

    /** @test */
    public function deleting_draft_item_unreserves_stock()
    {
        // Arrange
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $quantity = 200;
        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva',
            'quantity' => $quantity,
            'unit_price' => 1.0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 200,
            'tax_amount' => 0,
            'subtotal' => 200,
            'total' => 200,
            'concept_type' => 'harvest',
        ]);

        $stockBeforeDelete = $this->harvest->stockMovements()->latest()->first();

        // Act
        $item->delete();

        // Assert
        $latestStock = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals('unreserve', $latestStock->movement_type);
        $this->assertEquals($stockBeforeDelete->available_qty + $quantity, $latestStock->available_qty);
        $this->assertEquals($stockBeforeDelete->reserved_qty - $quantity, $latestStock->reserved_qty);
    }

    /** @test */
    public function deleting_sent_item_returns_stock()
    {
        // Arrange
        $invoice = Invoice::factory()->sent()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $quantity = 150;
        $item = InvoiceItem::create([
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

        $stockBeforeDelete = $this->harvest->stockMovements()->latest()->first();

        // Act
        $item->delete();

        // Assert
        $latestStock = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals('return', $latestStock->movement_type);
        $this->assertEquals($stockBeforeDelete->available_qty + $quantity, $latestStock->available_qty);
        $this->assertEquals($stockBeforeDelete->sold_qty - $quantity, $latestStock->sold_qty);
    }

    /** @test */
    public function container_state_updates_correctly_on_all_operations()
    {
        // Arrange
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $container = Container::find($this->harvest->container_id);
        $this->assertNotNull($container, 'Container should exist');
        $initialContainer = ContainerCurrentState::where('container_id', $container->id)->first();
        $this->assertNotNull($initialContainer, 'ContainerCurrentState should exist');
        $quantity = 100;

        // Act & Assert - Create (reserve)
        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva',
            'quantity' => $quantity,
            'unit_price' => 1.0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 100,
            'tax_amount' => 0,
            'subtotal' => 100,
            'total' => 100,
            'concept_type' => 'harvest',
        ]);

        $containerAfterCreate = ContainerCurrentState::where('container_id', $container->id)->first();
        $this->assertNotNull($containerAfterCreate, 'ContainerCurrentState should exist after creating item');
        $this->assertEquals($initialContainer->available_qty - $quantity, $containerAfterCreate->available_qty);
        $this->assertEquals($quantity, $containerAfterCreate->reserved_qty);
        $this->assertNotNull($containerAfterCreate->last_movement_at);
    }
}
