<?php

namespace Tests\Feature\Viticulturist\Personal;

use App\Livewire\Viticulturist\Personal\Create;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_viticulturist_can_create_crew(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', 'Cuadrilla Vendimiadores')
            ->set('description', 'Equipo de vendimia')
            ->call('save')
            ->assertRedirect(route('viticulturist.personal.index'));

        $this->assertDatabaseHas('crews', [
            'viticulturist_id' => $viticulturist->id,
            'name' => 'Cuadrilla Vendimiadores',
        ]);
    }

    public function test_name_is_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_cannot_link_to_unassigned_winery(): void
    {
        $viticulturist = $this->makeViticulturist();
        $unrelatedWinery = User::factory()->create(['role' => 'winery']);
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', 'Test Cuadrilla')
            ->set('winery_id', $unrelatedWinery->id)
            ->call('save')
            ->assertHasErrors(['winery_id']);
    }

    public function test_can_link_to_assigned_winery(): void
    {
        $viticulturist = $this->makeViticulturist();
        $winery = $viticulturist->wineries->first();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', 'Con Bodega')
            ->set('winery_id', $winery->id)
            ->call('save')
            ->assertRedirect(route('viticulturist.personal.index'));

        $this->assertDatabaseHas('crews', [
            'viticulturist_id' => $viticulturist->id,
            'name' => 'Con Bodega',
            'winery_id' => $winery->id,
        ]);
    }

    public function test_mount_auto_selects_single_winery(): void
    {
        $viticulturist = $this->makeViticulturist();
        $winery = $viticulturist->wineries->first();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->assertSet('winery_id', $winery->id);
    }

    public function test_winery_id_is_null_when_empty(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        // Ensure winery_id is stored as NULL when not provided
        Livewire::test(Create::class)
            ->set('name', 'Sin Bodega')
            ->set('winery_id', '')
            ->call('save');

        $this->assertDatabaseHas('crews', [
            'viticulturist_id' => $viticulturist->id,
            'name' => 'Sin Bodega',
            'winery_id' => null,
        ]);
    }
}
