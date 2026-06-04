<?php

namespace Tests\Feature\Viticulturist\FieldEquipment;

use App\Livewire\Viticulturist\FieldEquipment\Create;
use App\Models\FieldEquipment;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_can_create_equipment_with_required_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', 'Atomizador 2000')
            ->set('equipment_type', 'sprayer')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.field-equipment.index'));

        $this->assertDatabaseHas('field_equipment', [
            'viticulturist_id' => $viticulturist->id,
            'name' => 'Atomizador 2000',
            'equipment_type' => 'sprayer',
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', '')
            ->set('equipment_type', '')
            ->call('save')
            ->assertHasErrors(['name', 'equipment_type']);
    }

    public function test_invalid_equipment_type_fails_validation(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', 'Máquina Desconocida')
            ->set('equipment_type', 'tipo_invalido')
            ->call('save')
            ->assertHasErrors(['equipment_type']);
    }

    public function test_all_valid_equipment_types_are_accepted(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        foreach (array_keys(FieldEquipment::TYPES) as $type) {
            Livewire::test(Create::class)
                ->set('name', "Equipo {$type}")
                ->set('equipment_type', $type)
                ->call('save')
                ->assertHasNoErrors(['equipment_type']);
        }
    }

    public function test_next_inspection_must_be_after_last_inspection(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', 'Atomizador Test')
            ->set('equipment_type', 'sprayer')
            ->set('last_inspection_date', '2024-06-01')
            ->set('next_inspection_date', '2024-05-01')
            ->call('save')
            ->assertHasErrors(['next_inspection_date']);
    }

    public function test_optional_fields_are_saved(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', 'Pulverizador Pro')
            ->set('equipment_type', 'sprayer')
            ->set('registration_number', 'REG-2024-001')
            ->set('last_inspection_date', '2024-01-15')
            ->set('next_inspection_date', '2025-01-15')
            ->set('inspection_entity', 'ATRIA Madrid')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('field_equipment', [
            'viticulturist_id' => $viticulturist->id,
            'registration_number' => 'REG-2024-001',
            'inspection_entity' => 'ATRIA Madrid',
        ]);
    }
}
