<?php

namespace Tests\Feature\Producer\Invoices;

use App\Livewire\Producer\Invoices\Edit;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\GrapeVariety;
use App\Models\Harvest;
use App\Models\HarvestStock;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\ProductLot;
use App\Models\Tax;
use Livewire\Livewire;
use Tests\Feature\ProducerTestCase;

/**
 * Tests para la edición de albaranes mixtos del producer.
 *
 * Invariantes:
 *   - Al actualizar, las reservas anteriores se revierten antes de re-reservar.
 *   - El stock neto (available + reserved) se mantiene constante entre ediciones.
 *   - Una factura bloqueada (delivered/cancelled) no puede ser modificada.
 */
class MixedInvoiceEditTest extends ProducerTestCase
{
    private \App\Models\User $producer;
    private Plot $plot;
    private PlotPlanting $planting;
    private Client $client;
    private ClientAddress $address;
    private Tax $tax;

    protected function setUp(): void
    {
        parent::setUp();

        $this->producer = $this->makeProducer();
        $this->actingAs($this->producer);

        $grapeVariety = GrapeVariety::firstOrCreate(
            ['code' => 'TEMP'],
            ['name' => 'Tempranillo', 'color' => 'red']
        );

        $this->plot = Plot::create([
            'viticulturist_id' => $this->producer->id,
            'name'             => 'Parcela Edit Test',
            'reference'        => 'ET-001',
            'area'             => 3.0,
            'active'           => true,
        ]);

        $this->planting = PlotPlanting::create([
            'plot_id'          => $this->plot->id,
            'grape_variety_id' => $grapeVariety->id,
            'area_planted'     => 3.0,
            'planting_year'    => now()->year - 5,
            'status'           => 'active',
        ]);

        $this->client = Client::create([
            'user_id'      => $this->producer->id,
            'client_type'  => 'company',
            'company_name' => 'Cliente Edit Test S.L.',
            'email'        => 'cliente-edit@test.com',
            'active'       => true,
        ]);

        $this->address = ClientAddress::create([
            'client_id'  => $this->client->id,
            'first_name' => 'Test',
            'address'    => 'Calle Edición 1',
            'is_default' => true,
        ]);

        $this->tax = Tax::create([
            'name'       => 'IVA 10%',
            'code'       => 'IVA',
            'rate'       => 10,
            'active'     => true,
            'is_default' => false,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Crea una cosecha de cuaderno con su HarvestStock inicial disponible.
     */
    private function makeHarvest(float $total = 500): Harvest
    {
        $campaign = Campaign::getOrCreateActiveForYear($this->producer->id, now()->year);

        $activity = AgriculturalActivity::create([
            'plot_id'          => $this->plot->id,
            'viticulturist_id' => $this->producer->id,
            'campaign_id'      => $campaign->id,
            'activity_type'    => 'harvest',
            'activity_date'    => now()->toDateString(),
        ]);

        return Harvest::withoutEvents(fn () => Harvest::create([
            'activity_id'        => $activity->id,
            'plot_planting_id'   => $this->planting->id,
            'harvest_start_date' => now()->toDateString(),
            'total_weight'       => $total,
            'status'             => 'active',
        ]));
    }

    /**
     * Crea un invoice en estado draft con los datos mínimos necesarios.
     */
    private function makeInvoice(): Invoice
    {
        return Invoice::create([
            'user_id'            => $this->producer->id,
            'client_id'          => $this->client->id,
            'client_address_id'  => $this->address->id,
            'invoice_type'       => 'producer_sale',
            'status'             => 'draft',
            'delivery_status'    => 'pending',
            'payment_status'     => 'unpaid',
            'invoice_date'       => now()->toDateString(),
            'delivery_note_date' => now()->toDateString(),
            'subtotal'           => 0,
            'discount_amount'    => 0,
            'tax_base'           => 0,
            'tax_amount'         => 0,
            'total_amount'       => 0,
        ]);
    }

    /**
     * Simula el estado "post-creación" de una cosecha con 200 kg reservados:
     *   - HarvestStock inicial (initial): available=total, reserved=0
     *   - HarvestStock de reserva (reserve): available=total-200, reserved=200
     * Devuelve el InvoiceItem creado.
     */
    private function seedHarvestItem(Invoice $invoice, Harvest $harvest, float $reserved = 200): InvoiceItem
    {
        $item = InvoiceItem::withoutEvents(fn () => $invoice->items()->create([
            'harvest_id'          => $harvest->id,
            'wine_lot_id'         => null,
            'concept_type'        => 'harvest',
            'name'                => 'Tempranillo - Parcela Edit Test',
            'description'         => null,
            'sku'                 => "HARV-{$harvest->id}",
            'quantity'            => $reserved,
            'unit'                => 'kg',
            'unit_price'          => 0.45,
            'discount_percentage' => 0,
            'discount_amount'     => 0,
            'tax_id'              => $this->tax->id,
            'tax_name'            => 'IVA 10%',
            'tax_rate'            => 10,
            'tax_base'            => round($reserved * 0.45, 3),
            'tax_amount'          => round($reserved * 0.45 * 0.1, 3),
            'subtotal'            => round($reserved * 0.45, 3),
            'total'               => round($reserved * 0.45 * 1.1, 3),
        ]));

        // Crear stock inicial
        HarvestStock::create([
            'harvest_id'      => $harvest->id,
            'user_id'         => $this->producer->id,
            'movement_type'   => 'initial',
            'quantity_change' => $harvest->total_weight,
            'quantity_after'  => $harvest->total_weight,
            'available_qty'   => $harvest->total_weight,
            'reserved_qty'    => 0,
            'sold_qty'        => 0,
            'gifted_qty'      => 0,
            'lost_qty'        => 0,
        ]);

        // Crear reserva
        HarvestStock::create([
            'harvest_id'      => $harvest->id,
            'user_id'         => $this->producer->id,
            'invoice_item_id' => $item->id,
            'movement_type'   => 'reserve',
            'quantity_change' => 0,
            'quantity_after'  => $harvest->total_weight,
            'available_qty'   => $harvest->total_weight - $reserved,
            'reserved_qty'    => $reserved,
            'sold_qty'        => 0,
            'gifted_qty'      => 0,
            'lost_qty'        => 0,
        ]);

        return $item;
    }

    /**
     * Crea un lote con estado "post-creación" (12 unidades reservadas de 100).
     */
    private function seedProductLot(int $available = 88, int $reserved = 12): ProductLot
    {
        return ProductLot::create([
            'user_id'            => $this->producer->id,
            'name'               => 'Tempranillo Reserva 2021',
            'available_quantity' => $available,
            'reserved_quantity'  => $reserved,
            'sold_quantity'      => 0,
            'unit'               => 'botellas',
            'price_per_unit'     => 8.50,
            'archived'           => false,
        ]);
    }

    /**
     * Construye el array de ítem de cosecha para set('items', [...]).
     */
    private function harvestItemData(int $harvestId, float $qty = 200, float $available = 300): array
    {
        return [
            'harvest_id'          => $harvestId,
            'wine_lot_id'         => null,
            'concept_type'        => 'harvest',
            'name'                => 'Tempranillo - Parcela Edit Test',
            'description'         => '',
            'sku'                 => "HARV-{$harvestId}",
            'quantity'            => $qty,
            'unit'                => 'kg',
            'available_qty'       => $available,
            'unit_price'          => 0.45,
            'discount_percentage' => 0,
            'tax_id'              => $this->tax->id,
        ];
    }

    /**
     * Construye el array de ítem de lote de vino para set('items', [...]).
     */
    private function wineItemData(int $lotId, float $qty = 12, float $available = 100): array
    {
        return [
            'harvest_id'          => null,
            'wine_lot_id'         => $lotId,
            'concept_type'        => 'wine',
            'name'                => 'Tempranillo Reserva 2021',
            'description'         => '',
            'sku'                 => '',
            'quantity'            => $qty,
            'unit'                => 'botella',
            'available_qty'       => $available,
            'unit_price'          => 8.50,
            'discount_percentage' => 0,
            'tax_id'              => $this->tax->id,
        ];
    }

    // ── Mount / carga de datos ─────────────────────────────────────────────────

    public function test_edit_loads_existing_invoice_data(): void
    {
        $invoice = $this->makeInvoice();

        $component = Livewire::test(Edit::class, ['invoice' => $invoice]);

        $component->assertSet('client_id', (string) $this->client->id);
        $component->assertSet('client_address_id', (string) $this->address->id);
        $component->assertSet('delivery_status', 'pending');
        $component->assertSet('payment_status', 'unpaid');
    }

    // ── Actualizar ítem de cosecha ─────────────────────────────────────────────

    public function test_update_harvest_item_reverses_old_reservation_and_creates_new(): void
    {
        $harvest = $this->makeHarvest(500);
        $invoice = $this->makeInvoice();
        $this->seedHarvestItem($invoice, $harvest, reserved: 200);

        // Estado actual: available=300, reserved=200

        Livewire::test(Edit::class, ['invoice' => $invoice])
            ->set('items', [$this->harvestItemData($harvest->id, qty: 150, available: 300)])
            ->call('update')
            ->assertHasNoErrors();

        // Debe existir una entrada 'unreserve' (revertir 200)
        $this->assertDatabaseHas('harvest_stocks', [
            'harvest_id'    => $harvest->id,
            'movement_type' => 'unreserve',
        ]);

        // Y una nueva entrada 'reserve' (reservar 150)
        $latest = HarvestStock::where('harvest_id', $harvest->id)->latest('id')->first();
        $this->assertNotNull($latest);
        $this->assertEquals('reserve', $latest->movement_type);
        $this->assertEquals(350.0, (float) $latest->available_qty, 'available_qty debe ser 500 - 150 = 350');
        $this->assertEquals(150.0, (float) $latest->reserved_qty, 'reserved_qty debe ser 150');
    }

    public function test_update_harvest_item_increasing_quantity(): void
    {
        $harvest = $this->makeHarvest(500);
        $invoice = $this->makeInvoice();
        $this->seedHarvestItem($invoice, $harvest, reserved: 100);

        // Estado actual: available=400, reserved=100. Editamos a 250.
        Livewire::test(Edit::class, ['invoice' => $invoice])
            ->set('items', [$this->harvestItemData($harvest->id, qty: 250, available: 400)])
            ->call('update')
            ->assertHasNoErrors();

        $latest = HarvestStock::where('harvest_id', $harvest->id)->latest('id')->first();
        $this->assertEquals('reserve', $latest->movement_type);
        $this->assertEquals(250.0, (float) $latest->available_qty, 'available_qty debe ser 500 - 250 = 250');
        $this->assertEquals(250.0, (float) $latest->reserved_qty);
    }

    // ── Actualizar ítem de lote de vino ───────────────────────────────────────

    public function test_update_wine_lot_item_reverses_old_reservation_and_creates_new(): void
    {
        $lot     = $this->seedProductLot(available: 88, reserved: 12);
        $invoice = $this->makeInvoice();

        // Seed del item de vino existente (bypass observer)
        InvoiceItem::withoutEvents(fn () => $invoice->items()->create([
            'wine_lot_id'         => $lot->id,
            'harvest_id'          => null,
            'concept_type'        => 'wine',
            'name'                => 'Tempranillo Reserva 2021',
            'quantity'            => 12,
            'unit'                => 'botella',
            'unit_price'          => 8.50,
            'discount_percentage' => 0,
            'discount_amount'     => 0,
            'tax_id'              => $this->tax->id,
            'tax_name'            => 'IVA 10%',
            'tax_rate'            => 10,
            'tax_base'            => 102,
            'tax_amount'          => 10.2,
            'subtotal'            => 102,
            'total'               => 112.2,
        ]));

        // Editar: bajar de 12 a 8
        Livewire::test(Edit::class, ['invoice' => $invoice])
            ->set('items', [$this->wineItemData($lot->id, qty: 8, available: 100)])
            ->call('update')
            ->assertHasNoErrors();

        $lot->refresh();
        $this->assertEquals(92, (int) $lot->available_quantity, 'available_quantity debe ser 100 - 8 = 92');
        $this->assertEquals(8, (int) $lot->reserved_quantity, 'reserved_quantity debe ser 8');
    }

    public function test_update_wine_lot_item_increasing_quantity(): void
    {
        $lot     = $this->seedProductLot(available: 80, reserved: 20);
        $invoice = $this->makeInvoice();

        InvoiceItem::withoutEvents(fn () => $invoice->items()->create([
            'wine_lot_id'         => $lot->id,
            'harvest_id'          => null,
            'concept_type'        => 'wine',
            'name'                => 'Tempranillo Reserva 2021',
            'quantity'            => 20,
            'unit'                => 'botella',
            'unit_price'          => 8.50,
            'discount_percentage' => 0,
            'discount_amount'     => 0,
            'tax_id'              => $this->tax->id,
            'tax_name'            => 'IVA 10%',
            'tax_rate'            => 10,
            'tax_base'            => 170,
            'tax_amount'          => 17,
            'subtotal'            => 170,
            'total'               => 187,
        ]));

        // available total = 80 + 20 = 100. Editamos a 30.
        Livewire::test(Edit::class, ['invoice' => $invoice])
            ->set('items', [$this->wineItemData($lot->id, qty: 30, available: 100)])
            ->call('update')
            ->assertHasNoErrors();

        $lot->refresh();
        $this->assertEquals(70, (int) $lot->available_quantity, 'available debe ser 100 - 30 = 70');
        $this->assertEquals(30, (int) $lot->reserved_quantity);
    }

    // ── Factura bloqueada ─────────────────────────────────────────────────────

    public function test_locked_invoice_cannot_be_updated(): void
    {
        $lot     = $this->seedProductLot(available: 88, reserved: 12);
        $invoice = $this->makeInvoice();
        $invoice->update(['delivery_status' => 'delivered']);

        InvoiceItem::withoutEvents(fn () => $invoice->items()->create([
            'wine_lot_id'         => $lot->id,
            'harvest_id'          => null,
            'concept_type'        => 'wine',
            'name'                => 'Tempranillo Reserva 2021',
            'quantity'            => 12,
            'unit'                => 'botella',
            'unit_price'          => 8.50,
            'discount_percentage' => 0,
            'discount_amount'     => 0,
            'tax_id'              => $this->tax->id,
            'tax_name'            => 'IVA 10%',
            'tax_rate'            => 10,
            'tax_base'            => 102,
            'tax_amount'          => 10.2,
            'subtotal'            => 102,
            'total'               => 112.2,
        ]));

        Livewire::test(Edit::class, ['invoice' => $invoice])
            ->set('items', [$this->wineItemData($lot->id, qty: 5, available: 100)])
            ->call('update');

        // El lote no debe haber cambiado (factura bloqueada)
        $lot->refresh();
        $this->assertEquals(88, (int) $lot->available_quantity, 'Lote no debe cambiar con factura bloqueada');
        $this->assertEquals(12, (int) $lot->reserved_quantity);
    }

    // ── Mixto: cosecha + vino ─────────────────────────────────────────────────

    public function test_update_mixed_invoice_reverses_and_rereserves_both_stock_types(): void
    {
        $harvest = $this->makeHarvest(500);
        $lot     = $this->seedProductLot(available: 88, reserved: 12);
        $invoice = $this->makeInvoice();

        $this->seedHarvestItem($invoice, $harvest, reserved: 200);

        InvoiceItem::withoutEvents(fn () => $invoice->items()->create([
            'wine_lot_id'         => $lot->id,
            'harvest_id'          => null,
            'concept_type'        => 'wine',
            'name'                => 'Tempranillo Reserva 2021',
            'quantity'            => 12,
            'unit'                => 'botella',
            'unit_price'          => 8.50,
            'discount_percentage' => 0,
            'discount_amount'     => 0,
            'tax_id'              => $this->tax->id,
            'tax_name'            => 'IVA 10%',
            'tax_rate'            => 10,
            'tax_base'            => 102,
            'tax_amount'          => 10.2,
            'subtotal'            => 102,
            'total'               => 112.2,
        ]));

        // Editar: cosecha 200→100, vino 12→6
        Livewire::test(Edit::class, ['invoice' => $invoice])
            ->set('items', [
                $this->harvestItemData($harvest->id, qty: 100, available: 300),
                $this->wineItemData($lot->id, qty: 6, available: 100),
            ])
            ->call('update')
            ->assertHasNoErrors();

        // Cosecha: unreserve 200, reserve 100 → available=400, reserved=100
        $latestHarvestStock = HarvestStock::where('harvest_id', $harvest->id)->latest('id')->first();
        $this->assertEquals('reserve', $latestHarvestStock->movement_type);
        $this->assertEquals(400.0, (float) $latestHarvestStock->available_qty);
        $this->assertEquals(100.0, (float) $latestHarvestStock->reserved_qty);

        // Vino: cancel 12, reserve 6 → available=94, reserved=6
        $lot->refresh();
        $this->assertEquals(94, (int) $lot->available_quantity, 'available debe ser 100 - 6 = 94');
        $this->assertEquals(6, (int) $lot->reserved_quantity);
    }
}
