<?php

namespace Tests\Feature\Producer\Invoices;

use App\Livewire\Producer\Invoices\Create;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Container;
use App\Models\GrapeVariety;
use App\Models\Harvest;
use App\Models\HarvestStock;
use App\Models\Invoice;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\ProductLot;
use App\Models\Tax;
use App\Services\ContainerStockService;
use Livewire\Livewire;
use Tests\Feature\ProducerTestCase;

/**
 * Tests para la creación de albaranes mixtos del producer
 * (cosechas + lotes de vino en el mismo documento).
 *
 * Invariantes:
 *   - Un ítem de cosecha reserva stock en HarvestStock.
 *   - Un ítem de lote de vino decremente available_quantity (reserva).
 *   - Ambos tipos pueden coexistir en el mismo albarán.
 *   - El productor sólo puede facturar sus propias cosechas y lotes.
 */
class MixedInvoiceCreateTest extends ProducerTestCase
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
            'name'             => 'Parcela Producer Test',
            'reference'        => 'PT-001',
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
            'company_name' => 'Restaurante Producer Test S.L.',
            'email'        => 'cliente-producer@test.com',
            'active'       => true,
        ]);

        $this->address = ClientAddress::create([
            'client_id'  => $this->client->id,
            'first_name' => 'Test',
            'address'    => 'Calle Viñedo 1',
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
     * Crea una cosecha de cuaderno (activity_id set, winery_id null) con su
     * HarvestStock inicial, lista para ser facturada.
     */
    private function makeNotebookHarvest(float $weight = 500): Harvest
    {
        $campaign = Campaign::getOrCreateActiveForYear($this->producer->id, now()->year);

        $activity = AgriculturalActivity::create([
            'plot_id'          => $this->plot->id,
            'viticulturist_id' => $this->producer->id,
            'campaign_id'      => $campaign->id,
            'activity_type'    => 'harvest',
            'activity_date'    => now()->toDateString(),
        ]);

        $harvest = Harvest::withoutEvents(fn () => Harvest::create([
            'activity_id'        => $activity->id,
            'plot_planting_id'   => $this->planting->id,
            'harvest_start_date' => now()->toDateString(),
            'total_weight'       => $weight,
            'status'             => 'active',
        ]));

        // Inicializar stock (ContainerStockService::initializeStock no aplica aquí
        // porque la cosecha no tiene container_id; usamos ensureInitialStock vía reserveStock)
        // → HarvestStock se auto-crea la primera vez que se reserva (ver ensureInitialStock)

        return $harvest;
    }

    private function makeProductLot(int $available = 100): ProductLot
    {
        return ProductLot::create([
            'user_id'            => $this->producer->id,
            'name'               => 'Tempranillo Reserva 2021',
            'available_quantity' => $available,
            'reserved_quantity'  => 0,
            'sold_quantity'      => 0,
            'unit'               => 'botellas',
            'price_per_unit'     => 8.50,
            'archived'           => false,
        ]);
    }

    private function harvestItem(int $harvestId, float $qty = 200): array
    {
        return [
            'harvest_id'          => $harvestId,
            'wine_lot_id'         => null,
            'concept_type'        => 'harvest',
            'name'                => 'Tempranillo - Parcela Producer Test',
            'description'         => '',
            'sku'                 => "HARV-{$harvestId}",
            'quantity'            => $qty,
            'unit'                => 'kg',
            'available_qty'       => 500,
            'unit_price'          => 0.45,
            'discount_percentage' => 0,
            'tax_id'              => $this->tax->id,
        ];
    }

    private function wineItem(int $lotId, float $qty = 6): array
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
            'available_qty'       => 100,
            'unit_price'          => 8.50,
            'discount_percentage' => 0,
            'tax_id'              => $this->tax->id,
        ];
    }

    // ── Crear con cosecha ─────────────────────────────────────────────────────

    public function test_create_invoice_with_harvest_item_creates_harvest_stock(): void
    {
        $harvest = $this->makeNotebookHarvest(500);

        Livewire::test(Create::class)
            ->set('client_id', (string) $this->client->id)
            ->set('client_address_id', (string) $this->address->id)
            ->set('invoice_date', now()->toDateString())
            ->set('delivery_note_date', now()->toDateString())
            ->set('items', [$this->harvestItem($harvest->id, 200)])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoices', [
            'user_id'      => $this->producer->id,
            'invoice_type' => 'producer_sale',
            'status'       => 'draft',
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'harvest_id'   => $harvest->id,
            'concept_type' => 'harvest',
            'quantity'     => 200,
        ]);

        // El stock de cosecha debe tener una entrada 'reserve'
        $this->assertDatabaseHas('harvest_stocks', [
            'harvest_id'    => $harvest->id,
            'movement_type' => 'reserve',
        ]);
    }

    public function test_create_invoice_with_harvest_item_reserves_correct_quantity(): void
    {
        $harvest = $this->makeNotebookHarvest(500);

        Livewire::test(Create::class)
            ->set('client_id', (string) $this->client->id)
            ->set('client_address_id', (string) $this->address->id)
            ->set('invoice_date', now()->toDateString())
            ->set('delivery_note_date', now()->toDateString())
            ->set('items', [$this->harvestItem($harvest->id, 200)])
            ->call('save')
            ->assertHasNoErrors();

        // El último HarvestStock debe reflejar la reserva
        $latest = HarvestStock::where('harvest_id', $harvest->id)->latest('id')->first();
        $this->assertNotNull($latest);
        $this->assertEquals(300.0, (float) $latest->available_qty, 'available_qty debe ser 500 - 200 = 300');
        $this->assertEquals(200.0, (float) $latest->reserved_qty, 'reserved_qty debe ser 200');
    }

    // ── Crear con lote de vino ────────────────────────────────────────────────

    public function test_create_invoice_with_wine_lot_item_decrements_available_quantity(): void
    {
        $lot = $this->makeProductLot(100);

        Livewire::test(Create::class)
            ->set('client_id', (string) $this->client->id)
            ->set('client_address_id', (string) $this->address->id)
            ->set('invoice_date', now()->toDateString())
            ->set('delivery_note_date', now()->toDateString())
            ->set('items', [$this->wineItem($lot->id, 12)])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoice_items', [
            'wine_lot_id'  => $lot->id,
            'concept_type' => 'wine',
            'quantity'     => 12,
        ]);

        $lot->refresh();
        $this->assertEquals(88, (int) $lot->available_quantity, 'available_quantity debe ser 100 - 12 = 88');
        $this->assertEquals(12, (int) $lot->reserved_quantity, 'reserved_quantity debe ser 12');
    }

    // ── Crear mixto (cosecha + vino) ──────────────────────────────────────────

    public function test_create_mixed_invoice_with_harvest_and_wine_items(): void
    {
        $harvest = $this->makeNotebookHarvest(500);
        $lot     = $this->makeProductLot(50);

        Livewire::test(Create::class)
            ->set('client_id', (string) $this->client->id)
            ->set('client_address_id', (string) $this->address->id)
            ->set('invoice_date', now()->toDateString())
            ->set('delivery_note_date', now()->toDateString())
            ->set('items', [
                $this->harvestItem($harvest->id, 100),
                $this->wineItem($lot->id, 6),
            ])
            ->call('save')
            ->assertHasNoErrors();

        // Cosecha reservada
        $this->assertDatabaseHas('harvest_stocks', [
            'harvest_id'    => $harvest->id,
            'movement_type' => 'reserve',
        ]);

        // Lote de vino reservado
        $lot->refresh();
        $this->assertEquals(44, (int) $lot->available_quantity, 'available_quantity debe ser 50 - 6 = 44');
        $this->assertEquals(6, (int) $lot->reserved_quantity);
    }

    // ── Validaciones ──────────────────────────────────────────────────────────

    public function test_create_requires_client(): void
    {
        $lot = $this->makeProductLot(50);

        Livewire::test(Create::class)
            ->set('client_id', '')
            ->set('client_address_id', (string) $this->address->id)
            ->set('invoice_date', now()->toDateString())
            ->set('delivery_note_date', now()->toDateString())
            ->set('items', [$this->wineItem($lot->id, 6)])
            ->call('save')
            ->assertHasErrors(['client_id']);
    }

    public function test_create_requires_at_least_one_item(): void
    {
        Livewire::test(Create::class)
            ->set('client_id', (string) $this->client->id)
            ->set('client_address_id', (string) $this->address->id)
            ->set('invoice_date', now()->toDateString())
            ->set('delivery_note_date', now()->toDateString())
            ->set('items', [])
            ->call('save')
            ->assertHasErrors(['items']);
    }

    // ── Aislamiento: producer no puede facturar recursos ajenos ──────────────

    public function test_producer_cannot_use_another_producers_lot(): void
    {
        $otherProducer = $this->makeOtherProducer();
        $foreignLot    = ProductLot::create([
            'user_id'            => $otherProducer->id,
            'name'               => 'Lote Ajeno',
            'available_quantity' => 100,
            'reserved_quantity'  => 0,
            'sold_quantity'      => 0,
            'unit'               => 'botellas',
            'archived'           => false,
        ]);

        Livewire::test(Create::class)
            ->set('client_id', (string) $this->client->id)
            ->set('client_address_id', (string) $this->address->id)
            ->set('invoice_date', now()->toDateString())
            ->set('delivery_note_date', now()->toDateString())
            ->set('items', [$this->wineItem($foreignLot->id, 6)])
            ->call('save');

        // El lote ajeno NO debe haber sido modificado
        $foreignLot->refresh();
        $this->assertEquals(100, (int) $foreignLot->available_quantity, 'El lote ajeno no debe ser tocado');
    }

    public function test_producer_cannot_invoice_another_viticulturists_harvest(): void
    {
        $foreignHarvest = $this->makeForeignNotebookHarvest($this->makeOtherProducer(), 500);

        // El producer autenticado inyecta el harvest_id ajeno en el estado del cliente.
        Livewire::test(Create::class)
            ->set('client_id', (string) $this->client->id)
            ->set('client_address_id', (string) $this->address->id)
            ->set('invoice_date', now()->toDateString())
            ->set('delivery_note_date', now()->toDateString())
            ->set('items', [$this->harvestItem($foreignHarvest->id, 200)])
            ->call('save');

        // La transacción debe revertir: ni factura, ni ítem, ni reserva de stock sobre la cosecha ajena.
        $this->assertDatabaseMissing('invoice_items', [
            'harvest_id' => $foreignHarvest->id,
        ]);
        $this->assertDatabaseMissing('harvest_stocks', [
            'harvest_id'    => $foreignHarvest->id,
            'movement_type' => 'reserve',
        ]);
        $this->assertDatabaseMissing('invoices', [
            'user_id'      => $this->producer->id,
            'invoice_type' => 'producer_sale',
        ]);
    }

    /**
     * Cosecha de cuaderno perteneciente a OTRO viticultor (su propia parcela,
     * plantación, campaña y actividad). Sirve para probar el aislamiento.
     */
    private function makeForeignNotebookHarvest(\App\Models\User $owner, float $weight = 500): Harvest
    {
        $grapeVariety = GrapeVariety::firstOrCreate(
            ['code' => 'TEMP'],
            ['name' => 'Tempranillo', 'color' => 'red']
        );

        $plot = Plot::create([
            'viticulturist_id' => $owner->id,
            'name'             => 'Parcela Ajena',
            'reference'        => 'PA-999',
            'area'             => 2.0,
            'active'           => true,
        ]);

        $planting = PlotPlanting::create([
            'plot_id'          => $plot->id,
            'grape_variety_id' => $grapeVariety->id,
            'area_planted'     => 2.0,
            'planting_year'    => now()->year - 5,
            'status'           => 'active',
        ]);

        $campaign = Campaign::getOrCreateActiveForYear($owner->id, now()->year);

        $activity = AgriculturalActivity::create([
            'plot_id'          => $plot->id,
            'viticulturist_id' => $owner->id,
            'campaign_id'      => $campaign->id,
            'activity_type'    => 'harvest',
            'activity_date'    => now()->toDateString(),
        ]);

        return Harvest::withoutEvents(fn () => Harvest::create([
            'activity_id'        => $activity->id,
            'plot_planting_id'   => $planting->id,
            'harvest_start_date' => now()->toDateString(),
            'total_weight'       => $weight,
            'status'             => 'active',
        ]));
    }
}
