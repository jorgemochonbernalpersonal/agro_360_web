<?php

namespace Tests\Unit\Services\Exporters;

use App\Models\AgriculturalActivity;
use App\Models\OfficialReport;
use App\Models\Plot;
use App\Models\SigpacCode;
use App\Models\SigpacUse;
use App\Models\User;
use App\Services\Exporters\SiexCsvExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\MunicipalitySeeder;

class SiexCsvExporterTest extends TestCase
{
    use RefreshDatabase;

    protected SiexCsvExporter $exporter;
    protected User $user;
    protected OfficialReport $report;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed de localización requerido por los factories de Plot
        $this->seed([
            AutonomousCommunitySeeder::class,
            ProvinceSeeder::class,
            MunicipalitySeeder::class,
        ]);
        
        Storage::fake('local');
        $this->exporter = new SiexCsvExporter();
        
        // Crear usuario de prueba
        $this->user = User::factory()->create([
            'name' => 'Test Viticultor',
            'email' => 'test@example.com',
        ]);

        // Crear informe de prueba
        $this->report = OfficialReport::create([
            'user_id' => $this->user->id,
            'report_type' => 'phytosanitary_treatments',
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'period_start' => now()->subMonth(),
            'period_end' => now(),
            'signed_at' => now(),
            'signed_ip' => '127.0.0.1',
        ]);
    }

    #[Test]
    public function it_exports_phytosanitary_treatments_with_sigpac_data()
    {
        // Crear código SIGPAC
        $sigpacCode = SigpacCode::create([
            'code' => '1328079001200045003',
            'code_autonomous_community' => '13',
            'code_province' => '28',
            'code_municipality' => '079',
            'code_aggregate' => '0',
            'code_zone' => '0',
            'code_polygon' => '12',
            'code_plot' => '00045',
            'code_enclosure' => '003',
        ]);

        // Crear uso SIGPAC
        $sigpacUse = SigpacUse::create([
            'code' => 'VI',
            'description' => 'Viñedo',
        ]);

        // Crear parcela con SIGPAC
        $plot = Plot::factory()->create([
            'viticulturist_id' => $this->user->id,
            'name' => 'Parcela Test',
            'area' => 2.5,
        ]);
        
        $plot->sigpacCodes()->attach($sigpacCode->id);
        $plot->sigpacUses()->attach($sigpacUse->id);

        // Crear actividades de prueba
        $activity = AgriculturalActivity::factory()
            ->withPhytosanitaryTreatment()
            ->create([
                'plot_id' => $plot->id,
                'viticulturist_id' => $this->user->id,
                'activity_date' => now(),
            ]);

        // Cargar relaciones antes de crear la colección
        $activity->load([
            'plot.sigpacCodes',
            'plot.sigpacUses',
            'phytosanitaryTreatment.product',
        ]);

        $treatments = collect([$activity]);

        $stats = ['total_treatments' => 1];

        // Ejecutar exportación
        $csvPath = $this->exporter->exportPhytosanitaryTreatments(
            $this->report,
            $this->user,
            $treatments,
            $stats
        );

        // Verificar que el archivo se creó
        Storage::disk('local')->assertExists($csvPath);

        // Leer contenido del CSV
        $csvContent = Storage::disk('local')->get($csvPath);

        // Verificar que contiene BOM UTF-8
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csvContent);

        // Verificar que contiene datos SIGPAC
        $this->assertStringContainsString('Código SIGPAC', $csvContent);
        $this->assertStringContainsString('13-28-079-0-0-12-00045-003', $csvContent);
        $this->assertStringContainsString('Viñedo', $csvContent);
        $this->assertStringContainsString('2.5', $csvContent);
    }

    #[Test]
    public function it_exports_full_notebook_with_sigpac_data()
    {
        // Crear código SIGPAC
        $sigpacCode = SigpacCode::create([
            'code' => '1328079001200045003',
            'code_province' => '28',
            'code_municipality' => '079',
            'code_polygon' => '12',
            'code_plot' => '00045',
            'code_enclosure' => '003',
        ]);

        // Crear parcela con SIGPAC
        $plot = Plot::factory()->create([
            'viticulturist_id' => $this->user->id,
            'area' => 3.0,
        ]);
        
        $plot->sigpacCodes()->attach($sigpacCode->id);

        // Crear actividades
        $activity = AgriculturalActivity::factory()->create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $this->user->id,
            'activity_type' => 'irrigation',
        ]);

        // Cargar relaciones antes de crear la colección
        $activity->load(['plot.sigpacCodes', 'plot.sigpacUses']);
        $activities = collect([$activity]);

        $stats = ['total_activities' => 1];

        // Ejecutar exportación
        $csvPath = $this->exporter->exportFullNotebook(
            $this->report,
            $this->user,
            $activities,
            $stats
        );

        // Verificar
        Storage::disk('local')->assertExists($csvPath);
        $csvContent = Storage::disk('local')->get($csvPath);

        $this->assertStringContainsString('Código SIGPAC', $csvContent);
        $this->assertStringContainsString('13-28-079', $csvContent);
    }

    #[Test]
    public function it_uses_spanish_csv_format()
    {
        $activity = AgriculturalActivity::factory()->create([
            'viticulturist_id' => $this->user->id,
        ]);
        $activities = collect([$activity]);

        $stats = ['total_activities' => 1];

        $csvPath = $this->exporter->exportFullNotebook(
            $this->report,
            $this->user,
            $activities,
            $stats
        );

        $csvContent = Storage::disk('local')->get($csvPath);

        // Verificar delimitador punto y coma
        $this->assertStringContainsString(';', $csvContent);
        
        // Verificar UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csvContent);
    }

    #[Test]
    public function it_handles_missing_sigpac_gracefully()
    {
        // Parcela sin SIGPAC
        $plot = Plot::factory()->create([
            'viticulturist_id' => $this->user->id,
        ]);

        $activity = AgriculturalActivity::factory()->create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $this->user->id,
        ]);

        // Cargar relaciones antes de crear la colección
        $activity->load(['plot.sigpacCodes', 'plot.sigpacUses']);
        $activities = collect([$activity]);

        $stats = ['total_activities' => 1];

        // Ejecutar sin que falle
        $csvPath = $this->exporter->exportFullNotebook(
            $this->report,
            $this->user,
            $activities,
            $stats
        );

        Storage::disk('local')->assertExists($csvPath);
        $csvContent = Storage::disk('local')->get($csvPath);

        // Debe mostrar "Sin SIGPAC"
        $this->assertStringContainsString('Sin SIGPAC', $csvContent);
    }
}
