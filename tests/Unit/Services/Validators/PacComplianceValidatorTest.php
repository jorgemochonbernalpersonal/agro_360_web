<?php

namespace Tests\Unit\Services\Validators;

use App\Models\AgriculturalActivity;
use App\Models\Plot;
use App\Models\SigpacCode;
use App\Models\User;
use App\Services\Validators\PacComplianceValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\MunicipalitySeeder;

class PacComplianceValidatorTest extends TestCase
{
    use RefreshDatabase;

    protected PacComplianceValidator $validator;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed de localización requerido por los factories de Plot
        $this->seed([
            AutonomousCommunitySeeder::class,
            ProvinceSeeder::class,
            MunicipalitySeeder::class,
        ]);
        
        $this->validator = new PacComplianceValidator();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_detects_missing_sigpac()
    {
        // Parcela sin SIGPAC
        $plot = Plot::factory()->create(['viticulturist_id' => $this->user->id]);

        $activity = AgriculturalActivity::factory()->create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $this->user->id,
        ]);

        // Cargar relaciones antes de crear la colección
        $activity->load(['plot.sigpacCodes', 'plot.sigpacUses']);
        $activities = collect([$activity]);

        $result = $this->validator->validateActivities($activities);

        $this->assertFalse($result['is_compliant']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('sin código SIGPAC', $result['errors'][0]['errors'][0]);
        $this->assertEquals(1, $result['stats']['without_sigpac']);
    }

    #[Test]
    public function it_detects_invalid_sigpac_format()
    {
        // SIGPAC con formato incorrecto
        $sigpacCode = SigpacCode::create([
            'code' => '123', // Menos de 19 dígitos
            'code_province' => '28',
        ]);

        $plot = Plot::factory()->create(['viticulturist_id' => $this->user->id]);
        $plot->sigpacCodes()->attach($sigpacCode->id);

        $activity = AgriculturalActivity::factory()->create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $this->user->id,
        ]);

        // Cargar relaciones antes de crear la colección
        $activity->load(['plot.sigpacCodes', 'plot.sigpacUses']);
        $activities = collect([$activity]);

        $result = $this->validator->validateActivities($activities);

        $this->assertTrue($result['has_warnings']);
        $this->assertStringContainsString('formato correcto', $result['warnings'][0]['warnings'][0]);
    }

    #[Test]
    public function it_validates_surface_area_for_treatments()
    {
        // Parcela con 1 hectárea
        $plot = Plot::factory()->create([
            'viticulturist_id' => $this->user->id,
            'area' => 1.0,
        ]);

        $sigpacCode = SigpacCode::create(['code' => str_repeat('1', 19)]);
        $plot->sigpacCodes()->attach($sigpacCode->id);

        // Tratamiento que cubre 2 hectáreas (más que la parcela)
        $activity = AgriculturalActivity::factory()
            ->withPhytosanitaryTreatment(['area_treated' => 2.0])
            ->create([
                'plot_id' => $plot->id,
                'viticulturist_id' => $this->user->id,
            ]);

        // Cargar relaciones antes de crear la colección
        $activity->load(['plot.sigpacCodes', 'plot.sigpacUses', 'phytosanitaryTreatment.product']);
        $activities = collect([$activity]);

        $result = $this->validator->validateActivities($activities);

        $this->assertTrue($result['has_warnings']);
        $this->assertNotEmpty(array_filter($result['warnings'][0]['warnings'], function($warning) {
            return str_contains($warning, 'excede superficie');
        }));
    }

    #[Test]
    public function it_passes_fully_compliant_activities()
    {
        // SIGPAC válido
        $sigpacCode = SigpacCode::create([
            'code' => str_repeat('1', 19),
            'code_province' => '28',
        ]);

        // Crear uso SIGPAC para evitar warnings
        $sigpacUse = \App\Models\SigpacUse::create([
            'code' => 'VI',
            'description' => 'Viñedo',
        ]);

        $plot = Plot::factory()->create([
            'viticulturist_id' => $this->user->id,
            'area' => 2.5,
        ]);
        $plot->sigpacCodes()->attach($sigpacCode->id);
        $plot->sigpacUses()->attach($sigpacUse->id);

        // Actividad de tipo "cultural" que no requiere validaciones específicas
        $activity = AgriculturalActivity::factory()->create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $this->user->id,
            'activity_type' => 'cultural',
            'phenological_stage' => 'floracion', // Para evitar warning
        ]);

        // Cargar relaciones antes de crear la colección
        $activity->load(['plot.sigpacCodes', 'plot.sigpacUses']);
        $activities = collect([$activity]);

        $result = $this->validator->validateActivities($activities);

        $this->assertTrue($result['is_compliant']);
        $this->assertEmpty($result['errors']);
        $this->assertEquals(1, $result['stats']['with_valid_sigpac']);
        $this->assertEquals(100.0, $this->validator->getCompliancePercentage($result));
    }

    #[Test]
    public function it_generates_compliance_report()
    {
        $sigpacCode = SigpacCode::create(['code' => str_repeat('1', 19)]);
        
        // Crear uso SIGPAC para evitar warnings
        $sigpacUse = \App\Models\SigpacUse::create([
            'code' => 'VI',
            'description' => 'Viñedo',
        ]);
        
        $plot = Plot::factory()->create(['viticulturist_id' => $this->user->id]);
        $plot->sigpacCodes()->attach($sigpacCode->id);
        $plot->sigpacUses()->attach($sigpacUse->id);

        // Actividad de tipo "cultural" que no requiere validaciones específicas
        $activity = AgriculturalActivity::factory()->create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $this->user->id,
            'activity_type' => 'cultural',
            'phenological_stage' => 'floracion', // Para evitar warning
        ]);

        // Cargar relaciones antes de crear la colección
        $activity->load(['plot.sigpacCodes', 'plot.sigpacUses']);
        $activities = collect([$activity]);

        $validation = $this->validator->validateActivities($activities);
        $report = $this->validator->generateComplianceReport($validation);

        $this->assertStringContainsString('REPORTE DE CUMPLIMIENTO PAC', $report);
        $this->assertStringContainsString('RESUMEN:', $report);
        $this->assertStringContainsString('Total actividades analizadas', $report);
        $this->assertStringContainsString('CUMPLE CON REQUISITOS PAC', $report);
    }

    #[Test]
    public function it_detects_missing_plot()
    {
        // Actividad sin parcela
        $activity = AgriculturalActivity::factory()->create([
            'plot_id' => null,
            'viticulturist_id' => $this->user->id,
        ]);

        $activities = collect([$activity]);

        $result = $this->validator->validateActivities($activities);

        $this->assertFalse($result['is_compliant']);
        $this->assertStringContainsString('sin parcela', $result['errors'][0]['errors'][0]);
        $this->assertEquals(1, $result['stats']['missing_plot']);
    }

    #[Test]
    public function it_checks_all_activities_have_sigpac()
    {
        // Actividades mixtas
        $plotWithSigpac = Plot::factory()->create();
        $sigpacCode = SigpacCode::create(['code' => str_repeat('1', 19)]);
        $plotWithSigpac->sigpacCodes()->attach($sigpacCode->id);

        $plotWithoutSigpac = Plot::factory()->create();

        $activity1 = AgriculturalActivity::factory()->create(['plot_id' => $plotWithSigpac->id]);
        $activity2 = AgriculturalActivity::factory()->create(['plot_id' => $plotWithoutSigpac->id]);

        // Cargar relaciones antes de crear la colección
        $activity1->load(['plot.sigpacCodes']);
        $activity2->load(['plot.sigpacCodes']);
        $activities = collect([$activity1, $activity2]);

        $hasAll = $this->validator->allActivitiesHaveSigpac($activities);

        $this->assertFalse($hasAll);
    }

    #[Test]
    public function it_calculates_compliance_percentage()
    {
        // 2 de 3 actividades con SIGPAC válido
        $sigpacCode = SigpacCode::create(['code' => str_repeat('1', 19)]);
        
        // Crear uso SIGPAC para evitar warnings
        $sigpacUse = \App\Models\SigpacUse::create([
            'code' => 'VI',
            'description' => 'Viñedo',
        ]);
        
        $plotWithSigpac = Plot::factory()->create();
        $plotWithSigpac->sigpacCodes()->attach($sigpacCode->id);
        $plotWithSigpac->sigpacUses()->attach($sigpacUse->id);

        $plotWithoutSigpac = Plot::factory()->create();

        // Actividades de tipo "cultural" que no requieren validaciones específicas
        $activity1 = AgriculturalActivity::factory()->create([
            'plot_id' => $plotWithSigpac->id,
            'activity_type' => 'cultural',
            'phenological_stage' => 'floracion',
        ]);
        $activity2 = AgriculturalActivity::factory()->create([
            'plot_id' => $plotWithSigpac->id,
            'activity_type' => 'cultural',
            'phenological_stage' => 'floracion',
        ]);
        $activity3 = AgriculturalActivity::factory()->create([
            'plot_id' => $plotWithoutSigpac->id,
            'activity_type' => 'cultural',
        ]);

        // Cargar relaciones antes de crear la colección
        $activity1->load(['plot.sigpacCodes', 'plot.sigpacUses']);
        $activity2->load(['plot.sigpacCodes', 'plot.sigpacUses']);
        $activity3->load(['plot.sigpacCodes', 'plot.sigpacUses']);
        $activities = collect([$activity1, $activity2, $activity3]);

        $validation = $this->validator->validateActivities($activities);
        $percentage = $this->validator->getCompliancePercentage($validation);

        // 2 de 3 actividades cumplen (las que tienen SIGPAC válido)
        // La tercera tiene error porque no tiene SIGPAC
        $this->assertEqualsWithDelta(66.7, $percentage, 0.1);
    }
}
