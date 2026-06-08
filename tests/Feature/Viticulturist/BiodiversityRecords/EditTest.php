<?php

namespace Tests\Feature\Viticulturist\BiodiversityRecords;

use App\Livewire\Viticulturist\BiodiversityRecords\Edit;
use App\Models\BiodiversityRecord;
use App\Models\Plot;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    public function test_mount_fills_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $record = $this->makeRecord($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['biodiversityRecord' => $record])
            ->assertSet('record_type', 'cubierta_vegetal')
            ->assertSet('record_date', '2024-06-15')
            ->assertSet('species', 'Festuca rubra');
    }

    public function test_can_update_record(): void
    {
        $viticulturist = $this->makeViticulturist();
        $record = $this->makeRecord($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['biodiversityRecord' => $record])
            ->set('species', 'Lolium perenne')
            ->set('area_m2', '800')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.biodiversity-records.index'));

        $this->assertDatabaseHas('biodiversity_records', [
            'id' => $record->id,
            'species' => 'Lolium perenne',
            'area_m2' => 800,
        ]);
    }

    public function test_validates_required_fields_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $record = $this->makeRecord($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['biodiversityRecord' => $record])
            ->set('plot_id', '')
            ->set('record_date', '')
            ->call('save')
            ->assertHasErrors(['plot_id', 'record_date']);
    }

    public function test_cannot_edit_other_viticulturists_record(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $record = $this->makeRecord($viticulturist->id);

        $this->actingAs($other)
            ->get(route('viticulturist.biodiversity-records.edit', $record))
            ->assertStatus(403);
    }

    private function makeRecord(int $viticulturistId): BiodiversityRecord
    {
        $plot = Plot::factory()->create(['viticulturist_id' => $viticulturistId]);

        return BiodiversityRecord::create([
            'viticulturist_id' => $viticulturistId,
            'plot_id' => $plot->id,
            'record_type' => 'cubierta_vegetal',
            'record_date' => '2024-06-15',
            'species' => 'Festuca rubra',
            'area_m2' => 500,
        ]);
    }
}
