<?php

namespace Tests\Feature\Tools;

use App\Livewire\Tools\SigpacViewer;
use App\Models\SigpacUse;
use App\Services\Tools\SigpacParcelLookup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class SigpacViewerTest extends TestCase
{
    use RefreshDatabase;

    private const RECORD = [
        'provincia' => 13, 'municipio' => 87, 'agregado' => 0, 'zona' => 0,
        'poligono' => 10, 'parcela' => 20, 'recinto' => 1,
        'superficie' => 2.3185, 'pendiente_media' => 0.6, 'coef_regadio' => 100,
        'admisibilidad' => null, 'incidencias' => '75', 'uso_sigpac' => 'VI',
        'region' => 12, 'altitud' => 688, 'srid' => 4258,
        'wkt' => 'POLYGON((-3.3650 38.8475,-3.3652 38.8492,-3.3643 38.8506,-3.3650 38.8475))',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        RateLimiter::clear('sigpac-viewer:127.0.0.1');
    }

    public function test_page_is_public(): void
    {
        $this->get('/herramientas/visor-sigpac')->assertOk()->assertSee('Visor de recinto SIGPAC');
    }

    public function test_shows_parcel_data_and_dispatches_map_event(): void
    {
        SigpacUse::updateOrCreate(['code' => 'VI'], ['description' => 'Viñedo']);
        Http::fake(['sigpac-hubcloud.es/*' => Http::response([self::RECORD])]);

        $this->fillReference(Livewire::test(SigpacViewer::class))
            ->call('search')
            ->assertHasNoErrors()
            ->assertSet('status', SigpacParcelLookup::STATUS_OK)
            ->assertSee('2,3185 ha')
            ->assertSee('Viñedo')
            ->assertSee('688 m')
            ->assertDispatched('sigpac-parcel-found', wkt: self::RECORD['wkt']);

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/recinfo/13/87/0/0/10/20/1.json'));
    }

    public function test_pasting_full_reference_fills_all_fields(): void
    {
        Livewire::test(SigpacViewer::class)
            ->set('province', '13:87:0:0:10:20:3')
            ->assertSet('province', '13')
            ->assertSet('municipality', '87')
            ->assertSet('polygon', '10')
            ->assertSet('parcel', '20')
            ->assertSet('enclosure', '3');
    }

    public function test_not_found_reference(): void
    {
        Http::fake(['sigpac-hubcloud.es/*' => Http::response([])]);

        $this->fillReference(Livewire::test(SigpacViewer::class))
            ->call('search')
            ->assertSet('status', SigpacParcelLookup::STATUS_NOT_FOUND)
            ->assertSet('result', null)
            ->assertSee('No existe ningún recinto')
            ->assertNotDispatched('sigpac-parcel-found');
    }

    public function test_api_down_is_reported_and_not_cached(): void
    {
        Http::fake(['sigpac-hubcloud.es/*' => Http::sequence()
            ->pushFailedConnection()
            ->push([self::RECORD]),
        ]);

        $this->fillReference(Livewire::test(SigpacViewer::class))
            ->call('search')
            ->assertSet('status', SigpacParcelLookup::STATUS_UNAVAILABLE)
            ->assertSee('no responde');

        // Cuando la API vuelve, la siguiente consulta funciona (el fallo no quedó en caché).
        $this->fillReference(Livewire::test(SigpacViewer::class))
            ->call('search')
            ->assertSet('status', SigpacParcelLookup::STATUS_OK);
    }

    public function test_results_are_cached(): void
    {
        Http::fake(['sigpac-hubcloud.es/*' => Http::response([self::RECORD])]);

        $this->fillReference(Livewire::test(SigpacViewer::class))->call('search');
        $this->fillReference(Livewire::test(SigpacViewer::class))->call('search');

        Http::assertSentCount(1);
    }

    public function test_validates_reference(): void
    {
        Http::fake();

        Livewire::test(SigpacViewer::class)
            ->set('province', 'abc')
            ->call('search')
            ->assertHasErrors(['province', 'municipality', 'polygon', 'parcel']);

        Http::assertNothingSent();
    }

    public function test_is_rate_limited(): void
    {
        Http::fake(['sigpac-hubcloud.es/*' => Http::response([self::RECORD])]);

        for ($i = 0; $i < SigpacViewer::MAX_LOOKUPS_PER_MINUTE; $i++) {
            $this->fillReference(Livewire::test(SigpacViewer::class))->call('search');
        }

        $this->fillReference(Livewire::test(SigpacViewer::class))
            ->call('search')
            ->assertHasErrors('province')
            ->assertSet('result', null);
    }

    public function test_parse_reference_accepts_common_separators(): void
    {
        $lookup = new SigpacParcelLookup;
        $expected = ['province' => 13, 'municipality' => 87, 'aggregate' => 0, 'zone' => 0, 'polygon' => 10, 'parcel' => 20, 'enclosure' => 1];

        $this->assertSame($expected, $lookup->parseReference('13:87:0:0:10:20:1'));
        $this->assertSame($expected, $lookup->parseReference(' 13-87-0-0-10-20-1 '));
        $this->assertSame($expected, $lookup->parseReference('13 87 0 0 10 20 1'));
        $this->assertNull($lookup->parseReference('13:87:10:20'));
    }

    private function fillReference($component)
    {
        return $component
            ->set('province', '13')
            ->set('municipality', '87')
            ->set('polygon', '10')
            ->set('parcel', '20')
            ->set('enclosure', '1');
    }
}
