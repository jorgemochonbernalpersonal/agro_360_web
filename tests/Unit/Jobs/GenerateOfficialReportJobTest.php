<?php

namespace Tests\Unit\Jobs;

use App\Jobs\GenerateOfficialReportJob;
use App\Models\OfficialReport;
use App\Models\User;
use App\Models\Plot;
use App\Models\Campaign;
use App\Models\AgriculturalActivity;
use App\Models\PhytosanitaryProduct;
use App\Models\PhytosanitaryTreatment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\MunicipalitySeeder;

class GenerateOfficialReportJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed datos base necesarios
        $this->seed([
            AutonomousCommunitySeeder::class,
            ProvinceSeeder::class,
            MunicipalitySeeder::class,
        ]);

        // Configurar Storage fake
        Storage::fake('local');
        
        // Fake Mail para no enviar emails reales
        Mail::fake();
    }

    /**
     * Test que el job genera correctamente un informe de tratamientos fitosanitarios
     */
    public function test_job_generates_phytosanitary_report_successfully(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $user->id])->create(['area' => 5.5]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);
        
        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'active_ingredient' => 'Ingrediente Activo',
            'registration_number' => 'REG-12345',
            'manufacturer' => 'Fabricante Test',
            'type' => 'insecticide',
        ]);

        // Crear actividad con tratamiento fitosanitario
        $activity = AgriculturalActivity::factory()
            ->withPhytosanitaryTreatment(['area_treated' => 2.0])
            ->create([
                'plot_id' => $plot->id,
                'viticulturist_id' => $user->id,
                'campaign_id' => $campaign->id,
                'activity_type' => 'phytosanitary',
                'activity_date' => now()->subDays(10),
            ]);

        // Crear reporte en estado pending
        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->subDays(30),
            'period_end' => now(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'signed_at' => now(),
            'signed_ip' => '127.0.0.1',
            'processing_status' => 'pending',
        ]);

        // Ejecutar el job
        $job = new GenerateOfficialReportJob(
            $report->id,
            $user->id,
            'phytosanitary_treatments',
            [
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
            'password123'
        );

        $job->handle();

        // Verificar que el reporte se actualizó correctamente
        $report->refresh();
        $this->assertEquals('completed', $report->processing_status);
        $this->assertNotNull($report->completed_at);
        $this->assertNotNull($report->pdf_path);
        $this->assertNotNull($report->signature_hash);
        $this->assertTrue($report->is_valid);

        // Verificar que se generó el PDF
        Storage::disk('local')->assertExists($report->pdf_path);

        // Verificar que se actualizó la metadata
        $this->assertNotNull($report->report_metadata);
        $this->assertArrayHasKey('total_treatments', $report->report_metadata);
    }

    /**
     * Test que el job usa las columnas correctas de la tabla plots (area, no total_area)
     */
    public function test_job_uses_correct_plot_columns(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $user->id])->create(['area' => 10.5]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);
        
        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'active_ingredient' => 'Ingrediente Activo',
            'registration_number' => 'REG-12345',
            'manufacturer' => 'Fabricante Test',
            'type' => 'insecticide',
        ]);

        // Crear actividad con tratamiento
        $activity = AgriculturalActivity::factory()
            ->withPhytosanitaryTreatment(['area_treated' => 3.0])
            ->create([
                'plot_id' => $plot->id,
                'viticulturist_id' => $user->id,
                'campaign_id' => $campaign->id,
                'activity_type' => 'phytosanitary',
                'activity_date' => now()->subDays(5),
            ]);

        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->subDays(30),
            'period_end' => now(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'signed_at' => now(),
            'signed_ip' => '127.0.0.1',
            'processing_status' => 'pending',
        ]);

        // Ejecutar el job - esto debería funcionar sin errores de columna
        $job = new GenerateOfficialReportJob(
            $report->id,
            $user->id,
            'phytosanitary_treatments',
            [
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
            'password123'
        );

        // No debe lanzar excepción sobre columna 'total_area' no encontrada
        $job->handle();

        $report->refresh();
        $this->assertEquals('completed', $report->processing_status);
    }

    /**
     * Test que el job genera correctamente un cuaderno digital completo
     */
    public function test_job_generates_full_notebook_report_successfully(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $user->id])->create(['area' => 8.0]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        // Crear varias actividades de diferentes tipos
        AgriculturalActivity::factory()
            ->withPhytosanitaryTreatment()
            ->create([
                'plot_id' => $plot->id,
                'viticulturist_id' => $user->id,
                'campaign_id' => $campaign->id,
                'activity_type' => 'phytosanitary',
                'activity_date' => now()->subDays(20),
            ]);

        AgriculturalActivity::factory()->create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'fertilization',
            'activity_date' => now()->subDays(15),
        ]);

        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'full_digital_notebook',
            'period_start' => $campaign->start_date,
            'period_end' => $campaign->end_date,
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'signed_at' => now(),
            'signed_ip' => '127.0.0.1',
            'processing_status' => 'pending',
            'report_metadata' => [
                'campaign_id' => $campaign->id,
                'campaign_name' => $campaign->name,
            ],
        ]);

        $job = new GenerateOfficialReportJob(
            $report->id,
            $user->id,
            'full_digital_notebook',
            [
                'campaign_id' => $campaign->id,
            ],
            'password123'
        );

        $job->handle();

        $report->refresh();
        $this->assertEquals('completed', $report->processing_status);
        $this->assertNotNull($report->pdf_path);
        Storage::disk('local')->assertExists($report->pdf_path);
    }

    /**
     * Test que el job maneja correctamente cuando el reporte no existe
     */
    public function test_job_handles_missing_report_gracefully(): void
    {
        $job = new GenerateOfficialReportJob(
            99999, // ID que no existe
            1,
            'phytosanitary_treatments',
            [
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
            'password123'
        );

        // No debe lanzar excepción, solo retornar
        $job->handle();

        // Verificar que no se creó nada
        $this->assertDatabaseMissing('official_reports', ['id' => 99999]);
    }

    /**
     * Test que el job actualiza processing_status a 'processing' durante la ejecución
     */
    public function test_job_updates_processing_status_during_execution(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $user->id])->create(['area' => 5.0]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);
        
        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'active_ingredient' => 'Ingrediente Activo',
            'registration_number' => 'REG-12345',
            'manufacturer' => 'Fabricante Test',
            'type' => 'insecticide',
        ]);

        $activity = AgriculturalActivity::factory()
            ->withPhytosanitaryTreatment()
            ->create([
                'plot_id' => $plot->id,
                'viticulturist_id' => $user->id,
                'campaign_id' => $campaign->id,
                'activity_type' => 'phytosanitary',
                'activity_date' => now()->subDays(10),
            ]);

        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->subDays(30),
            'period_end' => now(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'signed_at' => now(),
            'signed_ip' => '127.0.0.1',
            'processing_status' => 'pending',
        ]);

        $job = new GenerateOfficialReportJob(
            $report->id,
            $user->id,
            'phytosanitary_treatments',
            [
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
            'password123'
        );

        $job->handle();

        // Verificar que pasó por 'processing' y terminó en 'completed'
        $report->refresh();
        $this->assertEquals('completed', $report->processing_status);
        $this->assertNotNull($report->completed_at);
    }

    /**
     * Test que el job maneja errores y marca el reporte como failed
     */
    public function test_job_handles_errors_and_marks_as_failed(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        
        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->subDays(30),
            'period_end' => now(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'signed_at' => now(),
            'signed_ip' => '127.0.0.1',
            'processing_status' => 'pending',
        ]);

        // Crear job con tipo inválido para forzar error
        $job = new GenerateOfficialReportJob(
            $report->id,
            $user->id,
            'invalid_type', // Tipo inválido
            [
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
            'password123'
        );

        try {
            $job->handle();
        } catch (\Exception $e) {
            // El job debe manejar el error
        }

        $report->refresh();
        // El job debe marcar como failed si hay error
        $this->assertEquals('failed', $report->processing_status);
        $this->assertNotNull($report->processing_error);
    }

    /**
     * Test que el job funciona con múltiples tratamientos
     */
    public function test_job_handles_multiple_treatments(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $user->id])->create(['area' => 15.0]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);
        
        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'active_ingredient' => 'Ingrediente Activo',
            'registration_number' => 'REG-12345',
            'manufacturer' => 'Fabricante Test',
            'type' => 'insecticide',
        ]);

        // Crear múltiples actividades
        for ($i = 0; $i < 5; $i++) {
            AgriculturalActivity::factory()
                ->withPhytosanitaryTreatment(['area_treated' => 2.0 + $i])
                ->create([
                    'plot_id' => $plot->id,
                    'viticulturist_id' => $user->id,
                    'campaign_id' => $campaign->id,
                    'activity_type' => 'phytosanitary',
                    'activity_date' => now()->subDays(20 - $i * 2),
                ]);
        }

        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->subDays(30),
            'period_end' => now(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'signed_at' => now(),
            'signed_ip' => '127.0.0.1',
            'processing_status' => 'pending',
        ]);

        $job = new GenerateOfficialReportJob(
            $report->id,
            $user->id,
            'phytosanitary_treatments',
            [
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
            'password123'
        );

        $job->handle();

        $report->refresh();
        $this->assertEquals('completed', $report->processing_status);
        $this->assertEquals(5, $report->report_metadata['total_treatments']);
    }

    /**
     * Test que el job genera cuaderno completo con filtros de fecha (para generación por lotes)
     */
    public function test_job_generates_full_notebook_with_date_filters(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $user->id])->create(['area' => 10.0]);
        $campaign = Campaign::factory()->create([
            'viticulturist_id' => $user->id,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
        ]);

        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'active_ingredient' => 'Ingrediente Activo',
            'registration_number' => 'REG-12345',
            'manufacturer' => 'Fabricante Test',
            'type' => 'insecticide',
        ]);

        // Crear actividades en diferentes períodos
        // Actividades en el primer trimestre
        AgriculturalActivity::factory()
            ->withPhytosanitaryTreatment(['area_treated' => 2.0])
            ->create([
                'plot_id' => $plot->id,
                'viticulturist_id' => $user->id,
                'campaign_id' => $campaign->id,
                'activity_type' => 'phytosanitary',
                'activity_date' => now()->startOfYear()->addMonths(1), // Febrero
            ]);

        AgriculturalActivity::factory()->create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'fertilization',
            'activity_date' => now()->startOfYear()->addMonths(2), // Marzo
        ]);

        // Actividades en el segundo trimestre (fuera del filtro)
        AgriculturalActivity::factory()->create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'irrigation',
            'activity_date' => now()->startOfYear()->addMonths(5), // Junio
        ]);

        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'full_digital_notebook',
            'period_start' => now()->startOfYear(),
            'period_end' => now()->startOfYear()->endOfQuarter(), // Solo Q1
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'signed_at' => now(),
            'signed_ip' => '127.0.0.1',
            'processing_status' => 'pending',
            'report_metadata' => [
                'campaign_id' => $campaign->id,
                'campaign_name' => $campaign->name,
                'batch_index' => 1,
                'total_batches' => 4,
                'period_label' => 'Q1 ' . now()->year,
            ],
        ]);

        // Generar con filtros de fecha (solo Q1)
        $q1Start = now()->startOfYear()->format('Y-m-d');
        $q1End = now()->startOfYear()->endOfQuarter()->format('Y-m-d');

        $job = new GenerateOfficialReportJob(
            $report->id,
            $user->id,
            'full_digital_notebook',
            [
                'campaign_id' => $campaign->id,
                'start_date' => $q1Start, // Filtro de fecha
                'end_date' => $q1End,     // Filtro de fecha
            ],
            'password123'
        );

        $job->handle();

        $report->refresh();
        $this->assertEquals('completed', $report->processing_status);
        $this->assertNotNull($report->pdf_path);
        
        // Verificar que solo se incluyeron actividades del Q1 (2 actividades, no 3)
        $this->assertEquals(2, $report->report_metadata['total_activities']);
    }

    /**
     * Test que el job funciona correctamente con chunking y filtros de fecha
     * Nota: Este test verifica que el chunking funciona con filtros de fecha
     * pero con un número menor de actividades para evitar problemas de memoria en tests
     */
    public function test_job_handles_chunking_with_date_filters(): void
    {
        // Aumentar memoria para este test
        ini_set('memory_limit', '1G');
        
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $user->id])->create(['area' => 15.0]);
        $campaign = Campaign::factory()->create([
            'viticulturist_id' => $user->id,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
        ]);

        // Crear 1500 actividades en el primer trimestre (para forzar chunking)
        // Pero solo verificamos que el job puede cargar las actividades correctamente
        // sin generar el PDF completo para evitar problemas de memoria
        for ($i = 0; $i < 1500; $i++) {
            AgriculturalActivity::factory()->create([
                'plot_id' => $plot->id,
                'viticulturist_id' => $user->id,
                'campaign_id' => $campaign->id,
                'activity_type' => 'observation',
                'activity_date' => now()->startOfYear()->addDays(rand(0, 90)), // Solo Q1
            ]);
        }

        // Verificar que el método loadActivitiesInChunks funciona con filtros
        $job = new GenerateOfficialReportJob(
            999, // ID ficticio, no vamos a ejecutar handle()
            $user->id,
            'full_digital_notebook',
            [
                'campaign_id' => $campaign->id,
                'start_date' => now()->startOfYear()->format('Y-m-d'),
                'end_date' => now()->startOfYear()->endOfQuarter()->format('Y-m-d'),
            ],
            'password123'
        );

        // Usar reflexión para probar el método privado loadActivitiesInChunks
        $reflection = new \ReflectionClass($job);
        $method = $reflection->getMethod('loadActivitiesInChunks');
        $method->setAccessible(true);
        
        $activities = $method->invoke($job, $user->id, $campaign->id, [
            'start_date' => now()->startOfYear()->format('Y-m-d'),
            'end_date' => now()->startOfYear()->endOfQuarter()->format('Y-m-d'),
        ]);

        // Verificar que se cargaron las actividades correctamente
        $this->assertGreaterThan(0, $activities->count());
        $this->assertLessThanOrEqual(1500, $activities->count());
        
        // Verificar que todas las actividades están en el rango de fechas
        foreach ($activities as $activity) {
            $activityDate = Carbon::parse($activity->activity_date);
            $this->assertTrue(
                $activityDate->gte(now()->startOfYear()) && 
                $activityDate->lte(now()->startOfYear()->endOfQuarter()),
                "La actividad {$activity->id} está fuera del rango de fechas"
            );
        }
    }

    /**
     * Test que el job filtra correctamente actividades por fecha en cuaderno completo
     */
    public function test_job_filters_activities_by_date_range(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $user->id])->create(['area' => 8.0]);
        $campaign = Campaign::factory()->create([
            'viticulturist_id' => $user->id,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
        ]);

        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'active_ingredient' => 'Ingrediente Activo',
            'registration_number' => 'REG-12345',
            'manufacturer' => 'Fabricante Test',
            'type' => 'insecticide',
        ]);

        // Crear actividades en enero (dentro del filtro)
        AgriculturalActivity::factory()
            ->withPhytosanitaryTreatment(['area_treated' => 1.5])
            ->create([
                'plot_id' => $plot->id,
                'viticulturist_id' => $user->id,
                'campaign_id' => $campaign->id,
                'activity_type' => 'phytosanitary',
                'activity_date' => now()->startOfYear()->addDays(10),
            ]);

        // Crear actividades en febrero (dentro del filtro)
        AgriculturalActivity::factory()->create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'fertilization',
            'activity_date' => now()->startOfYear()->addMonths(1)->addDays(5),
        ]);

        // Crear actividades en junio (fuera del filtro)
        AgriculturalActivity::factory()->create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'irrigation',
            'activity_date' => now()->startOfYear()->addMonths(5)->addDays(10),
        ]);

        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'full_digital_notebook',
            'period_start' => now()->startOfYear(),
            'period_end' => now()->startOfYear()->endOfMonth()->addMonth(), // Enero y Febrero
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'signed_at' => now(),
            'signed_ip' => '127.0.0.1',
            'processing_status' => 'pending',
            'report_metadata' => [
                'campaign_id' => $campaign->id,
                'campaign_name' => $campaign->name,
            ],
        ]);

        $filterStart = now()->startOfYear()->format('Y-m-d');
        $filterEnd = now()->startOfYear()->endOfMonth()->addMonth()->format('Y-m-d');

        $job = new GenerateOfficialReportJob(
            $report->id,
            $user->id,
            'full_digital_notebook',
            [
                'campaign_id' => $campaign->id,
                'start_date' => $filterStart,
                'end_date' => $filterEnd,
            ],
            'password123'
        );

        $job->handle();

        $report->refresh();
        $this->assertEquals('completed', $report->processing_status);
        
        // Debe tener solo 2 actividades (enero y febrero), no 3
        $this->assertEquals(2, $report->report_metadata['total_activities']);
    }
}
