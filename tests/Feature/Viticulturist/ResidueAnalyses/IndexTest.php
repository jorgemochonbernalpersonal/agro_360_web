<?php

namespace Tests\Feature\Viticulturist\ResidueAnalyses;

use App\Livewire\Viticulturist\ResidueAnalyses\Index;
use App\Models\Campaign;
use App\Models\ResidueAnalysis;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    private function makeAnalysis(int $viticulturistId, string $labName = 'Lab Test', bool $active = true): ResidueAnalysis
    {
        $campaign = Campaign::getOrCreateActiveForYear($viticulturistId);

        return ResidueAnalysis::create([
            'viticulturist_id'  => $viticulturistId,
            'campaign_id'       => $campaign->id,
            'analysis_date'     => '2024-06-15',
            'laboratory_name'   => $labName,
            'overall_compliant' => true,
            'active'            => $active,
        ]);
    }

    public function test_index_shows_active_analyses(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeAnalysis($viticulturist->id, 'Lab-Visible-Test-001');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertSee('Lab-Visible-Test-001');
    }

    public function test_deactivate_archives_analysis(): void
    {
        $viticulturist = $this->makeViticulturist();
        $analysis      = $this->makeAnalysis($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('deactivate', $analysis->id);

        $this->assertDatabaseHas('residue_analyses', [
            'id'     => $analysis->id,
            'active' => false,
        ]);
    }

    public function test_deactivated_disappears(): void
    {
        $viticulturist = $this->makeViticulturist();
        $analysis      = $this->makeAnalysis($viticulturist->id, 'Lab-A-Archivar-001');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertSee('Lab-A-Archivar-001')
            ->call('deactivate', $analysis->id)
            ->assertDontSee('Lab-A-Archivar-001');
    }

    public function test_cannot_deactivate_other_viticulturists_analysis(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeOtherViticulturist();
        $analysis      = $this->makeAnalysis($viticulturist->id);

        $this->actingAs($other);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('deactivate', $analysis->id);
    }
}
