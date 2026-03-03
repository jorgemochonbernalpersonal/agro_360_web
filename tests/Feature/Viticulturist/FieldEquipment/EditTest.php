<?php

namespace Tests\Feature\Viticulturist\FieldEquipment;

use App\Livewire\Viticulturist\FieldEquipment\Edit;
use App\Models\FieldEquipment;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    private function makeEquipment(int $viticulturistId): FieldEquipment
    {
        return FieldEquipment::create([
            'viticulturist_id' => $viticulturistId,
            'name'             => 'Atomizador Original',
            'equipment_type'   => 'sprayer',
            'active'           => true,
        ]);
    }

    public function test_mount_fills_fields_from_model(): void
    {
        $viticulturist = $this->makeViticulturist();
        $equipment     = $this->makeEquipment($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['fieldEquipment' => $equipment])
            ->assertSet('name', 'Atomizador Original')
            ->assertSet('equipment_type', 'sprayer');
    }

    public function test_can_update_equipment(): void
    {
        $viticulturist = $this->makeViticulturist();
        $equipment     = $this->makeEquipment($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['fieldEquipment' => $equipment])
            ->set('name', 'Atomizador Actualizado')
            ->set('equipment_type', 'spreader')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.field-equipment.index'));

        $this->assertDatabaseHas('field_equipment', [
            'id'             => $equipment->id,
            'name'           => 'Atomizador Actualizado',
            'equipment_type' => 'spreader',
        ]);
    }

    public function test_validates_required_fields_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $equipment     = $this->makeEquipment($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['fieldEquipment' => $equipment])
            ->set('name', '')
            ->set('equipment_type', '')
            ->call('save')
            ->assertHasErrors(['name', 'equipment_type']);
    }

    public function test_next_inspection_must_be_after_last_inspection(): void
    {
        $viticulturist = $this->makeViticulturist();
        $equipment     = $this->makeEquipment($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['fieldEquipment' => $equipment])
            ->set('last_inspection_date', '2024-06-01')
            ->set('next_inspection_date', '2024-05-01')
            ->call('save')
            ->assertHasErrors(['next_inspection_date']);
    }

    public function test_cannot_edit_other_viticulturists_equipment(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeViticulturist();
        $equipment     = $this->makeEquipment($viticulturist->id);

        $this->actingAs($other)
            ->get(route('viticulturist.field-equipment.edit', $equipment))
            ->assertStatus(403);
    }
}
