<?php

namespace Tests\Feature\Viticulturist\ResidueManagements;

use App\Livewire\Viticulturist\ResidueManagements\Create;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\UnitSeeder::class);
    }

    public function test_can_create_residue_management(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('practice_type', 'incorporation')
            ->set('material_type', 'pruning_wood')
            ->set('date', '2024-03-15')
            ->set('quantity_unit', '')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.residue-managements.index'));

        $this->assertDatabaseHas('residue_managements', [
            'viticulturist_id' => $viticulturist->id,
            'practice_type' => 'incorporation',
            'material_type' => 'pruning_wood',
            'active' => true,
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('practice_type', '')
            ->set('material_type', '')
            ->set('date', '')
            ->set('quantity_unit', '')
            ->call('save')
            ->assertHasErrors(['practice_type', 'material_type', 'date']);
    }

    public function test_burning_requires_justification(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('practice_type', 'burning')
            ->set('material_type', 'pruning_wood')
            ->set('date', '2024-03-15')
            ->set('justification', '')
            ->set('quantity_unit', '')
            ->call('save')
            ->assertHasErrors(['justification']);
    }

    public function test_burning_justification_min_length(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('practice_type', 'burning')
            ->set('material_type', 'pruning_wood')
            ->set('date', '2024-03-15')
            ->set('justification', 'corto')
            ->set('quantity_unit', '')
            ->call('save')
            ->assertHasErrors(['justification']);
    }

    public function test_non_burning_justification_not_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('practice_type', 'composting')
            ->set('material_type', 'pruning_wood')
            ->set('date', '2024-03-15')
            ->set('justification', '')
            ->set('quantity_unit', '')
            ->call('save')
            ->assertHasNoErrors(['justification']);
    }

    public function test_justification_not_saved_for_non_burning(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('practice_type', 'composting')
            ->set('material_type', 'pruning_wood')
            ->set('date', '2024-03-15')
            ->set('justification', 'Justificación que no debe guardarse')
            ->set('quantity_unit', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('residue_managements', [
            'viticulturist_id' => $viticulturist->id,
            'practice_type' => 'composting',
            'justification' => null,
        ]);
    }
}
