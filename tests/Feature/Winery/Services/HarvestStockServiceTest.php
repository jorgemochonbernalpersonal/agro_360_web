<?php

namespace Tests\Feature\Winery\Services;

use App\Models\Harvest;
use App\Models\HarvestStock;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\HarvestStockService;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\Feature\WineryTestCase;
use Tests\Traits\CreatesDeliveryScenario;

/**
 * Cubre el sistema de tres cubos de HarvestStockService:
 *   available → reserved → sold  (y cancelaciones inversas)
 *
 * Fuente de verdad: app/Services/HarvestStockService.php
 */
class HarvestStockServiceTest extends WineryTestCase
{
    use CreatesDeliveryScenario;

    private Harvest $harvest;
    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
        $this->actingAs($this->winery);

        // Cosecha con 1000 kg disponibles (sin contenedor: el servicio no lo requiere)
        $this->harvest = Harvest::create([
            'winery_id'          => $this->winery->id,
            'plot_planting_id'   => $this->planting->id,
            'total_weight'       => 1000,
            'harvest_start_date' => '2024-09-15',
            'status'             => 'active',
            'vintage'            => 2024,
        ]);

        // Stock inicial en el ledger
        HarvestStock::create([
            'harvest_id'      => $this->harvest->id,
            'user_id'         => $this->winery->id,
            'movement_type'   => 'initial',
            'quantity_change' => 1000,
            'quantity_after'  => 1000,
            'available_qty'   => 1000,
            'reserved_qty'    => 0,
            'sold_qty'        => 0,
            'gifted_qty'      => 0,
            'lost_qty'        => 0,
        ]);

        $this->invoice = Invoice::factory()->create([
            'user_id'          => $this->winery->id,
            'invoice_number'   => 'FAC-TEST-001',
            'delivery_status'  => 'pending',
        ]);
    }

    // ── reserveForInvoice ─────────────────────────────────────────────────────

    public function test_reserve_moves_available_to_reserved(): void
    {
        $item = $this->makeItem(300);

        DB::transaction(function () {
            HarvestStockService::reserveForInvoice($this->invoice);
        });

        $latest = $this->latestStock();
        $this->assertEquals(700.0, (float) $latest->available_qty);
        $this->assertEquals(300.0, (float) $latest->reserved_qty);
        $this->assertEquals(0.0,   (float) $latest->sold_qty);
    }

    public function test_reserve_creates_ledger_entry_with_movement_type_reserve(): void
    {
        $item = $this->makeItem(200);

        DB::transaction(function () {
            HarvestStockService::reserveForInvoice($this->invoice);
        });

        $this->assertDatabaseHas('harvest_stocks', [
            'harvest_id'    => $this->harvest->id,
            'movement_type' => 'reserve',
            'reserved_qty'  => 200,
            'available_qty' => 800,
        ]);
    }

    public function test_reserve_throws_when_insufficient_stock(): void
    {
        $this->makeItem(2000); // Más de los 1000 kg disponibles

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Stock insuficiente/i');

        DB::transaction(function () {
            HarvestStockService::reserveForInvoice($this->invoice);
        });
    }

    // ── deliverForInvoice ─────────────────────────────────────────────────────

    public function test_deliver_moves_reserved_to_sold(): void
    {
        $item = $this->makeItem(400);

        DB::transaction(function () {
            HarvestStockService::reserveForInvoice($this->invoice);
        });

        DB::transaction(function () {
            HarvestStockService::deliverForInvoice($this->invoice);
        });

        $latest = $this->latestStock();
        $this->assertEquals(600.0, (float) $latest->available_qty);
        $this->assertEquals(0.0,   (float) $latest->reserved_qty);
        $this->assertEquals(400.0, (float) $latest->sold_qty);
    }

    public function test_deliver_does_not_double_count_if_reserved_already_moved(): void
    {
        // Si reserved_qty < qty (ya fue movido), el servicio lo ignora silenciosamente
        $item = $this->makeItem(500);

        DB::transaction(function () {
            HarvestStockService::reserveForInvoice($this->invoice);
        });

        // Simular que reserved ya fue vaciado (edge case)
        HarvestStock::where('harvest_id', $this->harvest->id)->latest('id')->first()
            ->update(['reserved_qty' => 0]);

        // Entregar no debe lanzar ni duplicar sold
        DB::transaction(function () {
            HarvestStockService::deliverForInvoice($this->invoice);
        });

        $latest = $this->latestStock();
        $this->assertEquals(0.0, (float) $latest->sold_qty); // no se movió
    }

    // ── cancelForInvoice ──────────────────────────────────────────────────────

    public function test_cancel_from_reserved_restores_available(): void
    {
        $item = $this->makeItem(300);

        DB::transaction(function () {
            HarvestStockService::reserveForInvoice($this->invoice);
        });

        $this->invoice->update(['delivery_status' => 'pending']);

        DB::transaction(function () {
            HarvestStockService::cancelForInvoice($this->invoice);
        });

        $latest = $this->latestStock();
        $this->assertEquals(1000.0, (float) $latest->available_qty);
        $this->assertEquals(0.0,    (float) $latest->reserved_qty);
        $this->assertEquals(0.0,    (float) $latest->sold_qty);
    }

    public function test_cancel_from_delivered_restores_available_from_sold(): void
    {
        $item = $this->makeItem(500);

        DB::transaction(function () {
            HarvestStockService::reserveForInvoice($this->invoice);
        });

        DB::transaction(function () {
            HarvestStockService::deliverForInvoice($this->invoice);
        });

        // Estado: available=500, reserved=0, sold=500
        $this->invoice->update(['delivery_status' => 'delivered']);

        DB::transaction(function () {
            HarvestStockService::cancelForInvoice($this->invoice);
        });

        $latest = $this->latestStock();
        $this->assertEquals(1000.0, (float) $latest->available_qty);
        $this->assertEquals(0.0,    (float) $latest->reserved_qty);
        $this->assertEquals(0.0,    (float) $latest->sold_qty);
    }

    // ── unreserveItems ────────────────────────────────────────────────────────

    public function test_unreserve_items_releases_reserved_qty(): void
    {
        $item = $this->makeItem(600);

        DB::transaction(function () {
            HarvestStockService::reserveForInvoice($this->invoice);
        });

        $existingItems = $this->invoice->items()
            ->where('concept_type', 'harvest')
            ->get();

        DB::transaction(function () use ($existingItems) {
            HarvestStockService::unreserveItems($existingItems, $this->invoice);
        });

        $latest = $this->latestStock();
        $this->assertEquals(1000.0, (float) $latest->available_qty);
        $this->assertEquals(0.0,    (float) $latest->reserved_qty);
    }

    public function test_unreserve_is_a_no_op_when_nothing_reserved(): void
    {
        // Sin reservas previas, unreserve no debe modificar nada
        $item = $this->makeItem(300);

        $existingItems = $this->invoice->items()->where('concept_type', 'harvest')->get();

        DB::transaction(function () use ($existingItems) {
            HarvestStockService::unreserveItems($existingItems, $this->invoice);
        });

        $ledgerCount = HarvestStock::where('harvest_id', $this->harvest->id)
            ->where('movement_type', 'unreserve')
            ->count();

        $this->assertEquals(0, $ledgerCount);
    }

    // ── moveOnItemSave ────────────────────────────────────────────────────────

    public function test_move_on_item_save_pending_status_reserves(): void
    {
        $item = $this->makeItem(200);

        DB::transaction(function () use ($item) {
            HarvestStockService::moveOnItemSave($this->invoice, $item, 'pending');
        });

        $latest = $this->latestStock();
        $this->assertEquals(800.0, (float) $latest->available_qty);
        $this->assertEquals(200.0, (float) $latest->reserved_qty);
    }

    public function test_move_on_item_save_delivered_status_reserves_then_delivers(): void
    {
        $item = $this->makeItem(250);

        DB::transaction(function () use ($item) {
            HarvestStockService::moveOnItemSave($this->invoice, $item, 'delivered');
        });

        $latest = $this->latestStock();
        $this->assertEquals(750.0, (float) $latest->available_qty);
        $this->assertEquals(0.0,   (float) $latest->reserved_qty);
        $this->assertEquals(250.0, (float) $latest->sold_qty);

        // Debe haber dos entradas: reserve + sale
        $entries = HarvestStock::where('harvest_id', $this->harvest->id)
            ->whereIn('movement_type', ['reserve', 'sale'])
            ->count();
        $this->assertEquals(2, $entries);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function makeItem(float $qty): InvoiceItem
    {
        // withoutEvents: bypasses InvoiceItemObserver which auto-reserves via
        // ContainerStockService (legacy system) and would double-count with
        // HarvestStockService calls made explicitly in the tests.
        return InvoiceItem::withoutEvents(fn () => InvoiceItem::create([
            'invoice_id'   => $this->invoice->id,
            'harvest_id'   => $this->harvest->id,
            'concept_type' => 'harvest',
            'name'         => 'Uva Tempranillo',
            'quantity'     => $qty,
            'unit_price'   => 0.25,
            'subtotal'     => $qty * 0.25,
            'total'        => $qty * 0.25,
            'tax_rate'     => 0,
            'tax_amount'   => 0,
            'tax_base'     => $qty * 0.25,
            'discount_percentage' => 0,
            'discount_amount'     => 0,
        ]));
    }

    private function latestStock(): HarvestStock
    {
        return HarvestStock::where('harvest_id', $this->harvest->id)
            ->latest('id')
            ->firstOrFail();
    }
}
