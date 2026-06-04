<?php

namespace Tests\Feature\Viticulturist\WaterConcessions;

use App\Livewire\Viticulturist\WaterConcessions\Create;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_can_create_water_concession(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('concession_type', 'subterranea')
            ->set('water_body', 'Acuífero Terciario')
            ->set('authority', 'CHD')
            ->set('max_volume_m3', '8000.000')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.water-concessions.index'));

        $this->assertDatabaseHas('water_concessions', [
            'viticulturist_id' => $v->id,
            'concession_type' => 'subterranea',
            'water_body' => 'Acuífero Terciario',
            'active' => true,
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('water_body', '')
            ->set('authority', '')
            ->set('max_volume_m3', '')
            ->call('save')
            ->assertHasErrors(['water_body', 'authority', 'max_volume_m3']);
    }

    public function test_max_volume_must_be_positive(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('max_volume_m3', '0')
            ->call('save')
            ->assertHasErrors(['max_volume_m3']);
    }

    public function test_concession_type_must_be_valid_enum(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('concession_type', 'invalid')
            ->call('save')
            ->assertHasErrors(['concession_type']);
    }

    public function test_expiry_must_be_after_concession_date(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('concession_date', '2024-06-01')
            ->set('expiry_date', '2024-01-01')
            ->set('water_body', 'Test')
            ->set('authority', 'CHD')
            ->set('max_volume_m3', '1000')
            ->call('save')
            ->assertHasErrors(['expiry_date']);
    }

    public function test_optional_fields_can_be_empty(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('concession_type', 'superficial')
            ->set('water_body', 'Río Pisuerga')
            ->set('authority', 'CHD')
            ->set('max_volume_m3', '2000')
            ->set('concession_number', '')
            ->set('surface_ha', '')
            ->set('used_volume_m3', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('water_concessions', [
            'viticulturist_id' => $v->id,
            'concession_number' => null,
            'surface_ha' => null,
        ]);
    }
}
