<?php

namespace Tests\Unit\Models;

use App\Models\CulturalWork;
use App\Models\AgriculturalActivity;
use App\Models\Plot;
use App\Models\User;
use App\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\MunicipalitySeeder;

class CulturalWorkTest extends TestCase
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

    public function test_cultural_work_belongs_to_activity(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'cultural',
            'activity_date' => now(),
        ]);

        $culturalWork = CulturalWork::create([
            'activity_id' => $activity->id,
            'work_type' => 'pruning',
        ]);

        $this->assertEquals($activity->id, $culturalWork->activity->id);
        $this->assertInstanceOf(AgriculturalActivity::class, $culturalWork->activity);
    }

    public function test_hours_worked_is_cast_to_decimal(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'cultural',
            'activity_date' => now(),
        ]);

        $culturalWork = CulturalWork::create([
            'activity_id' => $activity->id,
            'hours_worked' => 8.5,
        ]);

        // Los campos decimal:2 devuelven strings en Laravel
        $this->assertIsString($culturalWork->hours_worked);
        $this->assertEquals('8.50', $culturalWork->hours_worked);
    }

    public function test_cultural_work_can_store_all_fields(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'cultural',
            'activity_date' => now(),
        ]);

        $culturalWork = CulturalWork::create([
            'activity_id' => $activity->id,
            'work_type' => 'pruning',
            'hours_worked' => 6.5,
            'workers_count' => 4,
            'description' => 'Pruning of main branches',
        ]);

        $this->assertEquals('pruning', $culturalWork->work_type);
        $this->assertEquals(6.5, $culturalWork->hours_worked);
        $this->assertEquals(4, $culturalWork->workers_count);
        $this->assertEquals('Pruning of main branches', $culturalWork->description);
    }

    public function test_cultural_work_can_have_nullable_fields(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'cultural',
            'activity_date' => now(),
        ]);

        $culturalWork = CulturalWork::create([
            'activity_id' => $activity->id,
            'work_type' => 'pruning',
            'hours_worked' => null,
            'workers_count' => null,
            'description' => null,
        ]);

        $this->assertNotNull($culturalWork->id);
        $this->assertNull($culturalWork->hours_worked);
        $this->assertNull($culturalWork->workers_count);
        $this->assertNull($culturalWork->description);
    }
}

