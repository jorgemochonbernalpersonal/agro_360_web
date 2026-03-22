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

    // ── PAC: campos UF nitrógeno/fósforo/potasio ──────────────────────────────

    public function test_npk_uf_fields_stored_with_decimal3_precision(): void
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
            'activity_id'    => $activity->id,
            'nitrogen_uf'    => 123.456,
            'phosphorus_uf'  => 78.9,
            'potassium_uf'   => 45.0,
        ]);

        $this->assertEquals('123.456', $fertilization->nitrogen_uf);
        $this->assertEquals('78.900', $fertilization->phosphorus_uf);
        $this->assertEquals('45.000', $fertilization->potassium_uf);
    }

    public function test_npk_uf_fields_are_nullable(): void
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
            'activity_id'   => $activity->id,
            'nitrogen_uf'   => null,
            'phosphorus_uf' => null,
            'potassium_uf'  => null,
        ]);

        $this->assertNull($fertilization->nitrogen_uf);
        $this->assertNull($fertilization->phosphorus_uf);
        $this->assertNull($fertilization->potassium_uf);
    }

    public function test_burial_date_is_cast_to_carbon_date(): void
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

        $burialDate = now()->subDays(3)->format('Y-m-d');

        $fertilization = Fertilization::create([
            'activity_id'  => $activity->id,
            'fertilizer_type' => 'Fertilizante orgánico',
            'manure_type'  => 'Estiércol vacuno',
            'burial_date'  => $burialDate,
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $fertilization->burial_date);
        $this->assertEquals($burialDate, $fertilization->burial_date->format('Y-m-d'));
    }

    public function test_organic_pac_fields_stored_correctly(): void
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
            'activity_id'               => $activity->id,
            'fertilizer_type'           => 'Fertilizante orgánico',
            'manure_type'               => 'Estiércol vacuno',
            'burial_date'               => now()->subDays(1)->format('Y-m-d'),
            'emission_reduction_method' => 'Enterrado en menos de 24h',
        ]);

        $this->assertEquals('Fertilizante orgánico', $fertilization->fertilizer_type);
        $this->assertEquals('Estiércol vacuno', $fertilization->manure_type);
        $this->assertEquals('Enterrado en menos de 24h', $fertilization->emission_reduction_method);
    }
}

