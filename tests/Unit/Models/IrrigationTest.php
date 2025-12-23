<?php

namespace Tests\Unit\Models;

use App\Models\Irrigation;
use App\Models\AgriculturalActivity;
use App\Models\Plot;
use App\Models\User;
use App\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\MunicipalitySeeder;

class IrrigationTest extends TestCase
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

    public function test_irrigation_belongs_to_activity(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'irrigation',
            'activity_date' => now(),
        ]);

        $irrigation = Irrigation::create([
            'activity_id' => $activity->id,
            'water_volume' => 500.0,
        ]);

        $this->assertEquals($activity->id, $irrigation->activity->id);
        $this->assertInstanceOf(AgriculturalActivity::class, $irrigation->activity);
    }

    public function test_water_volume_is_cast_to_decimal(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'irrigation',
            'activity_date' => now(),
        ]);

        $irrigation = Irrigation::create([
            'activity_id' => $activity->id,
            'water_volume' => 1234.567,
        ]);

        $this->assertIsFloat($irrigation->water_volume);
        $this->assertEquals(1234.567, $irrigation->water_volume);
    }

    public function test_soil_moisture_fields_are_cast_to_decimal(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'irrigation',
            'activity_date' => now(),
        ]);

        $irrigation = Irrigation::create([
            'activity_id' => $activity->id,
            'soil_moisture_before' => 25.5,
            'soil_moisture_after' => 45.8,
        ]);

        $this->assertIsFloat($irrigation->soil_moisture_before);
        $this->assertIsFloat($irrigation->soil_moisture_after);
        $this->assertEquals(25.5, $irrigation->soil_moisture_before);
        $this->assertEquals(45.8, $irrigation->soil_moisture_after);
    }

    public function test_irrigation_can_store_all_fields(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'irrigation',
            'activity_date' => now(),
        ]);

        $irrigation = Irrigation::create([
            'activity_id' => $activity->id,
            'water_volume' => 1000.0,
            'irrigation_method' => 'drip',
            'duration_minutes' => 120,
            'soil_moisture_before' => 20.0,
            'soil_moisture_after' => 50.0,
        ]);

        $this->assertEquals(1000.0, $irrigation->water_volume);
        $this->assertEquals('drip', $irrigation->irrigation_method);
        $this->assertEquals(120, $irrigation->duration_minutes);
        $this->assertEquals(20.0, $irrigation->soil_moisture_before);
        $this->assertEquals(50.0, $irrigation->soil_moisture_after);
    }

    public function test_irrigation_can_have_nullable_fields(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'irrigation',
            'activity_date' => now(),
        ]);

        $irrigation = Irrigation::create([
            'activity_id' => $activity->id,
            'water_volume' => null,
            'irrigation_method' => null,
            'duration_minutes' => null,
            'soil_moisture_before' => null,
            'soil_moisture_after' => null,
        ]);

        $this->assertNotNull($irrigation->id);
        $this->assertNull($irrigation->water_volume);
        $this->assertNull($irrigation->irrigation_method);
    }
}

