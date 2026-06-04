<?php

namespace Tests\Feature\Winery\Harvest;

use App\Livewire\Producer\Harvest\PromoteToReception;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Container;
use App\Models\GrapeVariety;
use App\Models\Harvest;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\ProducerTestCase;

/**
 * Flujo "Promover a recepción de bodega" del producer.
 *
 * El producer tiene una cosecha en su cuaderno de campo (activity_id set, winery_id null)
 * y la promueve a recepción oficial de bodega (winery_id set, notebook_harvest_id set).
 */
class PromoteToReceptionTest extends ProducerTestCase
{
    private User $producer;

    private Plot $plot;

    private PlotPlanting $planting;

    private Container $container;

    private Harvest $notebookHarvest;

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
            'name' => 'Parcela Producer',
            'reference' => 'PROD-001',
            'area' => 5.0,
            'active' => true,
        ]);

        $this->planting = PlotPlanting::create([
            'plot_id' => $this->plot->id,
            'grape_variety_id' => $grapeVariety->id,
            'area_planted' => 4.0,
            'planting_year' => now()->year - 5,
            'status' => 'active',
        ]);

        $this->container = Container::create([
            'user_id' => $this->producer->id,
            'name' => 'Depósito Bodega',
            'capacity' => 5000,
            'used_capacity' => 0,
            'archived' => false,
            'unit' => 'kg',
        ]);

        // Cosecha del cuaderno: activity_id set, winery_id null
        $campaign = Campaign::getOrCreateActiveForYear($this->producer->id, 2024);
        $activity = AgriculturalActivity::create([
            'plot_id' => $this->plot->id,
            'viticulturist_id' => $this->producer->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => '2024-09-15',
        ]);

        $this->notebookHarvest = Harvest::withoutEvents(fn () => Harvest::create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $this->planting->id,
            'harvest_start_date' => '2024-09-15',
            'total_weight' => 1000,
            'baume_degree' => 12.5,
            'brix_degree' => 22.0,
            'ph_level' => 3.4,
            'health_status' => 'sano',
            'status' => 'active',
        ]));
    }

    // ── Happy path ────────────────────────────────────────────────────────────

    public function test_can_promote_notebook_harvest_to_reception(): void
    {
        Livewire::test(PromoteToReception::class, ['notebookHarvest' => $this->notebookHarvest])
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        // Recepción creada con winery_id y notebook_harvest_id
        $this->assertDatabaseHas('harvests', [
            'notebook_harvest_id' => $this->notebookHarvest->id,
            'winery_id' => $this->producer->id,
            'plot_planting_id' => $this->planting->id,
            'total_weight' => 1000,
            'status' => 'active',
        ]);
    }

    public function test_mount_preloads_data_from_notebook_harvest(): void
    {
        Livewire::test(PromoteToReception::class, ['notebookHarvest' => $this->notebookHarvest])
            ->assertSet('total_weight', '1000.000')
            ->assertSet('harvest_start_date', '2024-09-15')
            ->assertSet('baume_degree', '12.500')
            ->assertSet('health_status', 'sano');
    }

    public function test_promotion_links_notebook_harvest_to_reception(): void
    {
        Livewire::test(PromoteToReception::class, ['notebookHarvest' => $this->notebookHarvest])
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        // El cuaderno ahora tiene una recepción vinculada
        $this->notebookHarvest->refresh();
        $this->assertNotNull($this->notebookHarvest->grapeReception);
        $this->assertEquals($this->producer->id, $this->notebookHarvest->grapeReception->winery_id);
    }

    public function test_promotion_increments_container_used_capacity(): void
    {
        Livewire::test(PromoteToReception::class, ['notebookHarvest' => $this->notebookHarvest])
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        // HarvestObserver → initializeStock()
        $this->assertDatabaseHas('containers', [
            'id' => $this->container->id,
            'used_capacity' => 1000,
        ]);
    }

    public function test_promotion_creates_batch(): void
    {
        Livewire::test(PromoteToReception::class, ['notebookHarvest' => $this->notebookHarvest])
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('grape_reception_batches', [
            'winery_id' => $this->producer->id,
            'plot_planting_id' => $this->planting->id,
            'total_weight_kg' => 1000,
        ]);
    }

    // ── Validaciones ──────────────────────────────────────────────────────────

    public function test_container_is_required(): void
    {
        Livewire::test(PromoteToReception::class, ['notebookHarvest' => $this->notebookHarvest])
            ->set('container_id', '')
            ->call('save')
            ->assertHasErrors(['container_id']);
    }

    public function test_container_from_other_user_is_rejected(): void
    {
        $otherProducer = $this->makeOtherProducer();
        $foreignContainer = Container::create([
            'user_id' => $otherProducer->id,
            'name' => 'Cuba Ajena',
            'capacity' => 5000,
            'used_capacity' => 0,
            'archived' => false,
            'unit' => 'kg',
        ]);

        Livewire::test(PromoteToReception::class, ['notebookHarvest' => $this->notebookHarvest])
            ->set('container_id', (string) $foreignContainer->id)
            ->call('save')
            ->assertHasErrors(['container_id']);
    }

    public function test_litros_container_is_rejected(): void
    {
        $litrosContainer = Container::create([
            'user_id' => $this->producer->id,
            'name' => 'Depósito Litros',
            'capacity' => 10000,
            'used_capacity' => 0,
            'archived' => false,
            'unit' => 'litros',
        ]);

        Livewire::test(PromoteToReception::class, ['notebookHarvest' => $this->notebookHarvest])
            ->set('container_id', (string) $litrosContainer->id)
            ->call('save')
            ->assertHasErrors(['container_id']);
    }

    public function test_container_capacity_exceeded_shows_error(): void
    {
        $smallContainer = Container::create([
            'user_id' => $this->producer->id,
            'name' => 'Cuba Pequeña',
            'capacity' => 500,
            'used_capacity' => 500,
            'archived' => false,
            'unit' => 'kg',
        ]);

        Livewire::test(PromoteToReception::class, ['notebookHarvest' => $this->notebookHarvest])
            ->set('container_id', (string) $smallContainer->id)
            ->call('save')
            ->assertHasErrors(['container_id']);
    }

    // ── Seguridad ─────────────────────────────────────────────────────────────

    public function test_non_producer_cannot_access_promote(): void
    {
        $winery = $this->makeWinery();
        $this->actingAs($winery);

        Livewire::test(PromoteToReception::class, ['notebookHarvest' => $this->notebookHarvest])
            ->assertStatus(403);
    }

    public function test_other_producer_cannot_promote_foreign_harvest(): void
    {
        $otherProducer = $this->makeOtherProducer();
        $this->actingAs($otherProducer);

        Livewire::test(PromoteToReception::class, ['notebookHarvest' => $this->notebookHarvest])
            ->assertStatus(403);
    }

    public function test_cannot_promote_twice(): void
    {
        // Primera promoción
        Livewire::test(PromoteToReception::class, ['notebookHarvest' => $this->notebookHarvest])
            ->set('container_id', (string) $this->container->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->container->refresh();

        // Segunda promoción → redirige a la recepción existente
        $secondContainer = Container::create([
            'user_id' => $this->producer->id,
            'name' => 'Depósito B',
            'capacity' => 5000,
            'used_capacity' => 0,
            'archived' => false,
            'unit' => 'kg',
        ]);

        Livewire::test(PromoteToReception::class, ['notebookHarvest' => $this->notebookHarvest->fresh()])
            ->assertRedirect();

        // Solo existe una recepción para este cuaderno
        $this->assertDatabaseCount('harvests', 2); // notebook + 1 reception
    }
}
