<?php

namespace Tests\Unit\Models;

use App\Models\Observation;
use App\Models\AgriculturalActivity;
use App\Models\Plot;
use App\Models\User;
use App\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\MunicipalitySeeder;

class ObservationTest extends TestCase
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

    public function test_observation_belongs_to_activity(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'observation',
            'activity_date' => now(),
        ]);

        $observation = Observation::create([
            'activity_id' => $activity->id,
            'observation_type' => 'pest',
            'description' => 'Aphids detected',
        ]);

        $this->assertEquals($activity->id, $observation->activity->id);
        $this->assertInstanceOf(AgriculturalActivity::class, $observation->activity);
    }

    public function test_photos_field_is_cast_to_array(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'observation',
            'activity_date' => now(),
        ]);

        $photos = [
            'photo1.jpg',
            'photo2.jpg',
            'photo3.jpg',
        ];

        $observation = Observation::create([
            'activity_id' => $activity->id,
            'observation_type' => 'disease',
            'description' => 'Leaf spot detected',
            'photos' => $photos,
        ]);

        $this->assertIsArray($observation->photos);
        $this->assertCount(3, $observation->photos);
        $this->assertEquals('photo1.jpg', $observation->photos[0]);
        $this->assertEquals('photo2.jpg', $observation->photos[1]);
        $this->assertEquals('photo3.jpg', $observation->photos[2]);
    }

    public function test_photos_can_be_null(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'observation',
            'activity_date' => now(),
        ]);

        $observation = Observation::create([
            'activity_id' => $activity->id,
            'observation_type' => 'pest',
            'description' => 'Observation without photos',
            'photos' => null,
        ]);

        $this->assertNull($observation->photos);
    }

    public function test_observation_can_store_all_fields(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'observation',
            'activity_date' => now(),
        ]);

        $observation = Observation::create([
            'activity_id' => $activity->id,
            'observation_type' => 'pest',
            'description' => 'Aphids detected on leaves',
            'photos' => ['photo1.jpg', 'photo2.jpg'],
            'severity' => 'high',
            'action_taken' => 'Applied insecticide',
        ]);

        $this->assertEquals('pest', $observation->observation_type);
        $this->assertEquals('Aphids detected on leaves', $observation->description);
        $this->assertIsArray($observation->photos);
        $this->assertEquals('high', $observation->severity);
        $this->assertEquals('Applied insecticide', $observation->action_taken);
    }

    public function test_observation_can_have_nullable_fields(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'observation',
            'activity_date' => now(),
        ]);

        $observation = Observation::create([
            'activity_id' => $activity->id,
            'observation_type' => 'pest',
            'description' => null,
            'photos' => null,
            'severity' => null,
            'action_taken' => null,
        ]);

        $this->assertNotNull($observation->id);
        $this->assertNull($observation->description);
        $this->assertNull($observation->photos);
    }
}

