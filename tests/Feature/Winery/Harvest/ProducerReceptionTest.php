<?php

namespace Tests\Feature\Winery\Harvest;

use App\Livewire\Winery\Harvest\Reception\Assign;
use App\Livewire\Winery\Harvest\Reception\Create;
use App\Livewire\Winery\Harvest\Reception\Edit;
use App\Models\Campaign;
use App\Models\Container;
use App\Models\GrapeReceptionBatch;
use App\Models\GrapeVariety;
use App\Models\Harvest;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\ProducerTestCase;

/**
 * Producer usa los mismos componentes Livewire que Winery
 * (App\Livewire\Winery\Harvest\Reception\*).
 *
 * El producer puede hacer auto-recepción (viticulturist_id = producer->id)
 * sin necesidad de un WineryViticulturist link.
 *
 * La lógica de negocio (cálculos, batches, deliveries) está cubierta
 * exhaustivamente en CreateReceptionTest / EditReceptionTest / AssignReceptionTest
 * (WineryTestCase). Aquí se verifica:
 *  - Los componentes funcionan cuando el actor es producer.
 *  - El aislamiento de datos: container_id y harvest ownership respetan user_id.
 */
class ProducerReceptionTest extends ProducerTestCase
{
    private User $producer;
    private Plot $plot;
    private PlotPlanting $planting;
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->producer = $this->makeProducer();
        $this->actingAs($this->producer);

        $grapeVariety = GrapeVariety::firstOrCreate(
            ['code' => 'TEMP'],
            ['name' => 'Tempranillo', 'color' => 'red']
        );

        // Producer auto-recepción: la parcela le pertenece a él mismo como viticultor
        $this->plot = Plot::create([
            'viticulturist_id' => $this->producer->id,
            'name'             => 'Parcela Productor',
            'reference'        => 'PROD-001',
            'area'             => 5.0,
            'active'           => true,
        ]);

        $this->planting = PlotPlanting::create([
            'plot_id'          => $this->plot->id,
            'grape_variety_id' => $grapeVariety->id,
            'area_planted'     => 4.0,
            'planting_year'    => now()->year - 5,
            'status'           => 'active',
        ]);

        $this->container = Container::create([
            'user_id'       => $this->producer->id,
            'name'          => 'Depósito Productor',
            'capacity'      => 5000,
            'used_capacity' => 0,
            'archived'      => false,
            'unit'          => 'kg',
        ]);
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function makeProducerReception(array $attrs = []): Harvest
    {
        $campaign = Campaign::getOrCreateActiveForYear($this->producer->id, 2024);

        $batch = GrapeReceptionBatch::firstOrCreate(
            [
                'winery_id'        => $this->producer->id,
                'plot_planting_id' => $this->planting->id,
                'campaign_id'      => $campaign->id,
            ],
            [
                'viticulturist_id' => $this->producer->id,
                'vintage_year'     => 2024,
                'total_weight_kg'  => 0,
                'status'           => 'open',
            ]
        );

        return Harvest::create(array_merge([
            'winery_id'          => $this->producer->id,
            'batch_id'           => $batch->id,
            'plot_planting_id'   => $this->planting->id,
            'vintage'            => 2024,
            'total_weight'       => 1000,
            'harvest_start_date' => '2024-09-15',
            'status'             => 'active',
            'container_id'       => $this->container->id,
        ], $attrs));
    }

    // ── Rutas accesibles ──────────────────────────────────────────────────────

    public function test_producer_can_access_grape_reception_index(): void
    {
        $this->get(route('producer.grape-reception.index'))->assertOk();
    }

    public function test_producer_can_access_grape_reception_create(): void
    {
        $this->get(route('producer.grape-reception.create'))->assertOk();
    }

    // ── Create funciona para producer (auto-recepción) ────────────────────────

    public function test_producer_can_create_self_reception(): void
    {
        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->producer->id)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('vintage_year', 2024)
            ->set('harvest_start_date', '2024-09-15')
            ->set('total_weight', '800')
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('harvests', [
            'winery_id'    => $this->producer->id,
            'total_weight' => 800,
            'status'       => 'active',
        ]);
    }

    // ── Seguridad: container_id ───────────────────────────────────────────────

    public function test_producer_cannot_use_container_from_another_user(): void
    {
        $otherProducer    = $this->makeOtherProducer();
        $foreignContainer = Container::create([
            'user_id'       => $otherProducer->id,
            'name'          => 'Cuba Ajena',
            'capacity'      => 5000,
            'used_capacity' => 0,
            'archived'      => false,
            'unit'          => 'kg',
        ]);

        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->producer->id)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('vintage_year', 2024)
            ->set('harvest_start_date', '2024-09-15')
            ->set('total_weight', '500')
            ->set('container_id', (string) $foreignContainer->id)
            ->call('save')
            ->assertHasErrors(['container_id']);

        $this->assertDatabaseMissing('harvests', ['winery_id' => $this->producer->id]);
    }

    public function test_producer_cannot_use_litros_container_for_reception(): void
    {
        $litrosContainer = Container::create([
            'user_id'       => $this->producer->id,
            'name'          => 'Depósito Litros',
            'capacity'      => 10000,
            'used_capacity' => 0,
            'archived'      => false,
            'unit'          => 'litros',
        ]);

        Livewire::test(Create::class)
            ->set('viticulturist_id', (string) $this->producer->id)
            ->set('plot_id', (string) $this->plot->id)
            ->set('plot_planting_id', (string) $this->planting->id)
            ->set('vintage_year', 2024)
            ->set('harvest_start_date', '2024-09-15')
            ->set('total_weight', '500')
            ->set('container_id', (string) $litrosContainer->id)
            ->call('save')
            ->assertHasErrors(['container_id']);
    }

    // ── Aislamiento: acceso a recepciones ajenas ──────────────────────────────

    public function test_producer_cannot_access_another_producers_reception(): void
    {
        $otherProducer = $this->makeOtherProducer();
        $reception     = $this->makeProducerReception(['winery_id' => $otherProducer->id]);

        $this->get(route('producer.grape-reception.show', $reception))
            ->assertForbidden();
    }

    // ── Edit funciona para producer ───────────────────────────────────────────

    public function test_producer_can_edit_own_reception(): void
    {
        $reception = $this->makeProducerReception();
        $this->container->refresh();

        Livewire::test(Edit::class, ['harvest' => $reception])
            ->set('total_weight', '1200')
            ->set('harvest_start_date', '2024-09-20')
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('harvests', [
            'id'           => $reception->id,
            'total_weight' => 1200,
        ]);
    }

    public function test_producer_edit_rejects_foreign_container(): void
    {
        $reception     = $this->makeProducerReception();
        $otherProducer = $this->makeOtherProducer();
        $foreignContainer = Container::create([
            'user_id'       => $otherProducer->id,
            'name'          => 'Cuba Ajena Edit',
            'capacity'      => 5000,
            'used_capacity' => 0,
            'archived'      => false,
            'unit'          => 'kg',
        ]);
        $this->container->refresh();

        Livewire::test(Edit::class, ['harvest' => $reception])
            ->set('container_id', (string) $foreignContainer->id)
            ->call('save')
            ->assertHasErrors(['container_id']);
    }

    public function test_producer_cannot_edit_another_producers_reception(): void
    {
        $otherProducer = $this->makeOtherProducer();
        $reception     = $this->makeProducerReception(['winery_id' => $otherProducer->id]);

        Livewire::test(Edit::class, ['harvest' => $reception])
            ->assertStatus(403);
    }

    // ── Assign funciona para producer ─────────────────────────────────────────

    public function test_producer_can_assign_reception_to_new_container(): void
    {
        $reception = $this->makeProducerReception();
        $this->container->refresh();

        $newContainer = Container::create([
            'user_id'       => $this->producer->id,
            'name'          => 'Depósito Nuevo Productor',
            'capacity'      => 3000,
            'used_capacity' => 0,
            'archived'      => false,
            'unit'          => 'kg',
        ]);

        Livewire::test(Assign::class, ['harvest' => $reception])
            ->set('container_id', (string) $newContainer->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('harvests', [
            'id'           => $reception->id,
            'container_id' => $newContainer->id,
        ]);
    }

    public function test_producer_assign_rejects_foreign_container(): void
    {
        $reception     = $this->makeProducerReception();
        $this->container->refresh();
        $otherProducer = $this->makeOtherProducer();
        $foreignContainer = Container::create([
            'user_id'       => $otherProducer->id,
            'name'          => 'Cuba Ajena Assign',
            'capacity'      => 5000,
            'used_capacity' => 0,
            'archived'      => false,
            'unit'          => 'kg',
        ]);

        Livewire::test(Assign::class, ['harvest' => $reception])
            ->set('container_id', (string) $foreignContainer->id)
            ->call('save')
            ->assertHasErrors(['container_id']);
    }
}
