<?php

namespace Tests\Feature\Viticulturist\FieldEquipment;

use App\Livewire\Viticulturist\FieldEquipment\Index;
use App\Models\FieldEquipment;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    private function makeEquipment(int $viticulturistId, string $name = 'Atomizador Test'): FieldEquipment
    {
        return FieldEquipment::create([
            'viticulturist_id' => $viticulturistId,
            'name'             => $name,
            'equipment_type'   => 'sprayer',
            'active'           => true,
        ]);
    }

    public function test_index_shows_active_equipment(): void
    {
        $viticulturist = $this->makeViticulturist();
        $equipment     = $this->makeEquipment($viticulturist->id, 'Mi Atomizador');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertSee('Mi Atomizador');
    }

    public function test_deactivate_sets_active_to_false(): void
    {
        $viticulturist = $this->makeViticulturist();
        $equipment     = $this->makeEquipment($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('deactivate', $equipment->id);

        $this->assertDatabaseHas('field_equipment', [
            'id'     => $equipment->id,
            'active' => false,
        ]);
    }

    public function test_deactivated_equipment_disappears_from_list(): void
    {
        $viticulturist = $this->makeViticulturist();
        $equipment     = $this->makeEquipment($viticulturist->id, 'Equipo a Dar de Baja');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('deactivate', $equipment->id)
            ->assertDontSee('Equipo a Dar de Baja');
    }

    public function test_cannot_deactivate_other_viticulturists_equipment(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeOtherViticulturist();
        $equipment     = $this->makeEquipment($viticulturist->id);

        $this->actingAs($other);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('deactivate', $equipment->id);
    }

    public function test_equipment_from_other_viticulturist_not_visible(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeOtherViticulturist();
        $equipment     = $this->makeEquipment($viticulturist->id, 'Equipo Ajeno');

        $this->actingAs($other);

        Livewire::test(Index::class)
            ->assertDontSee('Equipo Ajeno');
    }
}
