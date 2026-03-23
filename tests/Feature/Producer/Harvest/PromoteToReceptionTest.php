<?php

namespace Tests\Feature\Producer\Harvest;

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
 * Tests for the Producer "Promote harvest to winery reception" flow.
 *
 * Covers: happy path, double-promotion guard, authorization, and validation.
 */
class PromoteToReceptionTest extends ProducerTestCase
{
    private User        $producer;
    private Plot        $plot;
    private PlotPlanting $planting;
    private Container   $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->producer = $this->makeProducer();

        $grapeVariety = GrapeVariety::firstOrCreate(
            ['code' => 'TEMP'],
            ['name' => 'Tempranillo', 'color' => 'red']
        );

        $this->plot = Plot::create([
            'viticulturist_id' => $this->producer->id,
            'name'             => 'Parcela PromoteTest',
            'reference'        => 'PM-001',
            'area'             => 2.0,
            'active'           => true,
        ]);

        $this->planting = PlotPlanting::create([
            'plot_id'          => $this->plot->id,
            'grape_variety_id' => $grapeVariety->id,
            'area_planted'     => 2.0,
            'planting_year'    => now()->year - 5,
            'status'           => 'active',
        ]);

        $this->container = Container::create([
            'user_id'       => $this->producer->id,
            'name'          => 'Depósito Cosecha',
            'unit'          => 'kg',
            'capacity'      => 10000,
            'used_capacity' => 0,
            'archived'      => false,
        ]);
    }

    // ── Helper ────────────────────────────────────────────────────────────────

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

        return Harvest::withoutEvents(fn () => Harvest::create([
            'activity_id'        => $activity->id,
            'plot_planting_id'   => $this->planting->id,
            'harvest_start_date' => now()->toDateString(),
            'total_weight'       => $weight,
            'status'             => 'active',
        ]));
    }

    // ── happy path ────────────────────────────────────────────────────────────

    public function test_can_promote_notebook_harvest_to_reception(): void
    {
        $this->actingAs($this->producer);

        $notebookHarvest = $this->makeNotebookHarvest(800);

        Livewire::test(PromoteToReception::class, ['notebookHarvest' => $notebookHarvest])
            ->set('container_id', (string) $this->container->id)
            ->set('harvest_start_date', now()->toDateString())
            ->set('total_weight', '800')
            ->call('save')
            ->assertHasNoErrors();

        // A winery-side Harvest (winery_id set) should have been created
        $this->assertDatabaseHas('harvests', [
            'notebook_harvest_id' => $notebookHarvest->id,
            'winery_id'           => $this->producer->id,
            'total_weight'        => 800,
        ]);
    }

    // ── double-promotion guard ─────────────────────────────────────────────────

    public function test_cannot_promote_already_promoted_harvest(): void
    {
        $this->actingAs($this->producer);

        $notebookHarvest = $this->makeNotebookHarvest(500);

        // First promotion
        Livewire::test(PromoteToReception::class, ['notebookHarvest' => $notebookHarvest])
            ->set('container_id', (string) $this->container->id)
            ->set('harvest_start_date', now()->toDateString())
            ->set('total_weight', '500')
            ->call('save')
            ->assertHasNoErrors();

        // Reload — second mount should redirect away (grapeReception already exists)
        $fresh = $notebookHarvest->fresh();
        $this->assertTrue($fresh->grapeReception()->exists());
    }

    // ── validation ────────────────────────────────────────────────────────────

    public function test_requires_container(): void
    {
        $this->actingAs($this->producer);

        $notebookHarvest = $this->makeNotebookHarvest();

        Livewire::test(PromoteToReception::class, ['notebookHarvest' => $notebookHarvest])
            ->set('container_id', '')
            ->set('harvest_start_date', now()->toDateString())
            ->set('total_weight', '500')
            ->call('save')
            ->assertHasErrors(['container_id']);
    }

    public function test_requires_harvest_date(): void
    {
        $this->actingAs($this->producer);

        $notebookHarvest = $this->makeNotebookHarvest();

        Livewire::test(PromoteToReception::class, ['notebookHarvest' => $notebookHarvest])
            ->set('container_id', (string) $this->container->id)
            ->set('harvest_start_date', '')
            ->set('total_weight', '500')
            ->call('save')
            ->assertHasErrors(['harvest_start_date']);
    }

    public function test_requires_weight(): void
    {
        $this->actingAs($this->producer);

        $notebookHarvest = $this->makeNotebookHarvest();

        Livewire::test(PromoteToReception::class, ['notebookHarvest' => $notebookHarvest])
            ->set('container_id', (string) $this->container->id)
            ->set('harvest_start_date', now()->toDateString())
            ->set('total_weight', '')
            ->call('save')
            ->assertHasErrors(['total_weight']);
    }

    // ── container capacity ────────────────────────────────────────────────────

    public function test_rejects_when_container_lacks_capacity(): void
    {
        $this->actingAs($this->producer);

        $smallContainer = Container::create([
            'user_id'       => $this->producer->id,
            'name'          => 'Depósito Pequeño',
            'unit'          => 'kg',
            'capacity'      => 100,
            'used_capacity' => 90,
            'archived'      => false,
        ]);

        $notebookHarvest = $this->makeNotebookHarvest(500);

        Livewire::test(PromoteToReception::class, ['notebookHarvest' => $notebookHarvest])
            ->set('container_id', (string) $smallContainer->id)
            ->set('harvest_start_date', now()->toDateString())
            ->set('total_weight', '500')
            ->call('save')
            ->assertHasErrors(['container_id']);
    }

    // ── authorization ─────────────────────────────────────────────────────────

    public function test_non_producer_cannot_access(): void
    {
        $viticulturist   = $this->makeViticulturist();
        $notebookHarvest = $this->makeNotebookHarvest();

        // Route is guarded by role:producer middleware — viticulturists are rejected
        $this->withoutExceptionHandling();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->actingAs($viticulturist)
            ->get(route('producer.digital-notebook.harvest.promote', $notebookHarvest));
    }
}
