<?php

namespace Tests\Feature\Tools;

use App\Livewire\Tools\PhytoSearch;
use App\Models\AppSetting;
use App\Models\PhytoRegistryProduct;
use App\Models\PhytoRegistryUse;
use App\Services\Tools\PhytoRegistryImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class PhytoSearchTest extends TestCase
{
    use RefreshDatabase;

    private const VID = '0101020700000000';

    private const VID_VINIFICACION = '0101020702000000';

    private const LENOSAS = '0101000000000000';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_importer_decodes_mapa_export(): void
    {
        $this->importFixture();

        $this->assertSame(4, PhytoRegistryProduct::count());
        $this->assertSame(5, PhytoRegistryUse::count());
        $this->assertSame('2026-09-10', AppSetting::get(PhytoRegistryProduct::SOURCE_DATE_SETTING));

        $product = PhytoRegistryProduct::where('registration_number', '11179')->sole();
        $this->assertSame('AZUFRE 80%', $product->activeSubstances());
        $this->assertSame(['AZUFRE MOJABLE PLUS'], $product->other_names);
        $this->assertSame('2027-07-31', $product->expires_on->toDateString());

        $oidio = $product->uses()->where('crop_code', self::VID)->sole();
        $this->assertSame(5, $oidio->safety_days);
        $this->assertSame('0,2–0,4 %', $oidio->doseLabel());
        $this->assertSame("Aplicar en pulverización.\nNo superar la dosis.", $oidio->specific_conditions);
    }

    public function test_dose_label_handles_zero_as_missing_bound(): void
    {
        $use = new PhytoRegistryUse(['dose_unit' => '%']);

        $use->forceFill(['dose_min' => 0.3, 'dose_max' => 0]);
        $this->assertSame('0,3 %', $use->doseLabel());

        $use->forceFill(['dose_min' => 0, 'dose_max' => 2.5]);
        $this->assertSame('2,5 %', $use->doseLabel());

        $use->forceFill(['dose_min' => 2, 'dose_max' => 2]);
        $this->assertSame('2 %', $use->doseLabel());

        $use->forceFill(['dose_min' => 0, 'dose_max' => 0, 'dose_unit' => '(ver nota)']);
        $this->assertSame('(ver nota)', $use->doseLabel());
    }

    public function test_safety_labels_keep_free_text_and_detect_not_applicable(): void
    {
        $this->importFixture();

        $labels = PhytoRegistryUse::whereHas('product', fn ($q) => $q->where('status', 'Vigente'))->get()
            ->mapWithKeys(fn ($u) => [$u->agent => $u->safetyLabel()]);

        $this->assertSame('5 días', $labels['Oídio de la vid, Erysiphe necator']);
        $this->assertSame('No procede', $labels['Repilo']);
        $this->assertSame('No procede', $labels['Dicotiledóneas anuales']);
        $this->assertSame('10 (AIRE LIBRE) 3 (INVERNADERO)', $labels['Mildiu de la vid, Plasmopara viticola']);
    }

    public function test_reimport_replaces_uses_and_removes_missing_products(): void
    {
        $this->importFixture();

        app(PhytoRegistryImporter::class)->import($this->export([
            $this->product(1, '11179', 'AZUFRE MOJABLE', 'AZUFRE', [
                $this->use(self::VID, 'Vid', 'Oídio de la vid, Erysiphe necator', 0.3, 0.5, '%', '7'),
            ]),
        ]), minExpectedProducts: 1);

        $this->assertSame(1, PhytoRegistryProduct::count());
        $use = PhytoRegistryUse::sole();
        $this->assertSame(7, $use->safety_days);
    }

    public function test_importer_refuses_suspiciously_small_export(): void
    {
        $this->importFixture();

        try {
            app(PhytoRegistryImporter::class)->import($this->export([]));
            $this->fail('Debería haber abortado');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('se aborta', $e->getMessage());
        }

        $this->assertSame(4, PhytoRegistryProduct::count());
    }

    public function test_importer_rejects_unexpected_format(): void
    {
        $this->expectException(RuntimeException::class);

        app(PhytoRegistryImporter::class)->import('{"foo":1}', minExpectedProducts: 1);
    }

    public function test_command_downloads_and_imports(): void
    {
        $products = [];
        for ($i = 1; $i <= PhytoRegistryImporter::MIN_EXPECTED_PRODUCTS; $i++) {
            $products[] = $this->product($i, (string) (10000 + $i), "PRODUCTO {$i}", 'AZUFRE', [
                $this->use(self::VID, 'Vid', 'Oídio de la vid, Erysiphe necator', 1, 1, '%', '7'),
            ]);
        }
        Http::fake(['servicio.mapa.gob.es/*' => Http::response($this->export($products))]);

        $this->artisan('phyto:import-registry')
            ->expectsOutputToContain('Importados '.PhytoRegistryImporter::MIN_EXPECTED_PRODUCTS.' productos')
            ->assertSuccessful();

        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request['tipoExportacion'] === 'ProductosAutorizados');
    }

    public function test_command_fails_cleanly_when_mapa_is_down(): void
    {
        Sleep::fake(); // los reintentos esperan 5 s entre intentos
        Http::fake(['servicio.mapa.gob.es/*' => Http::response('', 503)]);

        $this->artisan('phyto:import-registry')->assertFailed();

        $this->assertSame(0, PhytoRegistryProduct::count());
    }

    public function test_crop_ancestors(): void
    {
        $this->assertSame(
            ['0101020702000000', '0101020700000000', '0101020000000000', '0101000000000000', '0100000000000000'],
            PhytoSearch::cropWithAncestors('0101020702000000')
        );
        // Un nivel acabado en cero ("10") sigue siendo un nivel.
        $this->assertSame(
            ['0101021000000000', '0101020000000000', '0101000000000000', '0100000000000000'],
            PhytoSearch::cropWithAncestors('0101021000000000')
        );
    }

    public function test_generic_vine_includes_child_crops_labelled(): void
    {
        $this->importFixture();

        Livewire::test(PhytoSearch::class) // "Vid" por defecto
            ->assertSee('AZUFRE MOJABLE')
            ->assertSee('COBRE FLOW')
            ->assertSee('Autorizado para: Vid de vinificación');

        // Y "Vid de mesa" no ve lo autorizado solo para vinificación.
        Livewire::test(PhytoSearch::class)
            ->set('crop', '0101020701000000')
            ->assertSee('AZUFRE MOJABLE')
            ->assertDontSee('COBRE FLOW');

        $this->assertSame('01010207', PhytoSearch::cropPrefix('0101020700000000'));
        $this->assertSame('01', PhytoSearch::cropPrefix('0100000000000000'));
    }

    public function test_page_is_public(): void
    {
        $this->importFixture();

        $this->get('/herramientas/buscador-fitosanitarios')
            ->assertOk()
            ->assertSee('Buscador de fitosanitarios autorizados')
            ->assertSee('AZUFRE MOJABLE');
    }

    public function test_vine_search_includes_parent_groups_and_excludes_cancelled(): void
    {
        $this->importFixture();

        Livewire::test(PhytoSearch::class)
            ->set('crop', self::VID_VINIFICACION)
            ->assertSee('COBRE FLOW')
            ->assertSee('AZUFRE MOJABLE')              // autorizado en "Vid", padre
            ->assertSee('HERBICIDA LEÑOSAS')           // autorizado en "Leñosas"
            ->assertSee('Autorizado para: Leñosas')
            ->assertDontSee('PRODUCTO CANCELADO')
            ->assertDontSee('Repilo');                 // uso de olivo
    }

    public function test_filter_by_agent_and_text(): void
    {
        $this->importFixture();

        Livewire::test(PhytoSearch::class)
            ->set('agent', 'Oídio de la vid, Erysiphe necator')
            ->assertSee('AZUFRE MOJABLE')
            ->assertDontSee('HERBICIDA LEÑOSAS')
            ->set('agent', '')
            ->set('query', 'glifosato')                  // minúsculas contra "GLIFOSATO"
            ->assertSee('HERBICIDA LEÑOSAS')
            ->assertDontSee('AZUFRE MOJABLE')
            ->set('query', 'oxicloruro de cobre')
            ->assertSee('COBRE FLOW')
            ->assertDontSee('HERBICIDA LEÑOSAS')
            ->set('query', '11179')
            ->assertSee('AZUFRE MOJABLE')
            ->assertDontSee('HERBICIDA LEÑOSAS');
    }

    public function test_changing_crop_resets_agent(): void
    {
        $this->importFixture();

        Livewire::test(PhytoSearch::class)
            ->set('agent', 'Oídio de la vid, Erysiphe necator')
            ->set('crop', '0101020200000000')
            ->assertSet('agent', '')
            ->assertSee('Repilo');
    }

    public function test_empty_catalog_message(): void
    {
        Livewire::test(PhytoSearch::class)->assertSee('todavía no se ha cargado');
    }

    private function use(string $cropCode, string $crop, string $agent, float $min, float $max, string $unit, string $safety, array $extra = []): array
    {
        return array_merge([
            'CodigoCultivo' => $cropCode, 'Cultivo' => $crop, 'CodigoAgente' => '1.1', 'Agente' => $agent,
            'Dosis_Min' => $min, 'Dosis_Max' => $max, 'Unidad Medida dosis' => $unit, 'Plazo Seguridad' => $safety,
            'Volumen Caldo' => '200-1000 l/ha', 'Aplicaciones' => '1-6', 'IntervaloAplicaciones' => '7 días',
            'Bbch' => '', 'CondicionamientoEspecifico' => "Aplicar en pulverización.\rNo superar la dosis.", 'TipoUsuario' => 'Profesional',
            'Ambito' => '', 'SistemaCultivo' => '', 'MetodoAplicacion' => 'Pulverización', 'Volumen_Min' => 0.0, 'VolumenMax' => 0.0, 'Unidades Volumen' => '',
        ], $extra);
    }

    /** Exportación con la misma doble codificación que la del MAPA. */
    private function export(array $products, string $date = '2026-09-10T00:00:00'): string
    {
        $content = json_encode(['Productos' => $products]);
        $outer = json_encode(['Id' => 1, 'Tipo' => 'CEX', 'Contenido' => $content, 'Fecha' => $date]);

        return json_encode($outer);
    }

    private function product(int $id, string $reg, string $name, string $substance, array $uses, string $status = 'Vigente'): array
    {
        return [
            'DATOSPRODUCTO' => [
                'IdProducto' => $id, 'Num_Registro' => $reg, 'Nombre' => $name, 'Titular' => 'Titular S.A.',
                'Fabricante' => '', 'Fabrica' => '', 'Formulado' => "{$substance} 80% [WG] P/P", 'Fecha_Registro' => '2000-01-01T00:00:00',
                'Estado' => $status, 'Fecha_Caducidad' => '2027/07/31', 'Fecha_Cancelacion' => '', 'Fecha_LimiteVenta' => '', 'Condicionamiento' => 'Uso fungicida.',
            ],
            'OTRASDENOMINACIONES' => [['Nombre_Origen' => "{$name} PLUS"]],
            'OTROSNOMBRES' => [],
            'COMPOSICION' => [['Nombre Sustancia' => $substance, 'NombreUE' => '', 'Concentracion' => 80.0, 'DescripcionNota' => '%']],
            'USOS' => $uses,
        ];
    }

    private function importFixture(): void
    {
        $raw = $this->export([
            $this->product(1, '11179', 'AZUFRE MOJABLE', 'AZUFRE', [
                $this->use(self::VID, 'Vid', 'Oídio de la vid, Erysiphe necator', 0.2, 0.4, '%', '5'),
                $this->use('0101020200000000', 'Olivo', 'Repilo', 0.3, 0.3, '%', 'NP'),
            ]),
            $this->product(2, '25000', 'COBRE FLOW', 'OXICLORURO DE COBRE', [
                $this->use(self::VID_VINIFICACION, 'Vid de vinificación', 'Mildiu de la vid, Plasmopara viticola', 2, 3, 'l/ha', '10 (AIRE LIBRE) 3 (INVERNADERO)'),
            ]),
            $this->product(3, '30000', 'HERBICIDA LEÑOSAS', 'GLIFOSATO', [
                $this->use(self::LENOSAS, 'Leñosas', 'Dicotiledóneas anuales', 3, 3, 'l/ha', 'NO PROCEDE'),
            ]),
            $this->product(4, '40000', 'PRODUCTO CANCELADO', 'AZUFRE', [
                $this->use(self::VID, 'Vid', 'Oídio de la vid, Erysiphe necator', 1, 1, '%', '7'),
            ], status: 'Cancelado'),
        ]);

        app(PhytoRegistryImporter::class)->import($raw, minExpectedProducts: 1);
    }
}
