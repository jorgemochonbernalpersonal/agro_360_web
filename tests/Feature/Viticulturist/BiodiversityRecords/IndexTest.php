<?php

namespace Tests\Feature\Viticulturist\BiodiversityRecords;

use App\Livewire\Viticulturist\BiodiversityRecords\Index;
use App\Models\BiodiversityRecord;
use App\Models\Plot;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    public function test_index_shows_records(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeRecord($viticulturist->id, 'Biodiversidad-Visible-001');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertSee('Biodiversidad-Visible-001');
    }

    public function test_delete_removes_record(): void
    {
        $viticulturist = $this->makeViticulturist();
        $record = $this->makeRecord($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('delete', $record->id);

        $this->assertDatabaseMissing('biodiversity_records', ['id' => $record->id]);
    }

    public function test_cannot_delete_other_viticulturists_record(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $record = $this->makeRecord($viticulturist->id);

        $this->actingAs($other);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('delete', $record->id);
    }

    public function test_filter_by_record_type(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeRecord($viticulturist->id, 'Cubierta Test', 'cubierta_vegetal');
        $this->makeRecord($viticulturist->id, 'Seto Test', 'seto');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('filterRecordType', 'cubierta_vegetal')
            ->assertSee('Cubierta Test')
            ->assertDontSee('Seto Test');
    }

    private function makeRecord(int $viticulturistId, string $description = 'Registro Test', string $recordType = 'cubierta_vegetal'): BiodiversityRecord
    {
        $plot = Plot::factory()->create(['viticulturist_id' => $viticulturistId]);

        return BiodiversityRecord::create([
            'viticulturist_id' => $viticulturistId,
            'plot_id' => $plot->id,
            'record_type' => $recordType,
            'record_date' => '2024-06-15',
            'description' => $description,
            'area_m2' => 200,
        ]);
    }
}
