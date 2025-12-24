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

class SiexXmlExporterTest extends TestCase
{
    use RefreshDatabase;

    protected SiexXmlExporter $exporter;
    protected User $user;
    protected OfficialReport $report;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('local');
        $this->exporter = new SiexXmlExporter();
        
        $this->user = User::factory()->create();
        $this->report = OfficialReport::factory()->create([
            'user_id' => $this->user->id,
            'verification_code' => 'TEST123',
        ]);
    }

    /** @test */
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
            'total_area' => 2.5,
        ]);
        $plot->sigpacCodes()->attach($sigpacCode->id);

        // Crear actividad
        $treatments = collect([
            AgriculturalActivity::factory()
                ->withPhytosanitaryTreatment()
                ->create([
                    'plot_id' => $plot->id,
                    'viticulturist_id' => $this->user->id,
                ]),
        ]);

        $treatments->load(['plot.sigpacCodes', 'plot.sigpacUses', 'phytosanitaryTreatment.product']);

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

    /** @test */
    public function it_generates_valid_xml_structure()
    {
        $plot = Plot::factory()->create(['viticulturist_id' => $this->user->id]);
        
        $activities = collect([
            AgriculturalActivity::factory()->create([
                'plot_id' => $plot->id,
                'viticulturist_id' => $this->user->id,
            ]),
        ]);

        $activities->load(['plot.sigpacCodes', 'plot.sigpacUses']);

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

    /** @test */
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

        $activities = collect([
            AgriculturalActivity::factory()->create([
                'plot_id' => $plot->id,
                'viticulturist_id' => $this->user->id,
                'notes' => 'Notas con <tags> y & símbolos',
            ]),
        ]);

        $activities->load(['plot.sigpacCodes', 'plot.sigpacUses']);

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

        // Verificar que escaped correctamente
        $this->assertStringContainsString('&amp;', $xmlContent);
        $this->assertStringContainsString('&lt;', $xmlContent);
    }

    /** @test */
    public function it_omits_sigpac_node_when_missing()
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
