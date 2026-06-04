<?php

namespace Tests\Feature\Viticulturist\ResidueManagements;

use App\Livewire\Viticulturist\ResidueManagements\Edit;
use App\Models\Campaign;
use App\Models\ResidueManagement;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\UnitSeeder::class);
    }

    public function test_mount_fills_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $management = $this->makeManagement($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['residueManagement' => $management])
            ->assertSet('practice_type', 'incorporation')
            ->assertSet('material_type', 'pruning_wood')
            ->assertSet('date', '2024-03-15');
    }

    public function test_can_update_residue_management(): void
    {
        $viticulturist = $this->makeViticulturist();
        $management = $this->makeManagement($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['residueManagement' => $management])
            ->set('practice_type', 'composting')
            ->set('material_type', 'grape_marc')
            ->set('quantity_unit', '')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.residue-managements.index'));

        $this->assertDatabaseHas('residue_managements', [
            'id' => $management->id,
            'practice_type' => 'composting',
            'material_type' => 'grape_marc',
        ]);
    }

    public function test_burning_justification_required_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $management = $this->makeManagement($viticulturist->id, 'burning');

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['residueManagement' => $management])
            ->set('justification', '')
            ->set('quantity_unit', '')
            ->call('save')
            ->assertHasErrors(['justification']);
    }

    public function test_switch_from_burning_to_other_clears_justification_in_db(): void
    {
        $viticulturist = $this->makeViticulturist();
        $management = $this->makeManagement($viticulturist->id, 'burning');
        // Manually add justification
        $management->update(['justification' => 'Justificación de prueba que supera los 20 caracteres']);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['residueManagement' => $management])
            ->set('practice_type', 'composting')
            ->set('quantity_unit', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('residue_managements', [
            'id' => $management->id,
            'practice_type' => 'composting',
            'justification' => null,
        ]);
    }

    public function test_cannot_edit_other_viticulturists_management(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $management = $this->makeManagement($viticulturist->id);

        $this->actingAs($other)
            ->get(route('viticulturist.residue-managements.edit', $management))
            ->assertStatus(403);
    }

    private function makeManagement(int $viticulturistId, string $practiceType = 'incorporation'): ResidueManagement
    {
        $campaign = Campaign::create([
            'viticulturist_id' => $viticulturistId,
            'year' => 2024,
            'name' => 'Campaña 2024',
        ]);

        return ResidueManagement::create([
            'viticulturist_id' => $viticulturistId,
            'campaign_id' => $campaign->id,
            'date' => '2024-03-15',
            'practice_type' => $practiceType,
            'material_type' => 'pruning_wood',
            'active' => true,
        ]);
    }
}
