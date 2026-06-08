<?php

namespace Tests\Feature\Viticulturist\HarvestDeclarations;

use App\Livewire\Viticulturist\HarvestDeclarations\Edit;
use App\Models\Campaign;
use App\Models\HarvestDeclaration;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    public function test_mount_fills_fields(): void
    {
        $v = $this->makeViticulturist();
        $declaration = $this->makeDeclaration($v->id);
        $this->actingAs($v);

        Livewire::test(Edit::class, ['declaration' => $declaration])
            ->assertSet('declaration_year', '2024')
            ->assertSet('declaration_date', '2024-11-15')
            ->assertSet('authority', 'Junta de Andalucía')
            ->assertSet('status', 'draft');
    }

    public function test_can_update_declaration(): void
    {
        $v = $this->makeViticulturist();
        $declaration = $this->makeDeclaration($v->id);
        $this->actingAs($v);

        Livewire::test(Edit::class, ['declaration' => $declaration])
            ->set('authority', 'Consejería Agricultura')
            ->set('total_kg', '12500.50')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.harvest-declarations.index'));

        $this->assertDatabaseHas('harvest_declarations', [
            'id' => $declaration->id,
            'authority' => 'Consejería Agricultura',
        ]);
    }

    public function test_validates_required_fields_on_update(): void
    {
        $v = $this->makeViticulturist();
        $declaration = $this->makeDeclaration($v->id);
        $this->actingAs($v);

        Livewire::test(Edit::class, ['declaration' => $declaration])
            ->set('declaration_year', '')
            ->set('declaration_date', '')
            ->call('save')
            ->assertHasErrors(['declaration_year', 'declaration_date']);
    }

    public function test_cannot_edit_other_viticulturists_declaration(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $declaration = $this->makeDeclaration($v->id);

        $this->actingAs($other)
            ->get(route('viticulturist.harvest-declarations.edit', $declaration))
            ->assertStatus(403);
    }

    private function makeDeclaration(int $viticulturistId): HarvestDeclaration
    {
        $campaign = Campaign::create([
            'viticulturist_id' => $viticulturistId,
            'year' => 2024,
            'name' => 'Campaña 2024',
            'active' => true,
        ]);

        return HarvestDeclaration::create([
            'viticulturist_id' => $viticulturistId,
            'campaign_id' => $campaign->id,
            'declaration_year' => 2024,
            'declaration_date' => '2024-11-15',
            'authority' => 'Junta de Andalucía',
            'status' => 'draft',
            'active' => true,
        ]);
    }
}
