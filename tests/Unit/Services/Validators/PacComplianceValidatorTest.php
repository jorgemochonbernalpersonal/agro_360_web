<?php

namespace Tests\Unit\Services\Validators;

use App\Models\AgriculturalActivity;
use App\Models\Plot;
use App\Models\SigpacCode;
use App\Models\User;
use App\Services\Validators\PacComplianceValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PacComplianceValidatorTest extends TestCase
{
    use RefreshDatabase;

    protected PacComplianceValidator $validator;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->validator = new PacComplianceValidator();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_detects_missing_sigpac()
    {
        // Parcela sin SIGPAC
        $plot = Plot::factory()->create(['viticulturist_id' => $this->user->id]);

        $activities = collect([
            AgriculturalActivity::factory()->create([
                'plot_id' => $plot->id,
                'viticulturist_id' => $this->user->id,
            ]),
        ]);

        $activities->load(['plot.sigpacCodes', 'plot.sigpacUses']);

        $result = $this->validator->validateActivities($activities);

        $this->assertFalse($result['is_compliant']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('sin código SIGPAC', $result['errors'][0]['errors'][0]);
        $this->assertEquals(1, $result['stats']['without_sigpac']);
    }

    /** @test */
    public function it_detects_invalid_sigpac_format()
    {
        // SIGPAC con formato incorrecto
        $sigpacCode = SigpacCode::create([
            'code' => '123', // Menos de 19 dígitos
            'code_province' => '28',
        ]);

        $plot = Plot::factory()->create(['viticulturist_id' => $this->user->id]);
        $plot->sigpacCodes()->attach($sigpacCode->id);

        $activities = collect([
            AgriculturalActivity::factory()->create([
                'plot_id' => $plot->id,
                'viticulturist_id' => $this->user->id,
            ]),
        ]);

        $activities->load(['plot.sigpacCodes', 'plot.sigpacUses']);

        $result = $this->validator->validateActivities($activities);

        $this->assertTrue($result['has_warnings']);
        $this->assertStringContainsString('formato correcto', $result['warnings'][0]['warnings'][0]);
    }

    /** @test */
    public function it_validates_surface_area_for_treatments()
    {
        // Parcela con 1 hectárea
        $plot = Plot::factory()->create([
            'viticulturist_id' => $this->user->id,
            'total_area' => 1.0,
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

        $activities = collect([$activity]);
        $activities->load(['plot.sigpacCodes', 'plot.sigpacUses', 'phytosanitaryTreatment.product']);

        $result = $this->validator->validateActivities($activities);

        $this->assertTrue($result['has_warnings']);
        $this->assertNotEmpty(array_filter($result['warnings'][0]['warnings'], function($warning) {
            return str_contains($warning, 'excede superficie');
        }));
    }

    /** @test */
    public function it_passes_fully_compliant_activities()
    {
        // SIGPAC válido
        $sigpacCode = SigpacCode::create([
            'code' => str_repeat('1', 19),
            'code_province' => '28',
        ]);

        $plot = Plot::factory()->create([
            'viticulturist_id' => $this->user->id,
            'total_area' => 2.5,
        ]);
        $plot->sigpacCodes()->attach($sigpacCode->id);

        // Actividad válida
        $activities = collect([
            AgriculturalActivity::factory()->create([
                'plot_id' => $plot->id,
                'viticulturist_id' => $this->user->id,
            ]),
        ]);

        $activities->load(['plot.sigpacCodes', 'plot.sigpacUses']);

        $result = $this->validator->validateActivities($activities);

        $this->assertTrue($result['is_compliant']);
        $this->assertEmpty($result['errors']);
        $this->assertEquals(1, $result['stats']['with_valid_sigpac']);
        $this->assertEquals(100.0, $this->validator->getCompliancePercentage($result));
    }

    /** @test */
    public function it_generates_compliance_report()
    {
        $sigpacCode = SigpacCode::create(['code' => str_repeat('1', 19)]);
        $plot = Plot::factory()->create(['viticulturist_id' => $this->user->id]);
        $plot->sigpacCodes()->attach($sigpacCode->id);

        $activities = collect([
            AgriculturalActivity::factory()->create([
                'plot_id' => $plot->id,
                'viticulturist_id' => $this->user->id,
            ]),
        ]);

        $activities->load(['plot.sigpacCodes', 'plot.sigpacUses']);

        $validation = $this->validator->validateActivities($activities);
        $report = $this->validator->generateComplianceReport($validation);

        $this->assertStringContainsString('REPORTE DE CUMPLIMIENTO PAC', $report);
        $this->assertStringContainsString('RESUMEN:', $report);
        $this->assertStringContainsString('Total actividades analizadas', $report);
        $this->assertStringContainsString('CUMPLE CON REQUISITOS PAC', $report);
    }

    /** @test */
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

    /** @test */
    public function it_checks_all_activities_have_sigpac()
    {
        // Actividades mixtas
        $plotWithSigpac = Plot::factory()->create();
        $sigpacCode = SigpacCode::create(['code' => str_repeat('1', 19)]);
        $plotWithSigpac->sigpacCodes()->attach($sigpacCode->id);

        $plotWithoutSigpac = Plot::factory()->create();

        $activities = collect([
            AgriculturalActivity::factory()->create(['plot_id' => $plotWithSigpac->id]),
            AgriculturalActivity::factory()->create(['plot_id' => $plotWithoutSigpac->id]),
        ]);

        $activities->load(['plot.sigpacCodes']);

        $hasAll = $this->validator->allActivitiesHaveSigpac($activities);

        $this->assertFalse($hasAll);
    }

    /** @test */
    public function it_calculates_compliance_percentage()
    {
        // 2 de 3 actividades con SIGPAC válido
        $plotWithSigpac = Plot::factory()->create();
        $sigpacCode = SigpacCode::create(['code' => str_repeat('1', 19)]);
        $plotWithSigpac->sigpacCodes()->attach($sigpacCode->id);

        $plotWithoutSigpac = Plot::factory()->create();

        $activities = collect([
            AgriculturalActivity::factory()->create(['plot_id' => $plotWithSigpac->id]),
            AgriculturalActivity::factory()->create(['plot_id' => $plotWithSigpac->id]),
            AgriculturalActivity::factory()->create(['plot_id' => $plotWithoutSigpac->id]),
        ]);

        $activities->load(['plot.sigpacCodes', 'plot.sigpacUses']);

        $validation = $this->validator->validateActivities($activities);
        $percentage = $this->validator->getCompliancePercentage($validation);

        $this->assertEqualsWithDelta(66.7, $percentage, 0.1);
    }
}
