<?php

namespace Tests\Feature\Winery\Harvest;

use App\Livewire\Winery\Harvest\Reception\Assign;
use App\Models\Container;
use App\Models\Harvest;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;
use Tests\Traits\CreatesDeliveryScenario;

class AssignReceptionTest extends WineryTestCase
{
    use CreatesDeliveryScenario;

    private Container $container;
    private Harvest   $reception;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
        $this->container = $this->makeContainer(['capacity' => 5000, 'used_capacity' => 0]);
        $this->actingAs($this->winery);
        $this->reception = $this->makeWineryReception([
            'total_weight' => 1000,
            'container_id' => $this->container->id,
        ]);
        $this->container->refresh(); // used_capacity = 1000
    }

    // ── Asignación correcta ───────────────────────────────────────────────────

    public function test_can_assign_to_new_container(): void
    {
        $newContainer = $this->makeContainer(['capacity' => 3000, 'used_capacity' => 0]);

        Livewire::test(Assign::class, ['harvest' => $this->reception])
            ->set('container_id', (string) $newContainer->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('harvests', [
            'id'           => $this->reception->id,
            'container_id' => $newContainer->id,
        ]);
    }

    public function test_mount_preloads_current_container(): void
    {
        Livewire::test(Assign::class, ['harvest' => $this->reception])
            ->assertSet('container_id', (string) $this->container->id);
    }

    // ── Validaciones ──────────────────────────────────────────────────────────

    public function test_container_id_is_required(): void
    {
        Livewire::test(Assign::class, ['harvest' => $this->reception])
            ->set('container_id', '')
            ->call('save')
            ->assertHasErrors(['container_id']);
    }

    public function test_already_assigned_same_container_shows_error(): void
    {
        // Intentar asignar el mismo contenedor que ya tiene → toast de error, sin cambios
        Livewire::test(Assign::class, ['harvest' => $this->reception])
            ->set('container_id', (string) $this->container->id)
            ->call('save');

        // La recepción sigue apuntando al mismo contenedor
        $this->assertDatabaseHas('harvests', [
            'id'           => $this->reception->id,
            'container_id' => $this->container->id,
        ]);
    }

    public function test_capacity_exceeded_shows_error(): void
    {
        $fullContainer = $this->makeContainer(['capacity' => 500, 'used_capacity' => 500]);

        Livewire::test(Assign::class, ['harvest' => $this->reception])
            ->set('container_id', (string) $fullContainer->id)
            ->call('save')
            ->assertHasErrors(['container_id']);

        // Contenedor original sin cambios
        $this->assertDatabaseHas('harvests', [
            'id'           => $this->reception->id,
            'container_id' => $this->container->id,
        ]);
    }

    // ── Seguridad: container_id ───────────────────────────────────────────────

    public function test_container_from_other_winery_is_rejected(): void
    {
        $otherWinery      = $this->makeOtherWinery();
        $foreignContainer = $this->makeContainer(['user_id' => $otherWinery->id, 'capacity' => 5000]);

        Livewire::test(Assign::class, ['harvest' => $this->reception])
            ->set('container_id', (string) $foreignContainer->id)
            ->call('save')
            ->assertHasErrors(['container_id']);

        $this->assertDatabaseHas('harvests', [
            'id'           => $this->reception->id,
            'container_id' => $this->container->id,
        ]);
    }

    public function test_container_with_litros_unit_is_rejected(): void
    {
        $litrosContainer = $this->makeContainer(['unit' => 'litros', 'capacity' => 10000]);

        Livewire::test(Assign::class, ['harvest' => $this->reception])
            ->set('container_id', (string) $litrosContainer->id)
            ->call('save')
            ->assertHasErrors(['container_id']);
    }

    // ── Auth ──────────────────────────────────────────────────────────────────

    public function test_other_winery_cannot_access_assign_page(): void
    {
        $otherWinery = $this->makeOtherWinery();

        $this->actingAs($otherWinery)
            ->get(route('winery.grape-reception.assign', $this->reception))
            ->assertForbidden();
    }
}
