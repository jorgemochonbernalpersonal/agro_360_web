<?php

namespace Tests\Unit\Jobs;

use App\Jobs\GenerateOfficialReportJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\WithGeographyData;

/**
 * Tests optimizados del GenerateOfficialReportJob
 *
 * Nota: Estos tests verifican la lógica básica del Job sin generar PDFs reales.
 * Los tests de integración completa (con PDFs) se realizan en Feature tests.
 */
class GenerateOfficialReportJobTest extends TestCase
{
    use RefreshDatabase, WithGeographyData;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear datos geográficos básicos
        $this->createGeographyData();

        // Configurar Storage fake
        Storage::fake('local');

        // Fake Mail para no enviar emails reales
        Mail::fake();
    }

    /**
     * Test que el job puede ser instanciado correctamente
     */
    public function test_job_can_be_instantiated(): void
    {
        $user = User::factory()->create();

        $job = new GenerateOfficialReportJob(
            1,
            $user->id,
            'phytosanitary_treatments',
            [
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
            'password123'
        );

        $this->assertInstanceOf(GenerateOfficialReportJob::class, $job);
        $this->assertEquals(1, $job->reportId);
        $this->assertEquals($user->id, $job->userId);
        $this->assertEquals('phytosanitary_treatments', $job->reportType);
        $this->assertIsArray($job->parameters);
        $this->assertEquals('password123', $job->password);
    }

    /**
     * Test que el job tiene configuración de reintentos
     */
    public function test_job_has_retry_configuration(): void
    {
        $user = User::factory()->create();

        $job = new GenerateOfficialReportJob(
            1,
            $user->id,
            'phytosanitary_treatments',
            ['start_date' => now()->subDays(30)->toDateString(), 'end_date' => now()->toDateString()],
            'password123'
        );

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(600, $job->timeout);
        $this->assertEquals([60, 120, 300], $job->backoff);
    }

    /**
     * Test que el job acepta diferentes tipos de reportes
     */
    public function test_job_accepts_different_report_types(): void
    {
        $user = User::factory()->create();

        $phytosanitaryJob = new GenerateOfficialReportJob(
            1,
            $user->id,
            'phytosanitary_treatments',
            ['start_date' => now()->subDays(30)->toDateString(), 'end_date' => now()->toDateString()],
            'password123'
        );

        $notebookJob = new GenerateOfficialReportJob(
            2,
            $user->id,
            'full_digital_notebook',
            ['campaign_id' => 1],
            'password123'
        );

        $this->assertEquals('phytosanitary_treatments', $phytosanitaryJob->reportType);
        $this->assertEquals('full_digital_notebook', $notebookJob->reportType);
    }

    /**
     * Test que el job maneja parámetros con filtros de fecha opcionales
     */
    public function test_job_handles_optional_date_filters(): void
    {
        $user = User::factory()->create();

        // Job con filtros de fecha
        $jobWithDates = new GenerateOfficialReportJob(
            1,
            $user->id,
            'full_digital_notebook',
            [
                'campaign_id' => 1,
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ],
            'password123'
        );

        // Job sin filtros de fecha
        $jobWithoutDates = new GenerateOfficialReportJob(
            2,
            $user->id,
            'full_digital_notebook',
            ['campaign_id' => 1],
            'password123'
        );

        $this->assertArrayHasKey('start_date', $jobWithDates->parameters);
        $this->assertArrayNotHasKey('start_date', $jobWithoutDates->parameters);
    }
}
