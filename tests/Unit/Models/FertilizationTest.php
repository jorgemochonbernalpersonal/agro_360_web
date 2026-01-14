<?php

namespace Tests\Unit\Models;

use App\Models\Fertilization;
use App\Models\AgriculturalActivity;
use App\Models\Plot;
use App\Models\User;
use App\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\MunicipalitySeeder;

class FertilizationTest extends TestCase
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

    public function test_fertilization_belongs_to_activity(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'fertilization',
            'activity_date' => now(),
        ]);

        $fertilization = Fertilization::create([
            'activity_id' => $activity->id,
            'fertilizer_type' => 'organic',
            'fertilizer_name' => 'Compost',
            'quantity' => 100.5,
        ]);

        $this->assertEquals($activity->id, $fertilization->activity->id);
        $this->assertInstanceOf(AgriculturalActivity::class, $fertilization->activity);
    }

    public function test_quantity_is_cast_to_decimal(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'fertilization',
            'activity_date' => now(),
        ]);

        $fertilization = Fertilization::create([
            'activity_id' => $activity->id,
            'quantity' => 123.456,
        ]);

        // Los campos decimal:3 devuelven strings en Laravel
        $this->assertIsString($fertilization->quantity);
        $this->assertEquals('123.456', $fertilization->quantity);
    }

    public function test_area_applied_is_cast_to_decimal(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'fertilization',
            'activity_date' => now(),
        ]);

        $fertilization = Fertilization::create([
            'activity_id' => $activity->id,
            'area_applied' => 2.5,
        ]);

        // Los campos decimal:3 devuelven strings en Laravel
        $this->assertIsString($fertilization->area_applied);
        $this->assertEquals('2.500', $fertilization->area_applied);
    }

    public function test_fertilization_can_store_all_fields(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'fertilization',
            'activity_date' => now(),
        ]);

        $fertilization = Fertilization::create([
            'activity_id' => $activity->id,
            'fertilizer_type' => 'mineral',
            'fertilizer_name' => 'NPK 15-15-15',
            'quantity' => 200.75,
            'npk_ratio' => '15-15-15',
            'application_method' => 'spread',
            'area_applied' => 5.0,
        ]);

        $this->assertEquals('mineral', $fertilization->fertilizer_type);
        $this->assertEquals('NPK 15-15-15', $fertilization->fertilizer_name);
        $this->assertEquals(200.75, $fertilization->quantity);
        $this->assertEquals('15-15-15', $fertilization->npk_ratio);
        $this->assertEquals('spread', $fertilization->application_method);
        $this->assertEquals(5.0, $fertilization->area_applied);
    }

    public function test_fertilization_can_have_nullable_fields(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'fertilization',
            'activity_date' => now(),
        ]);

        $fertilization = Fertilization::create([
            'activity_id' => $activity->id,
            'fertilizer_type' => 'organic',
            'fertilizer_name' => null,
            'quantity' => null,
            'npk_ratio' => null,
            'application_method' => null,
            'area_applied' => null,
        ]);

        $this->assertNotNull($fertilization->id);
        $this->assertNull($fertilization->fertilizer_name);
        $this->assertNull($fertilization->quantity);
    }
}

