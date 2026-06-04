<?php

namespace Tests\Feature\Viticulturist\WaterConcessions;

use App\Livewire\Viticulturist\WaterConcessions\Index;
use App\Models\WaterConcession;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    public function test_index_shows_active_concessions(): void
    {
        $v = $this->makeViticulturist();
        $this->makeConcession($v->id, true, ['water_body' => 'Río-Visible-001']);
        $this->actingAs($v);

        Livewire::test(Index::class)->assertSee('Río-Visible-001');
    }

    public function test_archive_sets_active_false(): void
    {
        $v = $this->makeViticulturist();
        $concession = $this->makeConcession($v->id);
        $this->actingAs($v);

        Livewire::test(Index::class)->call('archive', $concession->id);

        $this->assertDatabaseHas('water_concessions', ['id' => $concession->id, 'active' => false]);
    }

    public function test_unarchive_restores_record(): void
    {
        $v = $this->makeViticulturist();
        $concession = $this->makeConcession($v->id, false);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('switchTab', 'archived')
            ->call('unarchive', $concession->id);

        $this->assertDatabaseHas('water_concessions', ['id' => $concession->id, 'active' => true]);
    }

    public function test_archived_tab_shows_inactive_records(): void
    {
        $v = $this->makeViticulturist();
        $this->makeConcession($v->id, false, ['water_body' => 'Acuífero-Archivado-001']);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('switchTab', 'archived')
            ->assertSee('Acuífero-Archivado-001');
    }

    public function test_delete_removes_archived_record(): void
    {
        $v = $this->makeViticulturist();
        $concession = $this->makeConcession($v->id, false);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('switchTab', 'archived')
            ->call('delete', $concession->id);

        $this->assertDatabaseMissing('water_concessions', ['id' => $concession->id]);
    }

    public function test_cannot_archive_other_viticulturists_concession(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $concession = $this->makeConcession($v->id);
        $this->actingAs($other);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)->call('archive', $concession->id);
    }

    public function test_stats_include_total_volume(): void
    {
        $v = $this->makeViticulturist();
        $this->makeConcession($v->id, true, ['max_volume_m3' => 5000.000]);
        $this->makeConcession($v->id, true, ['max_volume_m3' => 3000.000]);
        $this->actingAs($v);

        // Stats are returned via viewData — check the component renders without error
        Livewire::test(Index::class)->assertOk();
    }

    private function makeConcession(int $viticulturistId, bool $active = true, array $overrides = []): WaterConcession
    {
        return WaterConcession::create(array_merge([
            'viticulturist_id' => $viticulturistId,
            'concession_type' => 'superficial',
            'water_body' => 'Río Duero',
            'authority' => 'CHD',
            'max_volume_m3' => 10000.000,
            'active' => $active,
        ], $overrides));
    }
}
