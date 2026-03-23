<?php

namespace Tests\Feature\Winery\Cellar;

use App\Livewire\Winery\Cellar\Containers\Maintenance\Create;
use App\Livewire\Winery\Cellar\Containers\Maintenance\Edit;
use App\Livewire\Winery\Cellar\Containers\Maintenance\Index;
use App\Models\Container;
use App\Models\ContainerMaintenance;
use App\Models\ContainerType;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

/**
 * Tests for Winery Container Maintenance module.
 *
 * Covers: Index renders, Create (happy path, validation),
 *         Edit (happy path, authorization), status transitions.
 */
class ContainerMaintenanceTest extends WineryTestCase
{
    private User      $winery;
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);

        $type = ContainerType::firstOrCreate(['name' => 'Depósito Inox']);

        $this->container = Container::create([
            'user_id'       => $this->winery->id,
            'name'          => 'Cuba Mantenimiento',
            'type_id'       => $type->id,
            'capacity'      => 5000,
            'unit'          => 'litros',
            'used_capacity' => 0,
            'archived'      => false,
        ]);
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function makeMaintenance(array $attrs = []): ContainerMaintenance
    {
        return ContainerMaintenance::create(array_merge([
            'container_id'     => $this->container->id,
            'maintenance_type' => 'cleaning',
            'maintenance_name' => 'Limpieza inicial',
            'scheduled_date'   => now()->toDateString(),
            'status'           => 'scheduled',
        ], $attrs));
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_index_renders(): void
    {
        $this->get(route('winery.containers.maintenance.index', $this->container))
            ->assertOk();
    }

    public function test_other_winery_cannot_view_maintenance(): void
    {
        $this->actingAs($this->makeOtherWinery())
            ->get(route('winery.containers.maintenance.index', $this->container))
            ->assertForbidden();
    }

    // ── Create — happy path ───────────────────────────────────────────────────

    public function test_can_create_maintenance(): void
    {
        Livewire::test(Create::class, ['container' => $this->container])
            ->set('maintenance_type', 'cleaning')
            ->set('maintenance_name', 'Limpieza programada')
            ->set('scheduled_date', now()->toDateString())
            ->set('status', 'scheduled')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('container_maintenances', [
            'container_id'     => $this->container->id,
            'maintenance_type' => 'cleaning',
            'maintenance_name' => 'Limpieza programada',
            'status'           => 'scheduled',
        ]);
    }

    // ── Create — validation ───────────────────────────────────────────────────

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class, ['container' => $this->container])
            ->set('maintenance_type', '')
            ->set('maintenance_name', '')
            ->set('scheduled_date', '')
            ->call('save')
            ->assertHasErrors(['maintenance_type', 'maintenance_name', 'scheduled_date']);
    }

    public function test_create_rejects_invalid_type(): void
    {
        Livewire::test(Create::class, ['container' => $this->container])
            ->set('maintenance_type', 'invalid_type')
            ->set('maintenance_name', 'Test')
            ->set('scheduled_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['maintenance_type']);
    }

    // ── Edit — happy path ─────────────────────────────────────────────────────

    public function test_can_edit_maintenance(): void
    {
        $maintenance = $this->makeMaintenance();

        Livewire::test(Edit::class, ['container' => $this->container, 'maintenance' => $maintenance])
            ->set('maintenance_name', 'Limpieza actualizada')
            ->set('status', 'completed')
            ->set('performed_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('container_maintenances', [
            'id'               => $maintenance->id,
            'maintenance_name' => 'Limpieza actualizada',
            'status'           => 'completed',
        ]);
    }

    // ── Edit — authorization ──────────────────────────────────────────────────

    public function test_other_winery_cannot_edit_maintenance(): void
    {
        $maintenance = $this->makeMaintenance();

        $this->actingAs($this->makeOtherWinery())
            ->get(route('winery.containers.maintenance.edit', [$this->container, $maintenance]))
            ->assertForbidden();
    }

    // ── Status transitions ────────────────────────────────────────────────────

    public function test_can_mark_maintenance_completed(): void
    {
        $maintenance = $this->makeMaintenance(['status' => 'scheduled']);

        Livewire::test(Index::class, ['container' => $this->container])
            ->call('markCompleted', $maintenance->id);

        $this->assertDatabaseHas('container_maintenances', [
            'id'     => $maintenance->id,
            'status' => 'completed',
        ]);
    }

    public function test_can_cancel_maintenance(): void
    {
        $maintenance = $this->makeMaintenance(['status' => 'scheduled']);

        Livewire::test(Index::class, ['container' => $this->container])
            ->call('cancel', $maintenance->id);

        $this->assertDatabaseHas('container_maintenances', [
            'id'     => $maintenance->id,
            'status' => 'cancelled',
        ]);
    }
}
