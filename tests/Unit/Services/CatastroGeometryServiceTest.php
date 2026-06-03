<?php

namespace Tests\Unit\Services;

use App\Services\CatastroGeometryService;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Tests del parseo GML→WKT (lógica pura, sin BD ni red).
 *
 * Se usa ReflectionMethod para acceder a gmlToWkt() y posListToWktRing()
 * porque son privados; se testean con fixtures GML reales del WFS del Catastro.
 */
class CatastroGeometryServiceTest extends TestCase
{
    private CatastroGeometryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CatastroGeometryService();
    }

    private function callGmlToWkt(string $gml): ?string
    {
        $method = new ReflectionMethod(CatastroGeometryService::class, 'gmlToWkt');
        $method->setAccessible(true);
        return $method->invoke($this->service, $gml);
    }

    private function callPosListToWktRing(string $posList): ?string
    {
        $method = new ReflectionMethod(CatastroGeometryService::class, 'posListToWktRing');
        $method->setAccessible(true);
        return $method->invoke($this->service, $posList);
    }

    // ── posListToWktRing ─────────────────────────────────────────────────────

    public function test_posList_swap_lat_lon_to_lon_lat(): void
    {
        // GML INSPIRE: "lat lon lat lon ..." → WKT necesita "lon lat"
        $posList = '40.12 -3.45 40.13 -3.46 40.14 -3.44 40.12 -3.45';
        $result  = $this->callPosListToWktRing($posList);

        $this->assertNotNull($result);
        // Primer vértice debe ser "-3.45 40.12" (lon primero)
        $this->assertStringStartsWith('-3.45 40.12', $result);
    }

    public function test_posList_returns_null_when_fewer_than_4_points(): void
    {
        // Un anillo válido necesita mínimo 4 puntos (incluyendo cierre)
        $posList = '40.12 -3.45 40.13 -3.46 40.12 -3.45'; // 3 pares = 3 puntos
        $this->assertNull($this->callPosListToWktRing($posList));
    }

    public function test_posList_returns_null_on_out_of_range_coords(): void
    {
        // lat > 90 es inválida
        $posList = '200.0 -3.45 40.13 -3.46 40.14 -3.44 200.0 -3.45';
        $this->assertNull($this->callPosListToWktRing($posList));
    }

    // ── gmlToWkt — POLYGON ───────────────────────────────────────────────────

    public function test_gml_polygon_parsed_correctly(): void
    {
        $gml = $this->sampleGmlPolygon();
        $wkt = $this->callGmlToWkt($gml);

        $this->assertNotNull($wkt);
        $this->assertStringStartsWith('POLYGON', $wkt);
        // El WKT debe contener coordenadas lon-lat (lon ≈ -3.45, primero)
        $this->assertStringContainsString('-3.45', $wkt);
    }

    public function test_gml_multipolygon_produces_multipolygon_wkt(): void
    {
        $gml = $this->sampleGmlMultiPolygon();
        $wkt = $this->callGmlToWkt($gml);

        $this->assertNotNull($wkt);
        $this->assertStringStartsWith('MULTIPOLYGON', $wkt);
    }

    public function test_gml_with_interior_ring_included_in_output(): void
    {
        $gml = $this->sampleGmlWithHole();
        $wkt = $this->callGmlToWkt($gml);

        $this->assertNotNull($wkt);
        // Un POLYGON con agujero tiene 2 anillos separados por coma
        $this->assertStringStartsWith('POLYGON', $wkt);
        // Debe haber dos grupos de paréntesis "(ring),(ring)"
        $this->assertGreaterThanOrEqual(2, substr_count($wkt, '('));
    }

    public function test_gml_invalid_xml_returns_null(): void
    {
        $this->assertNull($this->callGmlToWkt('esto no es xml'));
    }

    public function test_gml_without_surface_or_polygon_returns_null(): void
    {
        $gml = '<root><item>foo</item></root>';
        $this->assertNull($this->callGmlToWkt($gml));
    }

    // ── normalizeReference (vía fetchWkt con ref inválida) ──────────────────

    public function test_fetch_wkt_returns_null_on_short_reference(): void
    {
        // Una refcat de menos de 14 chars debe devolver null sin hacer petición HTTP
        $result = $this->service->fetchWkt('ABC123');
        $this->assertNull($result);
    }

    public function test_fetch_wkt_returns_null_on_null_reference(): void
    {
        $this->assertNull($this->service->fetchWkt(null));
    }

    // ── Fixtures GML ─────────────────────────────────────────────────────────

    private function sampleGmlPolygon(): string
    {
        // Estructura mínima real de una respuesta WFS INSPIRE del Catastro
        return <<<'GML'
<?xml version="1.0" encoding="UTF-8"?>
<wfs:FeatureCollection xmlns:wfs="http://www.opengis.net/wfs/2.0"
                       xmlns:gml="http://www.opengis.net/gml/3.2"
                       xmlns:cp="http://inspire.ec.europa.eu/schemas/cp/4.0">
  <wfs:member>
    <cp:CadastralParcel gml:id="ES.SDGC.CP.32010A02000475">
      <cp:geometry>
        <gml:Surface srsName="urn:ogc:def:crs:EPSG::4326">
          <gml:patches>
            <gml:PolygonPatch>
              <gml:exterior>
                <gml:LinearRing>
                  <gml:posList>
                    40.12 -3.45 40.13 -3.46 40.14 -3.44 40.12 -3.44 40.12 -3.45
                  </gml:posList>
                </gml:LinearRing>
              </gml:exterior>
            </gml:PolygonPatch>
          </gml:patches>
        </gml:Surface>
      </cp:geometry>
    </cp:CadastralParcel>
  </wfs:member>
</wfs:FeatureCollection>
GML;
    }

    private function sampleGmlMultiPolygon(): string
    {
        return <<<'GML'
<?xml version="1.0" encoding="UTF-8"?>
<wfs:FeatureCollection xmlns:wfs="http://www.opengis.net/wfs/2.0"
                       xmlns:gml="http://www.opengis.net/gml/3.2"
                       xmlns:cp="http://inspire.ec.europa.eu/schemas/cp/4.0">
  <wfs:member>
    <cp:CadastralParcel>
      <cp:geometry>
        <gml:Surface srsName="urn:ogc:def:crs:EPSG::4326">
          <gml:patches>
            <gml:PolygonPatch>
              <gml:exterior>
                <gml:LinearRing>
                  <gml:posList>40.12 -3.45 40.13 -3.46 40.14 -3.44 40.12 -3.44 40.12 -3.45</gml:posList>
                </gml:LinearRing>
              </gml:exterior>
            </gml:PolygonPatch>
          </gml:patches>
        </gml:Surface>
        <gml:Surface srsName="urn:ogc:def:crs:EPSG::4326">
          <gml:patches>
            <gml:PolygonPatch>
              <gml:exterior>
                <gml:LinearRing>
                  <gml:posList>41.00 -2.00 41.01 -2.01 41.02 -1.99 41.00 -1.99 41.00 -2.00</gml:posList>
                </gml:LinearRing>
              </gml:exterior>
            </gml:PolygonPatch>
          </gml:patches>
        </gml:Surface>
      </cp:geometry>
    </cp:CadastralParcel>
  </wfs:member>
</wfs:FeatureCollection>
GML;
    }

    private function sampleGmlWithHole(): string
    {
        return <<<'GML'
<?xml version="1.0" encoding="UTF-8"?>
<wfs:FeatureCollection xmlns:wfs="http://www.opengis.net/wfs/2.0"
                       xmlns:gml="http://www.opengis.net/gml/3.2"
                       xmlns:cp="http://inspire.ec.europa.eu/schemas/cp/4.0">
  <wfs:member>
    <cp:CadastralParcel>
      <cp:geometry>
        <gml:Surface srsName="urn:ogc:def:crs:EPSG::4326">
          <gml:patches>
            <gml:PolygonPatch>
              <gml:exterior>
                <gml:LinearRing>
                  <gml:posList>40.10 -3.50 40.20 -3.50 40.20 -3.40 40.10 -3.40 40.10 -3.50</gml:posList>
                </gml:LinearRing>
              </gml:exterior>
              <gml:interior>
                <gml:LinearRing>
                  <gml:posList>40.12 -3.48 40.18 -3.48 40.18 -3.42 40.12 -3.42 40.12 -3.48</gml:posList>
                </gml:LinearRing>
              </gml:interior>
            </gml:PolygonPatch>
          </gml:patches>
        </gml:Surface>
      </cp:geometry>
    </cp:CadastralParcel>
  </wfs:member>
</wfs:FeatureCollection>
GML;
    }
}
