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

class StockManagementIntegrationTest extends TestCase
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

    public function test_complete_flow_create_approve_revert_cancel_restores_stock()
    {
        // Flujo: draft (reserve) → sent (sigue reservado) → delivered (confirm) →
        // draft (revert) → cancel (release). No se puede cancelar directamente desde
        // sent (requiere rectificativa); el único disparador de venta es delivery_status.

        $initialStock = $this->harvest->stockMovements()->latest()->first();
        $initialAvailable = $initialStock->available_qty;

        // Step 1: Create draft invoice and add item
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

        // Verify: Stock reserved
        $stockAfterDraft = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals($initialAvailable - $quantity, $stockAfterDraft->available_qty);
        $this->assertEquals($quantity, $stockAfterDraft->reserved_qty);
        $this->assertEquals(0, $stockAfterDraft->sold_qty);

        // Step 2: Send invoice (draft → sent no mueve stock; sigue reservado)
        $invoice->update(['status' => 'sent']);

        $stockAfterSent = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals($initialAvailable - $quantity, $stockAfterSent->available_qty);
        $this->assertEquals($quantity, $stockAfterSent->reserved_qty);
        $this->assertEquals(0, $stockAfterSent->sold_qty);

        // Step 3: Confirm delivery (delivery_status → delivered convierte reserva en venta)
        $invoice->refresh()->update(['delivery_status' => 'delivered']);

        $stockAfterDelivery = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals($initialAvailable - $quantity, $stockAfterDelivery->available_qty);
        $this->assertEquals(0, $stockAfterDelivery->reserved_qty);
        $this->assertEquals($quantity, $stockAfterDelivery->sold_qty);

        // Step 4: Revert to draft (con la entrega ya confirmada, revierte venta a reserva)
        $invoice->update(['status' => 'draft']);

        $stockAfterRevert = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals($initialAvailable - $quantity, $stockAfterRevert->available_qty);
        $this->assertEquals($quantity, $stockAfterRevert->reserved_qty);
        $this->assertEquals(0, $stockAfterRevert->sold_qty);

        // Step 5: Cancel draft (releases reservation)
        $invoice->update(['status' => 'cancelled']);

        $finalStock = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals($initialAvailable, $finalStock->available_qty);
        $this->assertEquals(0, $finalStock->reserved_qty);
        $this->assertEquals(0, $finalStock->sold_qty);

        // Verify container state
        $container = Container::find($this->harvest->container_id);
        $this->assertNotNull($container, 'Container should exist');
        $containerState = ContainerCurrentState::where('container_id', $container->id)
            ->where('harvest_id', $this->harvest->id)
            ->first();
        $this->assertNotNull($containerState, 'ContainerCurrentState should exist');
        $this->assertEquals($initialAvailable, $containerState->available_qty);
        $this->assertEquals(0, $containerState->reserved_qty);
        $this->assertEquals(0, $containerState->sold_qty);
    }

    public function test_stock_accuracy_after_multiple_operations()
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

        // Send + deliver first invoice (delivery_status→delivered convierte reserva en venta)
        $invoices[0]->update(['status' => 'sent']);
        $invoices[0]->refresh()->update(['delivery_status' => 'delivered']);

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

    public function test_modifying_quantities_maintains_stock_integrity()
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

        // Send invoice: sigue reservado hasta confirmar la entrega
        $invoice->update(['status' => 'sent']);

        $stockAfterSent = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals($initialAvailable - 180, $stockAfterSent->available_qty);
        $this->assertEquals(180, $stockAfterSent->reserved_qty);
        $this->assertEquals(0, $stockAfterSent->sold_qty);

        // Confirm delivery (delivery_status→delivered converts reservations to sales)
        $invoice->refresh()->update(['delivery_status' => 'delivered']);

        $stockAfterApproval = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals($initialAvailable - 180, $stockAfterApproval->available_qty);
        $this->assertEquals(0, $stockAfterApproval->reserved_qty);
        $this->assertEquals(180, $stockAfterApproval->sold_qty);

        // Modify quantity in delivered invoice
        $item->update(['quantity' => 150]); // -30 from sold

        $finalStock = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals($initialAvailable - 150, $finalStock->available_qty);
        $this->assertEquals(150, $finalStock->sold_qty);
    }

    public function test_preventing_overselling_throws_exception()
    {
        $initialStock = $this->harvest->stockMovements()->latest()->first();
        $availableQty = $initialStock->available_qty;

        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        // Reserve almost all stock (leaves 50 available)
        $largeQuantity = $availableQty - 50;
        InvoiceItem::create([
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

        $stockAfterFirst = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals(50, $stockAfterFirst->available_qty);

        // Attempting to reserve more than available throws and leaves stock intact
        $invoice2 = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $this->expectException(\RuntimeException::class);
        InvoiceItem::create([
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
    }

    public function test_stock_movements_create_complete_audit_trail()
    {
        // Flujo completo: draft(initial+reserve) → sent(sin cambio) → delivered(sale) →
        // draft(reserve, revierte venta) → cancel(unreserve).
        // Cancelar desde sent está prohibido; se debe revertir a draft primero.

        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        InvoiceItem::create([
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

        $invoice->update(['status' => 'sent']);
        // Sin movimiento nuevo: draft→sent ya no mueve stock (único disparador: delivery_status)
        $this->assertEquals(2, $this->harvest->stockMovements()->count());

        // Confirmar entrega
        $invoice->refresh()->update(['delivery_status' => 'delivered']);
        // + Sale
        $this->assertEquals(3, $this->harvest->stockMovements()->count());

        // Sent → draft (revert sale to reservation, la entrega ya estaba confirmada)
        $invoice->update(['status' => 'draft']);
        // + Reserve (revert)
        $this->assertEquals(4, $this->harvest->stockMovements()->count());

        // Draft → cancelled (release reservation)
        $invoice->update(['status' => 'cancelled']);
        // + Unreserve
        $this->assertEquals(5, $this->harvest->stockMovements()->count());

        // Verify all movements have proper types
        $movements = $this->harvest->stockMovements()->orderBy('id')->get();
        $this->assertEquals('initial', $movements[0]->movement_type);
        $this->assertEquals('reserve', $movements[1]->movement_type);
        $this->assertEquals('sale', $movements[2]->movement_type);
        $this->assertEquals('reserve', $movements[3]->movement_type); // revert
        $this->assertEquals('unreserve', $movements[4]->movement_type);

        // Refresh invoice to get the generated invoice_number
        $invoice->refresh();

        // The sale movement (created when delivery_status→delivered) references the invoice number.
        $this->assertEquals($invoice->invoice_number, $movements[2]->reference_number);
    }
}
