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

class StockManagementIntegrationTest extends TestCase
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
    public function complete_flow_create_approve_cancel_restores_stock()
    {
        // Get initial stock
        $initialStock = $this->harvest->stockMovements()->latest()->first();
        $initialAvailable = $initialStock->available_qty;

        // Step 1: Create draft invoice
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

        // Verify: Stock reserved
        $stockAfterDraft = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals($initialAvailable - $quantity, $stockAfterDraft->available_qty);
        $this->assertEquals($quantity, $stockAfterDraft->reserved_qty);
        $this->assertEquals(0, $stockAfterDraft->sold_qty);

        // Step 2: Approve invoice
        $invoice->update(['status' => 'approved']);

        // Verify: Stock moved to sold
        $stockAfterApprove = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals($initialAvailable - $quantity, $stockAfterApprove->available_qty);
        $this->assertEquals(0, $stockAfterApprove->reserved_qty);
        $this->assertEquals($quantity, $stockAfterApprove->sold_qty);

        // Step 3: Cancel invoice
        $invoice->update(['status' => 'cancelled']);

        // Verify: Stock fully restored
        $finalStock = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals($initialAvailable, $finalStock->available_qty);
        $this->assertEquals(0, $finalStock->reserved_qty);
        $this->assertEquals(0, $finalStock->sold_qty);

        // Verify container state
        $container = Container::find($this->harvest->container_id);
        $this->assertNotNull($container, 'Container should exist');
        $containerState = ContainerCurrentState::where('container_id', $container->id)->first();
        $this->assertNotNull($containerState, 'ContainerCurrentState should exist');
        $this->assertEquals($initialAvailable, $containerState->available_qty);
        $this->assertEquals(0, $containerState->reserved_qty);
        $this->assertEquals(0, $containerState->sold_qty);
    }

    /** @test */
    public function stock_accuracy_after_multiple_operations()
    {
        $initialStock = $this->harvest->stockMovements()->latest()->first();
        $initialAvailable = $initialStock->available_qty;

        // Create 3 draft invoices with different quantities
        $quantities = [100, 150, 200];
        $invoices = [];

        foreach ($quantities as $qty) {
            $invoice = Invoice::factory()->draft()->create([
                'user_id' => $this->user->id,
                'client_id' => $this->client->id,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'harvest_id' => $this->harvest->id,
                'name' => 'Uva',
                'quantity' => $qty,
                'unit_price' => 1.0,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'tax_base' => $qty,
                'tax_amount' => 0,
                'subtotal' => $qty,
                'total' => $qty,
                'concept_type' => 'harvest',
            ]);

            $invoices[] = $invoice;
        }

        // Verify: All quantities reserved
        $totalReserved = array_sum($quantities);
        $stockAfterReservations = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals($initialAvailable - $totalReserved, $stockAfterReservations->available_qty);
        $this->assertEquals($totalReserved, $stockAfterReservations->reserved_qty);

        // Approve first invoice
        $invoices[0]->update(['status' => 'approved']);

        $stockAfterFirstApproval = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals($quantities[0], $stockAfterFirstApproval->sold_qty);
        $this->assertEquals($quantities[1] + $quantities[2], $stockAfterFirstApproval->reserved_qty);

        // Cancel second invoice
        $invoices[1]->update(['status' => 'cancelled']);

        $stockAfterCancel = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals($quantities[0], $stockAfterCancel->sold_qty);
        $this->assertEquals($quantities[2], $stockAfterCancel->reserved_qty);
        $this->assertEquals($initialAvailable - $quantities[0] - $quantities[2], $stockAfterCancel->available_qty);

        // Delete third invoice
        $invoices[2]->delete();

        $finalStock = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals($quantities[0], $finalStock->sold_qty);
        $this->assertEquals(0, $finalStock->reserved_qty);
        $this->assertEquals($initialAvailable - $quantities[0], $finalStock->available_qty);
    }

    /** @test */
    public function modifying_quantities_maintains_stock_integrity()
    {
        $initialStock = $this->harvest->stockMovements()->latest()->first();
        $initialAvailable = $initialStock->available_qty;

        // Create draft invoice
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

        // Modify quantity multiple times
        $item->update(['quantity' => 150]); // +50
        $item->update(['quantity' => 120]); // -30
        $item->update(['quantity' => 200]); // +80
        $item->update(['quantity' => 180]); // -20

        $stockAfterModifications = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals($initialAvailable - 180, $stockAfterModifications->available_qty);
        $this->assertEquals(180, $stockAfterModifications->reserved_qty);

        // Approve invoice
        $invoice->update(['status' => 'approved']);

        $stockAfterApproval = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals($initialAvailable - 180, $stockAfterApproval->available_qty);
        $this->assertEquals(0, $stockAfterApproval->reserved_qty);
        $this->assertEquals(180, $stockAfterApproval->sold_qty);

        // Modify quantity in approved invoice
        $item->update(['quantity' => 150]); // -30 from sold

        $finalStock = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals($initialAvailable - 150, $finalStock->available_qty);
        $this->assertEquals(150, $finalStock->sold_qty);
    }

    /** @test */
    public function preventing_overselling_maintains_data_integrity()
    {
        $initialStock = $this->harvest->stockMovements()->latest()->first();
        $availableQty = $initialStock->available_qty;

        // Try to create invoice item with more than available
        // Note: In real app, this should be validated BEFORE creating
        // This test verifies that even if created, stock tracking works
        
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        // Reserve almost all stock
        $largeQuantity = $availableQty - 50;
        $item1 = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva 1',
            'quantity' => $largeQuantity,
            'unit_price' => 1.0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => $largeQuantity,
            'tax_amount' => 0,
            'subtotal' => $largeQuantity,
            'total' => $largeQuantity,
            'concept_type' => 'harvest',
        ]);

        $stockAfter = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals(50, $stockAfter->available_qty);

        // Verify that attempting to reserve more results in negative available
        // (In production, this should be prevented by validation)
        $invoice2 = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $item2 = InvoiceItem::create([
            'invoice_id' => $invoice2->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva 2',
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

        $stockWithOversell = $this->harvest->fresh()->stockMovements()->latest()->first();
        
        // Available should be negative (oversold)
        $this->assertEquals(-50, $stockWithOversell->available_qty);
        $this->assertEquals($largeQuantity + 100, $stockWithOversell->reserved_qty);

        // But if we cancel one, it should restore
        $invoice2->update(['status' => 'cancelled']);

        $stockAfterCancel = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals(50, $stockAfterCancel->available_qty);
    }

    /** @test */
    public function stock_movements_create_complete_audit_trail()
    {
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

        // Initial + Reserve
        $this->assertEquals(2, $this->harvest->stockMovements()->count());

        $invoice->update(['status' => 'approved']);
        // + Sale
        $this->assertEquals(3, $this->harvest->stockMovements()->count());

        $invoice->update(['status' => 'cancelled']);
        // + Return
        $this->assertEquals(4, $this->harvest->stockMovements()->count());

        // Verify all movements have proper types
        $movements = $this->harvest->stockMovements()->orderBy('id')->get();
        $this->assertEquals('initial', $movements[0]->movement_type);
        $this->assertEquals('reserve', $movements[1]->movement_type);
        $this->assertEquals('sale', $movements[2]->movement_type);
        $this->assertEquals('return', $movements[3]->movement_type);

        // All movements should reference the invoice
        foreach ($movements->skip(1) as $movement) {
            $this->assertEquals($invoice->invoice_number, $movement->reference_number);
        }
    }
}
