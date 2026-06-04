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

/**
 * Tests para casos no cubiertos previamente:
 *
 * - HarvestObserver: ajuste de peso (adjustWeight)
 * - HarvestObserver: transferencia entre contenedores (transferContainer)
 * - HarvestObserver: eliminar cosecha libera contenedor
 * - Capacidad máxima del contenedor
 * - InvoiceItemObserver::restored (soft-delete restore)
 * - delivery_status no mueve stock en ningún caso
 */
class ContainerStockServiceTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Traits\CreatesTestHarvest;

    protected User $user;

    protected Client $client;

    protected Harvest $harvest;

    protected Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->client = Client::factory()->create(['user_id' => $this->user->id]);
        $this->harvest = $this->createHarvestWithStock($this->user, 5000);
        $this->container = Container::find($this->harvest->container_id);

        $this->actingAs($this->user);
    }

    // -------------------------------------------------------------------------
    // HarvestObserver — adjustWeight
    // -------------------------------------------------------------------------

    public function test_increasing_harvest_weight_raises_available_stock()
    {
        $initialStock = $this->harvest->stockMovements()->latest()->first();
        $oldWeight = $this->harvest->total_weight; // 5000
        $newWeight = 6000;

        $this->harvest->update(['total_weight' => $newWeight]);

        $latestStock = $this->harvest->fresh()->stockMovements()->latest()->first();

        $this->assertEquals('adjustment', $latestStock->movement_type);
        $this->assertEquals($initialStock->available_qty + 1000, $latestStock->available_qty);
        $this->assertEquals($initialStock->reserved_qty, $latestStock->reserved_qty);
        $this->assertEquals($initialStock->sold_qty, $latestStock->sold_qty);
    }

    public function test_decreasing_harvest_weight_reduces_available_stock()
    {
        $initialStock = $this->harvest->stockMovements()->latest()->first();
        $newWeight = 3000;

        $this->harvest->update(['total_weight' => $newWeight]);

        $latestStock = $this->harvest->fresh()->stockMovements()->latest()->first();

        $this->assertEquals('adjustment', $latestStock->movement_type);
        $this->assertEquals($initialStock->available_qty - 2000, $latestStock->available_qty);
    }

    public function test_adjusting_weight_updates_container_used_capacity()
    {
        $initialUsed = $this->container->used_capacity; // 5000
        // Contenedor tiene capacity=6000, usado=5000 → libre=1000; subimos solo 500
        $this->harvest->update(['total_weight' => 5500]);

        $this->container->refresh();
        $this->assertEquals($initialUsed + 500, $this->container->used_capacity);
    }

    public function test_adjusting_weight_updates_container_current_state()
    {
        $stateBefore = ContainerCurrentState::where('container_id', $this->container->id)->first();
        $this->assertNotNull($stateBefore);

        $this->harvest->update(['total_weight' => 4000]);

        $stateAfter = ContainerCurrentState::where('container_id', $this->container->id)->first();
        $this->assertEquals($stateBefore->available_qty - 1000, $stateAfter->available_qty);
    }

    public function test_weight_adjustment_does_not_change_reserved_or_sold_qty()
    {
        // Reservar algo primero
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva',
            'quantity' => 500,
            'unit_price' => 1.0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 500,
            'tax_amount' => 0,
            'subtotal' => 500,
            'total' => 500,
            'concept_type' => 'harvest',
        ]);

        $stockBefore = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals(500, $stockBefore->reserved_qty);

        // Ajustar peso — la reserva no debe cambiar
        $this->harvest->update(['total_weight' => 6000]);

        $stockAfter = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals(500, $stockAfter->reserved_qty); // reserva intacta
        $this->assertEquals(0, $stockAfter->sold_qty);
    }

    public function test_increasing_weight_beyond_container_capacity_throws_exception()
    {
        // El contenedor tiene capacidad = 5000 * 1.2 = 6000, ya usada en 5000 → libre = 1000
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/capacidad/i');

        // Intentar subir 2000 kg más cuando solo hay 1000 libres
        $this->harvest->update(['total_weight' => 7001]);
    }

    // -------------------------------------------------------------------------
    // HarvestObserver — transferContainer
    // -------------------------------------------------------------------------

    public function test_transferring_harvest_to_new_container_updates_used_capacity()
    {
        $newContainer = Container::create([
            'user_id' => $this->user->id,
            'name' => 'Cuba Destino',
            'serial_number' => '2',
            'quantity' => 1,
            'capacity' => 20000,
            'used_capacity' => 0,
            'purchase_date' => now(),
            'unit_of_measurement_id' => 1,
            'type_id' => 1,
            'material_id' => 1,
        ]);

        $oldContainer = $this->container;
        $oldUsed = $oldContainer->used_capacity; // 5000

        $this->harvest->update(['container_id' => $newContainer->id]);

        $oldContainer->refresh();
        $newContainer->refresh();

        $this->assertEquals(0, $oldContainer->used_capacity);
        $this->assertEquals($oldUsed, $newContainer->used_capacity);
    }

    public function test_transferring_harvest_creates_new_container_state()
    {
        $newContainer = Container::create([
            'user_id' => $this->user->id,
            'name' => 'Cuba Nueva',
            'serial_number' => '3',
            'quantity' => 1,
            'capacity' => 20000,
            'used_capacity' => 0,
            'purchase_date' => now(),
            'unit_of_measurement_id' => 1,
            'type_id' => 1,
            'material_id' => 1,
        ]);

        $this->harvest->update(['container_id' => $newContainer->id]);

        $newState = ContainerCurrentState::where('container_id', $newContainer->id)->first();
        $this->assertNotNull($newState);
        $this->assertEquals((float) $this->harvest->total_weight, (float) $newState->current_quantity);
    }

    public function test_transferring_to_container_without_capacity_throws_exception()
    {
        $fullContainer = Container::create([
            'user_id' => $this->user->id,
            'name' => 'Cuba Llena',
            'serial_number' => '4',
            'quantity' => 1,
            'capacity' => 100,   // solo 100 kg
            'used_capacity' => 90,    // ya casi llena
            'purchase_date' => now(),
            'unit_of_measurement_id' => 1,
            'type_id' => 1,
            'material_id' => 1,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/capacidad/i');

        $this->harvest->update(['container_id' => $fullContainer->id]);
    }

    // -------------------------------------------------------------------------
    // HarvestObserver — deleting
    // -------------------------------------------------------------------------

    public function test_deleting_harvest_frees_container_capacity()
    {
        $usedBefore = $this->container->used_capacity; // 5000

        $this->harvest->delete();

        $this->container->refresh();
        $this->assertEquals($usedBefore - 5000, $this->container->used_capacity);
    }

    public function test_deleting_harvest_removes_container_current_state()
    {
        $state = ContainerCurrentState::where('container_id', $this->container->id)->first();
        $this->assertNotNull($state);

        $this->harvest->delete();

        $stateAfter = ContainerCurrentState::where('container_id', $this->container->id)->first();
        $this->assertNull($stateAfter);
    }

    public function test_deleting_harvest_with_remaining_harvests_zeroes_state_not_deletes()
    {
        // Añadir una segunda cosecha al mismo contenedor para que no quede vacío
        $harvest2 = $this->createHarvestWithStock($this->user, 1000);

        // Reasignar al mismo contenedor (si el de createHarvestWithStock crea uno nuevo)
        // Para este test, usamos el mismo contenedor forzando la update
        $harvest2->update(['container_id' => $this->container->id]);

        // Ahora eliminar la cosecha original
        $this->harvest->delete();

        $this->container->refresh();
        // El contenedor no debe estar vacío (la segunda cosecha sigue ahí)
        $this->assertGreaterThan(0, $this->container->used_capacity);
    }

    // -------------------------------------------------------------------------
    // Capacidad máxima al crear cosecha
    // -------------------------------------------------------------------------

    public function test_creating_harvest_beyond_container_capacity_throws_exception()
    {
        // El contenedor tiene capacity = 5000*1.2 = 6000, used = 5000 → libre = 1000
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/capacidad/i');

        // Usar el mismo contenedor para una nueva cosecha de 2000 kg (no caben)
        $campaign = \App\Models\Campaign::where('viticulturist_id', $this->user->id)->first();
        $plot = \App\Models\Plot::where('viticulturist_id', $this->user->id)->first();
        $planting = \App\Models\PlotPlanting::where('plot_id', $plot->id)->first();
        $activity = \App\Models\AgriculturalActivity::create([
            'viticulturist_id' => $this->user->id,
            'campaign_id' => $campaign->id,
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        Harvest::create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
            'container_id' => $this->container->id,
            'harvest_start_date' => now(),
            'harvest_end_date' => now()->addDay(),
            'total_weight' => 2000, // supera los 1000 libres
            'unit' => 'kg',
        ]);
    }

    // -------------------------------------------------------------------------
    // delivery_status no mueve stock
    // -------------------------------------------------------------------------

    public function test_changing_delivery_status_to_in_transit_does_not_move_stock()
    {
        $invoice = Invoice::factory()->sent()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'delivery_status' => 'pending',
        ]);

        $quantity = 300;
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva',
            'quantity' => $quantity,
            'unit_price' => 1.0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 300,
            'tax_amount' => 0,
            'subtotal' => 300,
            'total' => 300,
            'concept_type' => 'harvest',
        ]);

        $stockBefore = $this->harvest->fresh()->stockMovements()->latest()->first();
        $countBefore = $this->harvest->stockMovements()->count();

        // Cambiar delivery_status — NO debe crear movimientos de stock
        $invoice->update(['delivery_status' => 'in_transit']);

        $stockAfter = $this->harvest->fresh()->stockMovements()->latest()->first();
        $countAfter = $this->harvest->stockMovements()->count();

        $this->assertEquals($countBefore, $countAfter);
        $this->assertEquals($stockBefore->available_qty, $stockAfter->available_qty);
        $this->assertEquals($stockBefore->sold_qty, $stockAfter->sold_qty);
        $this->assertEquals($stockBefore->reserved_qty, $stockAfter->reserved_qty);
    }

    public function test_changing_delivery_status_to_delivered_does_not_move_stock()
    {
        $invoice = Invoice::factory()->sent()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'delivery_status' => 'in_transit',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva',
            'quantity' => 200,
            'unit_price' => 1.0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 200,
            'tax_amount' => 0,
            'subtotal' => 200,
            'total' => 200,
            'concept_type' => 'harvest',
        ]);

        $countBefore = $this->harvest->stockMovements()->count();
        $soldBefore = $this->harvest->stockMovements()->latest()->first()->sold_qty;

        $invoice->update(['delivery_status' => 'delivered']);

        $this->assertEquals($countBefore, $this->harvest->stockMovements()->count());
        $this->assertEquals($soldBefore, $this->harvest->fresh()->stockMovements()->latest()->first()->sold_qty);
    }

    public function test_changing_delivery_status_pending_to_in_transit_sets_no_stock_movements()
    {
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'delivery_status' => 'pending',
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

        $reservedBefore = $this->harvest->stockMovements()->latest()->first()->reserved_qty;

        // delivery_status no debe afectar la reserva
        $invoice->update(['delivery_status' => 'in_transit']);

        $reservedAfter = $this->harvest->fresh()->stockMovements()->latest()->first()->reserved_qty;
        $this->assertEquals($reservedBefore, $reservedAfter);
    }

    // -------------------------------------------------------------------------
    // InvoiceItemObserver::restored
    // -------------------------------------------------------------------------

    public function test_restoring_soft_deleted_item_re_reserves_stock()
    {
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $quantity = 400;
        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva',
            'quantity' => $quantity,
            'unit_price' => 1.0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 400,
            'tax_amount' => 0,
            'subtotal' => 400,
            'total' => 400,
            'concept_type' => 'harvest',
        ]);

        $stockAfterCreate = $this->harvest->stockMovements()->latest()->first();
        $this->assertEquals($quantity, $stockAfterCreate->reserved_qty);

        // Soft-delete el item → debe liberar la reserva
        $item->delete();

        $stockAfterDelete = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals('unreserve', $stockAfterDelete->movement_type);
        $this->assertEquals(0, $stockAfterDelete->reserved_qty);

        // Restaurar el item → debe volver a reservar
        $item->restore();

        $stockAfterRestore = $this->harvest->fresh()->stockMovements()->latest()->first();
        $this->assertEquals('reserve', $stockAfterRestore->movement_type);
        $this->assertEquals($quantity, $stockAfterRestore->reserved_qty);
    }

    public function test_restoring_item_from_non_draft_invoice_does_not_re_reserve()
    {
        // Si el item restaurado pertenece a una factura NO-draft, el observer no re-reserva
        $invoice = Invoice::factory()->sent()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $this->harvest->id,
            'name' => 'Uva',
            'quantity' => 200,
            'unit_price' => 1.0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_base' => 200,
            'tax_amount' => 0,
            'subtotal' => 200,
            'total' => 200,
            'concept_type' => 'harvest',
        ]);

        $countBefore = $this->harvest->stockMovements()->count();

        $item->delete();
        $item->restore();

        // Solo debe haber añadido el 'return' del delete, no un 'reserve' del restore
        $movements = $this->harvest->fresh()->stockMovements()->orderBy('id')->get();
        $lastMovement = $movements->last();

        // El último movimiento tras restore en factura sent NO debe ser 'reserve'
        $this->assertNotEquals('reserve', $lastMovement->movement_type);
    }
}
