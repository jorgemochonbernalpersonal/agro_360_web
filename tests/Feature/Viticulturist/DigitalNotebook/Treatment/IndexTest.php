<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\Treatment;

use App\Livewire\Viticulturist\DigitalNotebook\TreatmentIndex;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\Municipality;
use App\Models\PhytosanitaryProduct;
use App\Models\PhytosanitaryTreatment;
use App\Models\Plot;
use App\Models\Province;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    public function test_renders_for_viticulturist(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(TreatmentIndex::class)
            ->assertStatus(200);
    }

    public function test_does_not_show_other_viticulturist_activities(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $this->makeActivity($other, 'Tratamiento ajeno');

        $this->actingAs($viticulturist);

        Livewire::test(TreatmentIndex::class)
            ->assertDontSee('Tratamiento ajeno');
    }

    public function test_can_delete_own_activity(): void
    {
        $viticulturist = $this->makeViticulturist();
        $activity = $this->makeActivity($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(TreatmentIndex::class)
            ->call('delete', $activity->id);

        $this->assertDatabaseMissing('agricultural_activities', ['id' => $activity->id]);
    }

    public function test_cannot_delete_other_viticulturist_activity(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $activity = $this->makeActivity($other);

        $this->actingAs($viticulturist);

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(TreatmentIndex::class)
            ->call('delete', $activity->id);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeActivity($viticulturist, string $notes = 'Nota tratamiento'): AgriculturalActivity
    {
        $ac = AutonomousCommunity::firstOrCreate(['code' => 'TST'], ['name' => 'Test AC']);
        $prov = Province::firstOrCreate(['code' => '00'], ['name' => 'Test Province', 'autonomous_community_id' => $ac->id]);
        $muni = Municipality::firstOrCreate(['code' => '00000'], ['name' => 'Test Municipality', 'province_id' => $prov->id]);

        $plot = Plot::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'autonomous_community_id' => $ac->id,
            'province_id' => $prov->id,
            'municipality_id' => $muni->id,
            'active' => true,
        ]);

        $campaign = Campaign::factory()->active()->create([
            'viticulturist_id' => $viticulturist->id,
            'year' => now()->year,
        ]);

        $activity = AgriculturalActivity::create([
            'viticulturist_id' => $viticulturist->id,
            'plot_id' => $plot->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now()->format('Y-m-d'),
            'notes' => $notes,
            'is_locked' => false,
        ]);

        $product = PhytosanitaryProduct::firstOrCreate(
            ['registration_number' => 'TEST-0001'],
            [
                'name' => 'Producto Test',
                'active_ingredient' => 'ingrediente',
                'manufacturer' => 'Fabricante',
                'type' => 'fungicide',
                'withdrawal_period_days' => 14,
            ]
        );

        PhytosanitaryTreatment::create([
            'activity_id' => $activity->id,
            'product_id' => $product->id,
            'dose_per_hectare' => 1.5,
            'total_dose' => 3.0,
            'area_treated' => 2.0,
            'application_method' => 'pulverización',
            'target_pest' => 'mildiu',
        ]);

        return $activity;
    }
}
