<?php

namespace Tests\Unit\Models;

use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\PostHarvestTreatment;
use App\Models\User;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\MunicipalitySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostHarvestTreatmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            AutonomousCommunitySeeder::class,
            ProvinceSeeder::class,
            MunicipalitySeeder::class,
        ]);
    }

    public function test_treatment_belongs_to_activity(): void
    {
        $activity = $this->makeActivity();
        $treatment = PostHarvestTreatment::create([
            'activity_id' => $activity->id,
            'application_type' => 'copper_treatment',
            'treated_area_ha' => 1.5,
        ]);

        $this->assertEquals($activity->id, $treatment->activity->id);
        $this->assertInstanceOf(AgriculturalActivity::class, $treatment->activity);
    }

    public function test_treated_area_ha_stored_with_decimal4_precision(): void
    {
        $activity = $this->makeActivity();
        $treatment = PostHarvestTreatment::create([
            'activity_id' => $activity->id,
            'application_type' => 'copper_treatment',
            'treated_area_ha' => 2.5678,
        ]);

        $this->assertIsString($treatment->treated_area_ha);
        $this->assertEquals('2.5678', $treatment->treated_area_ha);
    }

    public function test_dose_per_hectare_stored_with_decimal4_precision(): void
    {
        $activity = $this->makeActivity();
        $treatment = PostHarvestTreatment::create([
            'activity_id' => $activity->id,
            'application_type' => 'sulfur_treatment',
            'treated_area_ha' => 1.0,
            'dose_per_hectare' => 3.1250,
            'dose_unit' => 'kg/ha',
        ]);

        $this->assertIsString($treatment->dose_per_hectare);
        $this->assertEquals('3.1250', $treatment->dose_per_hectare);
    }

    public function test_water_volume_stored_with_decimal2_precision(): void
    {
        $activity = $this->makeActivity();
        $treatment = PostHarvestTreatment::create([
            'activity_id' => $activity->id,
            'application_type' => 'foliar_application',
            'treated_area_ha' => 1.0,
            'water_volume_liters' => 300.50,
        ]);

        $this->assertIsString($treatment->water_volume_liters);
        $this->assertEquals('300.50', $treatment->water_volume_liters);
    }

    public function test_application_type_label_accessor(): void
    {
        $activity = $this->makeActivity();
        $treatment = PostHarvestTreatment::create([
            'activity_id' => $activity->id,
            'application_type' => 'copper_treatment',
            'treated_area_ha' => 1.0,
        ]);

        $this->assertEquals('Tratamiento con cobre', $treatment->application_type_label);
    }

    public function test_nullable_fields_can_be_null(): void
    {
        $activity = $this->makeActivity();
        $treatment = PostHarvestTreatment::create([
            'activity_id' => $activity->id,
            'application_type' => 'wound_sealing',
            'treated_area_ha' => 0.5,
            'product_id' => null,
            'dose_per_hectare' => null,
            'dose_unit' => null,
            'water_volume_liters' => null,
            'reentry_interval_hours' => null,
            'notes' => null,
        ]);

        $this->assertNull($treatment->product_id);
        $this->assertNull($treatment->dose_per_hectare);
        $this->assertNull($treatment->water_volume_liters);
        $this->assertNull($treatment->reentry_interval_hours);
        $this->assertNull($treatment->notes);
    }

    // ── Campo PAC: reentry_interval_hours ─────────────────────────────────────

    public function test_reentry_interval_hours_stored_as_integer(): void
    {
        $activity = $this->makeActivity();
        $treatment = PostHarvestTreatment::create([
            'activity_id' => $activity->id,
            'application_type' => 'copper_treatment',
            'treated_area_ha' => 2.0,
            'reentry_interval_hours' => 24,
        ]);

        $this->assertIsInt($treatment->reentry_interval_hours);
        $this->assertEquals(24, $treatment->reentry_interval_hours);
    }

    public function test_reentry_interval_hours_zero_means_no_restriction(): void
    {
        $activity = $this->makeActivity();
        $treatment = PostHarvestTreatment::create([
            'activity_id' => $activity->id,
            'application_type' => 'wound_sealing',
            'treated_area_ha' => 1.0,
            'reentry_interval_hours' => 0,
        ]);

        $this->assertIsInt($treatment->reentry_interval_hours);
        $this->assertEquals(0, $treatment->reentry_interval_hours);
    }

    public function test_reentry_interval_hours_is_nullable(): void
    {
        $activity = $this->makeActivity();
        $treatment = PostHarvestTreatment::create([
            'activity_id' => $activity->id,
            'application_type' => 'foliar_application',
            'treated_area_ha' => 1.0,
            'reentry_interval_hours' => null,
        ]);

        $this->assertNull($treatment->reentry_interval_hours);
    }

    private function makeActivity(): AgriculturalActivity
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        return AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'post_harvest',
            'activity_date' => now(),
        ]);
    }
}
