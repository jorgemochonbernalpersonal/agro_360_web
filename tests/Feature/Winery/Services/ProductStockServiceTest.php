<?php

namespace Tests\Feature\Winery\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceStockMovement;
use App\Models\ProductLot;
use App\Models\Wine;
use App\Services\ProductStockService;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\Feature\WineryTestCase;
use Tests\Traits\CreatesDeliveryScenario;

/**
 * Cubre el sistema de tres cubos de ProductStockService:
 *   available → reserved → sold  (y cancelaciones inversas)
 *
 * Fuente de verdad: app/Services/ProductStockService.php
 */
class ProductStockServiceTest extends WineryTestCase
{
    use CreatesDeliveryScenario;

    private ProductLot $lot;
    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
        $this->actingAs($this->winery);

        $wine = Wine::create([
            'user_id'   => $this->winery->id,
            'name'      => 'Ribera Test',
            'wine_type' => 'red',
            'status'    => 'in_progress',
        ]);

        $this->lot = ProductLot::create([
            'user_id'            => $this->winery->id,
            'wine_id'            => $wine->id,
            'name'               => 'Lote 2024',
            'quantity'           => 1000,
            'initial_quantity'   => 1000,
            'available_quantity' => 1000,
            'reserved_quantity'  => 0,
            'sold_quantity'      => 0,
            'unit'               => 'botellas',
            'wine_type'          => 'tinto',
            'vintage'            => 2024,
        ]);

        $this->invoice = Invoice::factory()->create([
            'user_id'         => $this->winery->id,
            'invoice_number'  => 'FAC-PROD-001',
            'delivery_status' => 'pending',
        ]);
    }

    // ── moveOnCreate ─────────────────────────────────────────────────────────

    public function test_on_create_moves_available_to_reserved(): void
    {
        $item = $this->makeItem(200);

        DB::transaction(function () use ($item) {
            $lot = ProductLot::lockForUpdate()->findOrFail($this->lot->id);
            ProductStockService::moveOnCreate($this->invoice, $item, $lot, 200);
        });

        $this->lot->refresh();
        $this->assertEquals(800.0, (float) $this->lot->available_quantity);
        $this->assertEquals(200.0, (float) $this->lot->reserved_quantity);
        $this->assertEquals(0.0,   (float) $this->lot->sold_quantity);
    }

    public function test_on_create_creates_stock_movement_log(): void
    {
        $item = $this->makeItem(100);

        DB::transaction(function () use ($item) {
            $lot = ProductLot::lockForUpdate()->findOrFail($this->lot->id);
            ProductStockService::moveOnCreate($this->invoice, $item, $lot, 100);
        });

        $this->assertDatabaseHas('invoice_stock_movements', [
            'invoice_id'  => $this->invoice->id,
            'wine_lot_id' => $this->lot->id,
            'action'      => 'create',
            'from_bucket' => 'available',
            'to_bucket'   => 'reserved',
            'qty'         => 100,
        ]);
    }

    public function test_on_create_throws_when_insufficient_stock(): void
    {
        $item = $this->makeItem(5000); // Más de los 1000 disponibles

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Stock insuficiente/i');

        DB::transaction(function () use ($item) {
            $lot = ProductLot::lockForUpdate()->findOrFail($this->lot->id);
            ProductStockService::moveOnCreate($this->invoice, $item, $lot, 5000);
        });
    }

    // ── moveForInvoice — deliver ──────────────────────────────────────────────

    public function test_on_deliver_moves_reserved_to_sold(): void
    {
        $item = $this->makeItem(300);

        DB::transaction(function () use ($item) {
            $lot = ProductLot::lockForUpdate()->findOrFail($this->lot->id);
            ProductStockService::moveOnCreate($this->invoice, $item, $lot, 300);
        });

        $this->invoice->update(['delivery_status' => 'pending']);

        DB::transaction(function () {
            ProductStockService::moveForInvoice($this->invoice, 'deliver');
        });

        $this->lot->refresh();
        $this->assertEquals(700.0, (float) $this->lot->available_quantity);
        $this->assertEquals(0.0,   (float) $this->lot->reserved_quantity);
        $this->assertEquals(300.0, (float) $this->lot->sold_quantity);
    }

    public function test_on_deliver_throws_when_reserved_is_insufficient(): void
    {
        $item = $this->makeItem(400);
        // No se hace moveOnCreate → reserved = 0

        $this->invoice->update(['delivery_status' => 'pending']);

        $this->expectException(RuntimeException::class);

        DB::transaction(function () {
            ProductStockService::moveForInvoice($this->invoice, 'deliver');
        });
    }

    // ── moveForInvoice — cancel ───────────────────────────────────────────────

    public function test_on_cancel_from_pending_restores_available_from_reserved(): void
    {
        $item = $this->makeItem(250);

        DB::transaction(function () use ($item) {
            $lot = ProductLot::lockForUpdate()->findOrFail($this->lot->id);
            ProductStockService::moveOnCreate($this->invoice, $item, $lot, 250);
        });

        $this->invoice->update(['delivery_status' => 'pending']);

        DB::transaction(function () {
            ProductStockService::moveForInvoice($this->invoice, 'cancel');
        });

        $this->lot->refresh();
        $this->assertEquals(1000.0, (float) $this->lot->available_quantity);
        $this->assertEquals(0.0,    (float) $this->lot->reserved_quantity);
        $this->assertEquals(0.0,    (float) $this->lot->sold_quantity);
    }

    public function test_on_cancel_from_delivered_restores_available_from_sold(): void
    {
        $item = $this->makeItem(500);

        DB::transaction(function () use ($item) {
            $lot = ProductLot::lockForUpdate()->findOrFail($this->lot->id);
            ProductStockService::moveOnCreate($this->invoice, $item, $lot, 500);
        });

        $this->invoice->update(['delivery_status' => 'pending']);

        DB::transaction(function () {
            ProductStockService::moveForInvoice($this->invoice, 'deliver');
        });

        $this->invoice->update(['delivery_status' => 'delivered']);

        DB::transaction(function () {
            ProductStockService::moveForInvoice($this->invoice, 'cancel');
        });

        $this->lot->refresh();
        $this->assertEquals(1000.0, (float) $this->lot->available_quantity);
        $this->assertEquals(0.0,    (float) $this->lot->reserved_quantity);
        $this->assertEquals(0.0,    (float) $this->lot->sold_quantity);
    }

    // ── Balance de stock ──────────────────────────────────────────────────────

    public function test_stock_balance_is_maintained_through_full_lifecycle(): void
    {
        $item = $this->makeItem(600);

        // Crear: 1000 available → 400 available, 600 reserved
        DB::transaction(function () use ($item) {
            $lot = ProductLot::lockForUpdate()->findOrFail($this->lot->id);
            ProductStockService::moveOnCreate($this->invoice, $item, $lot, 600);
        });

        $this->lot->refresh();
        $this->assertEquals(
            1000.0,
            (float) $this->lot->available_quantity
                + (float) $this->lot->reserved_quantity
                + (float) $this->lot->sold_quantity,
            'Balance roto tras create'
        );

        // Entregar
        $this->invoice->update(['delivery_status' => 'pending']);

        DB::transaction(function () {
            ProductStockService::moveForInvoice($this->invoice, 'deliver');
        });

        $this->lot->refresh();
        $this->assertEquals(
            1000.0,
            (float) $this->lot->available_quantity
                + (float) $this->lot->reserved_quantity
                + (float) $this->lot->sold_quantity,
            'Balance roto tras deliver'
        );

        // Cancelar
        $this->invoice->update(['delivery_status' => 'delivered']);

        DB::transaction(function () {
            ProductStockService::moveForInvoice($this->invoice, 'cancel');
        });

        $this->lot->refresh();
        $this->assertEquals(
            1000.0,
            (float) $this->lot->available_quantity
                + (float) $this->lot->reserved_quantity
                + (float) $this->lot->sold_quantity,
            'Balance roto tras cancel'
        );
    }

    public function test_multiple_invoices_do_not_exceed_total_quantity(): void
    {
        $invoice2 = Invoice::factory()->create([
            'user_id'         => $this->winery->id,
            'invoice_number'  => 'FAC-PROD-002',
            'delivery_status' => 'pending',
        ]);

        $item1 = $this->makeItem(600);
        $item2 = InvoiceItem::create([
            'invoice_id'   => $invoice2->id,
            'wine_lot_id'  => $this->lot->id,
            'concept_type' => 'wine',
            'name'         => 'Lote 2024',
            'quantity'     => 500,
            'unit_price'   => 5.0,
            'subtotal'     => 2500,
            'total'        => 2500,
            'tax_rate'     => 0,
            'tax_amount'   => 0,
            'tax_base'     => 2500,
            'discount_percentage' => 0,
            'discount_amount'     => 0,
        ]);

        // Reservar 600
        DB::transaction(function () use ($item1) {
            $lot = ProductLot::lockForUpdate()->findOrFail($this->lot->id);
            ProductStockService::moveOnCreate($this->invoice, $item1, $lot, 600);
        });

        // Intentar reservar 500 más (disponible = 400)
        $this->expectException(RuntimeException::class);

        DB::transaction(function () use ($invoice2, $item2) {
            $lot = ProductLot::lockForUpdate()->findOrFail($this->lot->id);
            ProductStockService::moveOnCreate($invoice2, $item2, $lot, 500);
        });
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function makeItem(float $qty): InvoiceItem
    {
        return InvoiceItem::create([
            'invoice_id'   => $this->invoice->id,
            'wine_lot_id'  => $this->lot->id,
            'concept_type' => 'wine',
            'name'         => 'Lote 2024',
            'quantity'     => $qty,
            'unit_price'   => 5.0,
            'subtotal'     => $qty * 5,
            'total'        => $qty * 5,
            'tax_rate'     => 0,
            'tax_amount'   => 0,
            'tax_base'     => $qty * 5,
            'discount_percentage' => 0,
            'discount_amount'     => 0,
        ]);
    }
}
