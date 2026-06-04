<?php

namespace Tests\Feature\Viticulturist\WaterConcessions;

use App\Livewire\Viticulturist\WaterConcessions\Edit;
use App\Models\WaterConcession;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    public function test_can_edit_water_concession(): void
    {
        $v = $this->makeViticulturist();
        $concession = $this->makeConcession($v->id);
        $this->actingAs($v);

        Livewire::test(Edit::class, ['concession' => $concession])
            ->set('water_body', 'Río Actualizado')
            ->set('max_volume_m3', '9000.000')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.water-concessions.index'));

        $this->assertDatabaseHas('water_concessions', [
            'id' => $concession->id,
            'water_body' => 'Río Actualizado',
        ]);
    }

    public function test_mount_fills_properties_from_model(): void
    {
        $v = $this->makeViticulturist();
        $concession = $this->makeConcession($v->id);
        $this->actingAs($v);

        Livewire::test(Edit::class, ['concession' => $concession])
            ->assertSet('water_body', 'Río Original')
            ->assertSet('authority', 'CHD');
    }

    public function test_cannot_edit_other_viticulturists_concession(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $concession = $this->makeConcession($v->id);
        $this->actingAs($other);

        Livewire::test(Edit::class, ['concession' => $concession])
            ->assertStatus(403);
    }

    public function test_validates_max_volume_on_update(): void
    {
        $v = $this->makeViticulturist();
        $concession = $this->makeConcession($v->id);
        $this->actingAs($v);

        Livewire::test(Edit::class, ['concession' => $concession])
            ->set('max_volume_m3', '-100')
            ->call('save')
            ->assertHasErrors(['max_volume_m3']);
    }

    private function makeConcession(int $viticulturistId): WaterConcession
    {
        return WaterConcession::create([
            'viticulturist_id' => $viticulturistId,
            'concession_type' => 'superficial',
            'water_body' => 'Río Original',
            'authority' => 'CHD',
            'max_volume_m3' => 5000.000,
        ]);
    }
}
