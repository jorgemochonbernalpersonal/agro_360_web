<?php

namespace Tests\Feature\Viticulturist\FieldApplicators;

use App\Livewire\Viticulturist\FieldApplicators\Create;
use App\Models\FieldApplicator;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_can_create_applicator_with_required_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', 'Pedro Aplicador')
            ->set('ropo_number', 'ROPO-2024-001')
            ->set('ropo_category', 'qualified')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.field-applicators.index'));

        $this->assertDatabaseHas('field_applicators', [
            'viticulturist_id' => $viticulturist->id,
            'name' => 'Pedro Aplicador',
            'ropo_number' => 'ROPO-2024-001',
            'ropo_category' => 'qualified',
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', '')
            ->set('ropo_number', '')
            ->call('save')
            ->assertHasErrors(['name', 'ropo_number']);
    }

    public function test_invalid_ropo_category_fails_validation(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', 'Pedro Test')
            ->set('ropo_number', 'ROPO-001')
            ->set('ropo_category', 'categoria_invalida')
            ->call('save')
            ->assertHasErrors(['ropo_category']);
    }

    public function test_all_valid_ropo_categories_are_accepted(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        foreach (array_keys(FieldApplicator::CATEGORIES) as $i => $category) {
            Livewire::test(Create::class)
                ->set('name', "Aplicador {$i}")
                ->set('ropo_number', "ROPO-00{$i}")
                ->set('ropo_category', $category)
                ->call('save')
                ->assertHasNoErrors(['ropo_category']);
        }
    }

    public function test_advisor_license_required_when_is_advisor_true(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', 'Pedro Asesor')
            ->set('ropo_number', 'ROPO-2024-001')
            ->set('ropo_category', 'qualified')
            ->set('is_advisor', true)
            ->set('advisor_license', '')
            ->call('save')
            ->assertHasErrors(['advisor_license']);
    }

    public function test_advisor_license_not_required_when_not_advisor(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', 'Pedro Aplicador')
            ->set('ropo_number', 'ROPO-2024-001')
            ->set('ropo_category', 'qualified')
            ->set('is_advisor', false)
            ->set('advisor_license', '')
            ->call('save')
            ->assertHasNoErrors(['advisor_license']);
    }

    public function test_advisor_license_saved_when_is_advisor_true(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', 'Pedro Asesor')
            ->set('ropo_number', 'ROPO-2024-001')
            ->set('ropo_category', 'qualified')
            ->set('is_advisor', true)
            ->set('advisor_license', 'LIC-ASESOR-001')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('field_applicators', [
            'viticulturist_id' => $viticulturist->id,
            'is_advisor' => true,
            'advisor_license' => 'LIC-ASESOR-001',
        ]);
    }
}
