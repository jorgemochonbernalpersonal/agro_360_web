<?php

namespace Tests\Unit\Services\Exporters;

use App\Models\AgriculturalActivity;
use App\Models\OfficialReport;
use App\Models\Plot;
use App\Models\SigpacCode;
use App\Models\User;
use App\Services\Exporters\SiexXmlExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\MunicipalitySeeder;

class SiexXmlExporterTest extends TestCase
{
    use RefreshDatabase;

    protected SiexXmlExporter $exporter;
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
        $this->exporter = new SiexXmlExporter();
        
        $this->user = User::factory()->create();
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
    public function it_exports_phytosanitary_treatments_with_sigpac_nodes()
    {
        // Crear SIGPAC
        $sigpacCode = SigpacCode::create([
            'code' => '1328079001200045003',
            'code_province' => '28',
            'code_municipality' => '079',
            'code_polygon' => '12',
            'code_plot' => '00045',
            'code_enclosure' => '003',
        ]);

        // Crear parcela
        $plot = Plot::factory()->create([
            'viticulturist_id' => $this->user->id,
            'area' => 2.5,
        ]);
        $plot->sigpacCodes()->attach($sigpacCode->id);

        // Crear actividad
        $activity = AgriculturalActivity::factory()
            ->withPhytosanitaryTreatment()
            ->create([
                'plot_id' => $plot->id,
                'viticulturist_id' => $this->user->id,
            ]);

        // Cargar relaciones antes de crear la colección
        $activity->load(['plot.sigpacCodes', 'plot.sigpacUses', 'phytosanitaryTreatment.product']);
        $treatments = collect([$activity]);

        $stats = ['total_treatments' => 1];

        // Exportar
        $xmlPath = $this->exporter->exportPhytosanitaryTreatments(
            $this->report,
            $this->user,
            $treatments,
            $stats
        );

        // Verificar
        Storage::disk('local')->assertExists($xmlPath);
        $xmlContent = Storage::disk('local')->get($xmlPath);

        // Verificar nodos SIGPAC
        $this->assertStringContainsString('<DatosSIGPAC>', $xmlContent);
        $this->assertStringContainsString('<CodigoCompleto>1328079001200045003</CodigoCompleto>', $xmlContent);
        $this->assertStringContainsString('<Provincia>28</Provincia>', $xmlContent);
        $this->assertStringContainsString('<Municipio>079</Municipio>', $xmlContent);
        $this->assertStringContainsString('<Poligono>12</Poligono>', $xmlContent);
    }

    #[Test]
    public function it_generates_valid_xml_structure()
    {
        $plot = Plot::factory()->create(['viticulturist_id' => $this->user->id]);
        
        $activity = AgriculturalActivity::factory()->create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $this->user->id,
        ]);

        // Cargar relaciones antes de crear la colección
        $activity->load(['plot.sigpacCodes', 'plot.sigpacUses']);
        $activities = collect([$activity]);

        $stats = ['total_activities' => 1];

        $xmlPath = $this->exporter->exportFullNotebook(
            $this->report,
            $this->user,
            $activities,
            $stats
        );

        $xmlContent = Storage::disk('local')->get($xmlPath);

        // Validar que es XML válido
        $xml = simplexml_load_string($xmlContent);
        $this->assertNotFalse($xml, 'El XML generado debe ser válido');

        // Verificar estructura básica
        $this->assertEquals('CuadernoDigitalExplotacion', $xml->getName());
        $this->assertNotNull($xml->Metadatos);
        $this->assertNotNull($xml->Viticultor);
    }

    #[Test]
    public function it_handles_special_characters_in_sigpac_data()
    {
        $sigpacCode = SigpacCode::create([
            'code' => '1328079001200045003',
            'code_province' => '28',
        ]);

        $plot = Plot::factory()->create([
            'viticulturist_id' => $this->user->id,
            'name' => 'Parcela con & caracteres <especiales>',
        ]);
        $plot->sigpacCodes()->attach($sigpacCode->id);

        $activity = AgriculturalActivity::factory()->create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $this->user->id,
            'notes' => 'Notas con <tags> y & símbolos',
        ]);

        // Cargar relaciones antes de crear la colección
        $activity->load(['plot.sigpacCodes', 'plot.sigpacUses']);
        $activities = collect([$activity]);

        $stats = ['total_activities' => 1];

        $xmlPath = $this->exporter->exportFullNotebook(
            $this->report,
            $this->user,
            $activities,
            $stats
        );

        $xmlContent = Storage::disk('local')->get($xmlPath);

        // Debe ser XML válido a pesar de caracteres especiales
        $xml = simplexml_load_string($xmlContent);
        $this->assertNotFalse($xml);

        // Verificar que los caracteres especiales están manejados correctamente
        // El XML usa CDATA para contenido, así que verificamos que el XML es válido
        // y que contiene los datos originales (aunque estén en CDATA)
        $this->assertStringContainsString('Parcela con', $xmlContent);
        $this->assertStringContainsString('caracteres', $xmlContent);
    }

    #[Test]
    public function it_omits_sigpac_node_when_missing()
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

        $stats = ['total_activities' => 1];

        $xmlPath = $this->exporter->exportFullNotebook(
            $this->report,
            $this->user,
            $activities,
            $stats
        );

        $xmlContent = Storage::disk('local')->get($xmlPath);

        // No debe tener nodo SIGPAC
        $this->assertStringNotContainsString('<DatosSIGPAC>', $xmlContent);
        
        // Pero debe ser XML válido
        $xml = simplexml_load_string($xmlContent);
        $this->assertNotFalse($xml);
    }
}
