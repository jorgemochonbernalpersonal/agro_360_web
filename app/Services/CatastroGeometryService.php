<?php

namespace App\Services;

use App\Models\MultipartPlotSigpac;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Obtiene geometría de parcela desde el WFS INSPIRE de la Dirección General del
 * Catastro a partir de una referencia catastral (plots.code_parcel).
 *
 * Gemelo de SigpacGeometryService, pero la fuente es el Catastro (GML INSPIRE)
 * en lugar de SIGPAC Hub Cloud (JSON con WKT). La geometría resultante se enlaza
 * a la parcela con source='catastro' y sin sigpac_code_id.
 *
 * @see https://www.catastro.hacienda.gob.es/webinspire/documentos/inspire-cp-WFS.pdf
 */
class CatastroGeometryService
{
    private const WFS_URL = 'http://ovc.catastro.meh.es/INSPIRE/wfsCP.aspx';

    /**
     * Obtiene el WKT (POLYGON o MULTIPOLYGON, SRID 4326) de una parcela catastral.
     * Devuelve null si la referencia es inválida, la petición falla o no hay geometría.
     */
    public function fetchWkt(?string $cadastralReference): ?string
    {
        $refcat = $this->normalizeReference($cadastralReference);
        if ($refcat === null) {
            return null;
        }

        try {
            $client = Http::timeout(15);
            if (app()->environment('local')) {
                $client = $client->withoutVerifying();
            }

            $response = $client->get(self::WFS_URL, [
                'service' => 'wfs',
                'version' => '2.0.0',
                'request' => 'getfeature',
                'STOREDQUERIE_ID' => 'GetParcel',
                'srsname' => 'EPSG:4326',
                'REFCAT' => $refcat,
            ]);

            if ($response->status() !== 200) {
                return null;
            }

            return $this->gmlToWkt($response->body());
        } catch (\Exception $e) {
            Log::warning('Error fetching Catastro geometry', [
                'reference' => $refcat,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Inserta la geometría en plot_geometry y enlaza la parcela con source='catastro'.
     * Reemplaza cualquier geometría catastral previa de la misma parcela.
     * Devuelve el ID de la geometría creada.
     *
     * Debe llamarse dentro de una transacción activa.
     */
    public function upsertGeometry(int $plotId, string $wkt, string $source = 'catastro'): int
    {
        DB::statement(
            'INSERT INTO plot_geometry (coordinates, centroid, created_at, updated_at)
             VALUES (ST_GeomFromText(?, 4326), ST_Centroid(ST_GeomFromText(?, 4326)), ?, ?)',
            [$wkt, $wkt, now(), now()]
        );
        $geometryId = DB::getPdo()->lastInsertId();

        // Reemplazar geometrías previas del mismo origen para esta parcela.
        $previous = MultipartPlotSigpac::where('plot_id', $plotId)
            ->where('source', $source)
            ->whereNull('sigpac_code_id')
            ->get();

        foreach ($previous as $row) {
            $oldGeometryId = $row->plot_geometry_id;
            $row->delete();
            if ($oldGeometryId) {
                DB::table('plot_geometry')->where('id', $oldGeometryId)->delete();
            }
        }

        MultipartPlotSigpac::create([
            'plot_id' => $plotId,
            'sigpac_code_id' => null,
            'source' => $source,
            'plot_geometry_id' => $geometryId,
        ]);

        return (int) $geometryId;
    }

    /**
     * Normaliza una referencia catastral a las 14 posiciones de PARCELA que
     * usa el WFS (descarta el cargo e identificador de control del inmueble,
     * p. ej. "32010A020004750000XX" -> "32010A02000475").
     */
    private function normalizeReference(?string $reference): ?string
    {
        if (! $reference) {
            return null;
        }

        $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $reference));

        if (strlen($clean) < 14) {
            return null;
        }

        return substr($clean, 0, 14);
    }

    /**
     * Convierte el GML INSPIRE de una parcela catastral a WKT (SRID 4326).
     *
     * - Cada gml:Surface (o gml:Polygon) es un polígono.
     * - El anillo gml:exterior es el contorno; los gml:interior son agujeros.
     * - Los gml:posList vienen en orden lat/lon (EPSG:4326 INSPIRE); WKT los
     *   necesita en lon/lat, por lo que se intercambian.
     * - Se ignora cp:referencePoint (gml:pos), que es el centroide, no un anillo.
     */
    private function gmlToWkt(string $gml): ?string
    {
        $dom = new \DOMDocument;
        if (! @$dom->loadXML($gml)) {
            return null;
        }

        $xpath = new \DOMXPath($dom);

        // Cada polígono = un gml:Surface; si no hay, probar gml:Polygon.
        $units = $xpath->query("//*[local-name()='Surface']");
        if ($units->length === 0) {
            $units = $xpath->query("//*[local-name()='Polygon']");
        }

        $polygons = [];
        foreach ($units as $unit) {
            $exterior = $xpath->query(".//*[local-name()='exterior']//*[local-name()='posList']", $unit);
            if ($exterior->length === 0) {
                continue;
            }

            $extRing = $this->posListToWktRing($exterior->item(0)->textContent);
            if ($extRing === null) {
                continue;
            }

            $rings = ['('.$extRing.')'];
            foreach ($xpath->query(".//*[local-name()='interior']//*[local-name()='posList']", $unit) as $interior) {
                $intRing = $this->posListToWktRing($interior->textContent);
                if ($intRing !== null) {
                    $rings[] = '('.$intRing.')';
                }
            }

            $polygons[] = '('.implode(',', $rings).')';
        }

        if (empty($polygons)) {
            return null;
        }

        return count($polygons) === 1
            ? 'POLYGON'.$polygons[0]
            : 'MULTIPOLYGON('.implode(',', $polygons).')';
    }

    /**
     * Convierte un gml:posList ("lat lon lat lon ...") en una lista de vértices
     * WKT ("lon lat, lon lat, ..."), invirtiendo el orden de los ejes.
     * Devuelve null si no forma un anillo válido (>= 4 puntos).
     */
    private function posListToWktRing(string $posList): ?string
    {
        $nums = preg_split('/\s+/', trim($posList), -1, PREG_SPLIT_NO_EMPTY);
        if (count($nums) < 8) { // >= 4 puntos (anillo cerrado) => >= 8 números
            return null;
        }

        $points = [];
        for ($i = 0; $i + 1 < count($nums); $i += 2) {
            $lat = (float) $nums[$i];
            $lng = (float) $nums[$i + 1];
            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                return null;
            }
            $points[] = $lng.' '.$lat; // WKT = lon lat (swap)
        }

        return count($points) >= 4 ? implode(', ', $points) : null;
    }
}
