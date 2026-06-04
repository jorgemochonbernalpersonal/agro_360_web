<?php

namespace Tests\Feature\Viticulturist\FieldApplicators;

use App\Livewire\Viticulturist\FieldApplicators\Edit;
use App\Models\FieldApplicator;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    public function test_mount_fills_fields_from_model(): void
    {
        $viticulturist = $this->makeViticulturist();
        $applicator = $this->makeApplicator($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['fieldApplicator' => $applicator])
            ->assertSet('name', 'Aplicador Original')
            ->assertSet('ropo_number', 'ROPO-ORIG-001')
            ->assertSet('ropo_category', 'basic')
            ->assertSet('is_advisor', false);
    }

    public function test_can_update_applicator(): void
    {
        $viticulturist = $this->makeViticulturist();
        $applicator = $this->makeApplicator($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['fieldApplicator' => $applicator])
            ->set('name', 'Aplicador Actualizado')
            ->set('ropo_number', 'ROPO-NEW-001')
            ->set('ropo_category', 'qualified')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.field-applicators.index'));

        $this->assertDatabaseHas('field_applicators', [
            'id' => $applicator->id,
            'name' => 'Aplicador Actualizado',
            'ropo_number' => 'ROPO-NEW-001',
            'ropo_category' => 'qualified',
        ]);
    }

    public function test_validates_required_fields_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $applicator = $this->makeApplicator($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['fieldApplicator' => $applicator])
            ->set('name', '')
            ->set('ropo_number', '')
            ->call('save')
            ->assertHasErrors(['name', 'ropo_number']);
    }

    public function test_advisor_license_required_when_is_advisor_toggled_on(): void
    {
        $viticulturist = $this->makeViticulturist();
        $applicator = $this->makeApplicator($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['fieldApplicator' => $applicator])
            ->set('is_advisor', true)
            ->set('advisor_license', '')
            ->call('save')
            ->assertHasErrors(['advisor_license']);
    }

    public function test_cannot_edit_other_viticulturists_applicator(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $applicator = $this->makeApplicator($viticulturist->id);

        $this->actingAs($other)
            ->get(route('viticulturist.field-applicators.edit', $applicator))
            ->assertStatus(403);
    }

    private function makeApplicator(int $viticulturistId): FieldApplicator
    {
        return FieldApplicator::create([
            'viticulturist_id' => $viticulturistId,
            'name' => 'Aplicador Original',
            'ropo_number' => 'ROPO-ORIG-001',
            'ropo_category' => 'basic',
            'is_advisor' => false,
            'active' => true,
        ]);
    }
}
