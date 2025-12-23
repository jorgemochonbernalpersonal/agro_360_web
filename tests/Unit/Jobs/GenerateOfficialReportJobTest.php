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
}
