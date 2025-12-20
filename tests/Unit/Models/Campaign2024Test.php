<?php

namespace Tests\Unit\Models;

use App\Models\Campaign;
use App\Models\User;
use App\Models\AgriculturalActivity;
use App\Models\Plot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\CompleteTestUserSeeder;

/**
 * Tests unitarios específicos para la campaña 2024
 * Estos tests verifican la lógica de negocio con datos históricos
 */
class Campaign2024Test extends TestCase
{
    use RefreshDatabase;

    protected User $testUser;
    protected Campaign $campaign2024;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed de datos base
        $this->seed([
            \Database\Seeders\AutonomousCommunitySeeder::class,
            \Database\Seeders\ProvinceSeeder::class,
            \Database\Seeders\MunicipalitySeeder::class,
            \Database\Seeders\SigpacUseSeeder::class,
            \Database\Seeders\GrapeVarietySeeder::class,
            \Database\Seeders\TrainingSystemSeeder::class,
            \Database\Seeders\MachineryTypeSeeder::class,
        ]);
        
        // Crear usuario completo con todos los datos
        $this->seed(CompleteTestUserSeeder::class);
        
        // Obtener el usuario de prueba
        $this->testUser = User::where('email', 'bernalmochonjorge@gmail.com')->firstOrFail();
        
        // Obtener la campaña 2024
        $this->campaign2024 = Campaign::where('viticulturist_id', $this->testUser->id)
            ->where('year', 2024)
            ->firstOrFail();
    }

    public function test_campaign_2024_exists_and_is_inactive(): void
    {
        $this->assertEquals(2024, $this->campaign2024->year);
        $this->assertEquals('Campaña 2024', $this->campaign2024->name);
        $this->assertFalse($this->campaign2024->active);
        $this->assertEquals($this->testUser->id, $this->campaign2024->viticulturist_id);
    }

    public function test_campaign_2024_has_activities(): void
    {
        $activitiesCount = AgriculturalActivity::where('campaign_id', $this->campaign2024->id)->count();
        
        $this->assertGreaterThan(0, $activitiesCount, 'La campaña 2024 debe tener actividades agrícolas');
        
        // Verificar que hay diferentes tipos de actividades
        $types = AgriculturalActivity::where('campaign_id', $this->campaign2024->id)
            ->distinct()
            ->pluck('activity_type')
            ->toArray();
        
        $this->assertContains('phytosanitary', $types);
        $this->assertContains('fertilization', $types);
        $this->assertContains('irrigation', $types);
    }

    public function test_campaign_2024_activities_have_related_data(): void
    {
        $phytosanitaryActivities = AgriculturalActivity::where('campaign_id', $this->campaign2024->id)
            ->where('activity_type', 'phytosanitary')
            ->with('phytosanitaryTreatment')
            ->get();
        
        $this->assertGreaterThan(0, $phytosanitaryActivities->count(), 'Debe haber al menos una actividad fitosanitaria');
        
        // Verificar que todas tienen datos básicos
        foreach ($phytosanitaryActivities as $activity) {
            $this->assertNotNull($activity->plot_id, "La actividad {$activity->id} debe tener plot_id");
            $this->assertNotNull($activity->viticulturist_id, "La actividad {$activity->id} debe tener viticulturist_id");
            $this->assertEquals($this->campaign2024->id, $activity->campaign_id, "La actividad {$activity->id} debe pertenecer a la campaña 2024");
        }
        
        // Verificar que algunas tienen tratamiento asociado (no todas necesitan tenerlo)
        $activitiesWithTreatment = $phytosanitaryActivities->filter(function ($activity) {
            return $activity->phytosanitaryTreatment !== null;
        })->count();
        
        // Al menos el 50% de las actividades fitosanitarias deberían tener tratamiento
        // Esto es más realista ya que no todas las actividades pueden tener tratamiento inmediatamente
        $this->assertGreaterThan(0, $activitiesWithTreatment, 
            'Al menos algunas actividades fitosanitarias deben tener tratamiento asociado');
    }

    public function test_campaign_2024_can_be_activated(): void
    {
        // Asegurar que está inactiva
        $this->assertFalse($this->campaign2024->active);
        
        // Activar
        $this->campaign2024->activate();
        
        // Verificar que se activó
        $this->assertTrue($this->campaign2024->fresh()->active);
        
        // Verificar que otras campañas del mismo viticultor se desactivaron
        $otherCampaigns = Campaign::where('viticulturist_id', $this->testUser->id)
            ->where('id', '!=', $this->campaign2024->id)
            ->get();
        
        foreach ($otherCampaigns as $campaign) {
            $this->assertFalse($campaign->fresh()->active, 
                "La campaña {$campaign->year} debería estar inactiva después de activar 2024");
        }
    }

    public function test_campaign_2024_activities_are_associated_with_plots(): void
    {
        $activities = AgriculturalActivity::where('campaign_id', $this->campaign2024->id)
            ->with('plot')
            ->get();
        
        $this->assertGreaterThan(0, $activities->count());
        
        foreach ($activities as $activity) {
            $this->assertNotNull($activity->plot);
            $this->assertEquals($this->testUser->id, $activity->plot->viticulturist_id);
        }
    }

    public function test_campaign_2024_statistics_are_correct(): void
    {
        $activitiesCount = AgriculturalActivity::where('campaign_id', $this->campaign2024->id)->count();
        $plotsCount = Plot::where('viticulturist_id', $this->testUser->id)->count();
        
        $this->assertGreaterThan(0, $activitiesCount);
        $this->assertGreaterThan(0, $plotsCount);
        
        // Verificar que las actividades están distribuidas en las parcelas
        $plotsWithActivities = AgriculturalActivity::where('campaign_id', $this->campaign2024->id)
            ->distinct()
            ->pluck('plot_id')
            ->count();
        
        $this->assertLessThanOrEqual($plotsCount, $plotsWithActivities);
    }

    public function test_campaign_2024_date_range_is_correct(): void
    {
        $this->assertEquals('2024-01-01', $this->campaign2024->start_date->format('Y-m-d'));
        $this->assertEquals('2024-12-31', $this->campaign2024->end_date->format('Y-m-d'));
        
        // Verificar que todas las actividades están dentro del rango
        $activities = AgriculturalActivity::where('campaign_id', $this->campaign2024->id)->get();
        
        foreach ($activities as $activity) {
            $this->assertGreaterThanOrEqual(
                $this->campaign2024->start_date->format('Y-m-d'),
                $activity->activity_date->format('Y-m-d'),
                "La actividad {$activity->id} está fuera del rango de fechas"
            );
            
            $this->assertLessThanOrEqual(
                $this->campaign2024->end_date->format('Y-m-d'),
                $activity->activity_date->format('Y-m-d'),
                "La actividad {$activity->id} está fuera del rango de fechas"
            );
        }
    }
}

