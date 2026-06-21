<?php

namespace Tests\Feature\Viticulturist\SoilAnalyses;

use App\Livewire\Viticulturist\SoilAnalyses\Index;
use App\Models\Plot;
use App\Models\SoilAnalysis;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    public function test_index_shows_analyses(): void
    {
        $v = $this->makeViticulturist();
        $analysis = $this->makeAnalysis($v, ['laboratory' => 'Lab Visible']);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->assertViewHas('stats', fn ($s) => $s['total'] === 1);
    }

    public function test_does_not_show_other_viticulturists_analyses(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $this->makeAnalysis($other);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->assertViewHas('stats', fn ($s) => $s['total'] === 0);
    }

    public function test_delete_removes_analysis(): void
    {
        $v = $this->makeViticulturist();
        $analysis = $this->makeAnalysis($v);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('delete', $analysis->id);

        $this->assertDatabaseMissing('soil_analyses', ['id' => $analysis->id]);
    }

    public function test_cannot_delete_other_viticulturists_analysis(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $analysis = $this->makeAnalysis($other);
        $this->actingAs($v);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('delete', $analysis->id);
    }

    public function test_filter_by_texture_class(): void
    {
        $v = $this->makeViticulturist();
        $this->makeAnalysis($v, ['texture_class' => 'franco']);
        $this->makeAnalysis($v, ['texture_class' => 'arcilloso']);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->set('filterTexture', 'franco')
            ->assertViewHas('stats', fn ($s) => $s['total'] === 2);
    }

    public function test_stats_avg_ph(): void
    {
        $v = $this->makeViticulturist();
        $this->makeAnalysis($v, ['ph' => '6.0']);
        $this->makeAnalysis($v, ['ph' => '7.0']);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->assertViewHas('stats', function ($s) {
                return (float) $s['avg_ph'] === 6.5
                    && $s['avg_ph_color'] === 'green';
            });
    }

    private function makeAnalysis($viticulturist, array $attrs = []): SoilAnalysis
    {
        $plot = Plot::factory()->forViticulturist($viticulturist)->create();

        return SoilAnalysis::create(array_merge([
            'viticulturist_id' => $viticulturist->id,
            'plot_id' => $plot->id,
            'analysis_date' => '2024-06-15',
        ], $attrs));
    }
}
