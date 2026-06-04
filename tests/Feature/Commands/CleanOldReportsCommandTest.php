<?php

namespace Tests\Feature\Commands;

use App\Models\OfficialReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CleanOldReportsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_deletes_old_reports(): void
    {
        Storage::fake('local');

        // Crear reportes antiguos
        $oldReport = OfficialReport::factory()->create([
            'created_at' => now()->subDays(100),
            'pdf_path' => 'reports/old_report.pdf',
        ]);

        Storage::put($oldReport->pdf_path, 'fake content');

        // Crear reporte reciente
        $recentReport = OfficialReport::factory()->create([
            'created_at' => now()->subDays(30),
        ]);

        // Ejecutar comando (limpiar reportes > 90 días)
        $this->artisan('reports:clean-old', ['--days' => 90])
            ->assertSuccessful();

        // Verificar que el antiguo fue eliminado
        $this->assertDatabaseMissing('official_reports', ['id' => $oldReport->id]);

        // Verificar que el reciente permanece
        $this->assertDatabaseHas('official_reports', ['id' => $recentReport->id]);
    }

    public function test_command_respects_days_parameter(): void
    {
        $report60days = OfficialReport::factory()->create([
            'created_at' => now()->subDays(60),
        ]);

        $report120days = OfficialReport::factory()->create([
            'created_at' => now()->subDays(120),
        ]);

        // Limpiar reportes > 90 días
        $this->artisan('reports:clean-old', ['--days' => 90])
            ->assertSuccessful();

        $this->assertDatabaseHas('official_reports', ['id' => $report60days->id]);
        $this->assertDatabaseMissing('official_reports', ['id' => $report120days->id]);
    }
}
