<?php

namespace App\Services;

use App\Models\MultipartPlotSigpac;
use App\Models\SigpacCode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SigpacGeometryService
{
    public const WKT_PATTERN = '/^(POLYGON|MULTIPOLYGON|LINESTRING|POINT)\s*\(.+\)$/i';

    /**
     * Obtiene el WKT de un código SIGPAC desde la API pública de SIGPAC Hub Cloud.
     * Devuelve null si la petición falla o la respuesta no contiene geometría válida.
     */
    public function fetchWkt(SigpacCode $sigpacCode): ?string
    {
        try {
            $url = sprintf(
                'https://sigpac-hubcloud.es/servicioconsultassigpac/query/recinfo/%s/%s/%s/%s/%s/%s/%s.json',
                $sigpacCode->code_province,
                $sigpacCode->code_municipality,
                $sigpacCode->code_aggregate ?? '0',
                $sigpacCode->code_zone,
                $sigpacCode->code_polygon,
                $sigpacCode->code_plot,
                $sigpacCode->code_enclosure
            );

            $httpClient = Http::timeout(10);
            if (app()->environment('local')) {
                $httpClient = $httpClient->withoutVerifying();
            }

            $response = $httpClient->get($url);

            if ($response->status() !== 200) {
                return null;
            }

            $data = $response->json();

            if (! is_array($data) || empty($data) || ! isset($data[0]['wkt'])) {
                return null;
            }

            return $data[0]['wkt'];
        } catch (\Exception $e) {
            Log::warning('Error fetching SIGPAC coordinates', [
                'sigpac_code_id' => $sigpacCode->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Inserta la geometría WKT en plot_geometry y hace upsert del registro MultipartPlotSigpac.
     * Devuelve el ID de la geometría creada.
     *
     * Debe llamarse dentro de una transacción activa — lanza excepción en caso de error de DB.
     */
    public function upsertGeometry(int $plotId, SigpacCode $sigpacCode, string $wkt): int
    {
        $geometryId = DB::table('plot_geometry')->insertGetId([
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::statement(
            'UPDATE plot_geometry SET
                coordinates = ST_GeomFromText(?, 4326),
                centroid = ST_Centroid(ST_GeomFromText(?, 4326))
            WHERE id = ?',
            [$wkt, $wkt, $geometryId]
        );

        $mps = MultipartPlotSigpac::where('plot_id', $plotId)
            ->where('sigpac_code_id', $sigpacCode->id)
            ->first();

        if ($mps) {
            $mps->plot_geometry_id = $geometryId;
            $mps->updated_at = now();
            $mps->save();
        } else {
            MultipartPlotSigpac::create([
                'plot_id' => $plotId,
                'sigpac_code_id' => $sigpacCode->id,
                'plot_geometry_id' => $geometryId,
            ]);
        }

        return $geometryId;
    }

    /**
     * Actualiza una geometría existente con nuevas coordenadas WKT.
     * Debe llamarse dentro de una transacción activa.
     */
    public function updateGeometry(int $geometryId, string $wkt): void
    {
        DB::statement(
            'UPDATE plot_geometry SET
                coordinates = ST_GeomFromText(?, 4326),
                centroid = ST_Centroid(ST_GeomFromText(?, 4326)),
                updated_at = NOW()
            WHERE id = ?',
            [$wkt, $wkt, $geometryId]
        );
    }

    /**
     * Construye WKT POLYGON desde un array de coordenadas [{lat, lng}, ...].
     * Cierra el polígono automáticamente y lanza \InvalidArgumentException en coordenadas inválidas.
     */
    public function buildWktFromCoordinates(array $coordinates): string
    {
        $points = $coordinates;
        $first = $points[0];
        $last = end($points);
        if ($first['lat'] != $last['lat'] || $first['lng'] != $last['lng']) {
            $points[] = $first;
        }

        $wktPoints = collect($points)->map(function ($point) {
            $lng = filter_var($point['lng'], FILTER_VALIDATE_FLOAT);
            $lat = filter_var($point['lat'], FILTER_VALIDATE_FLOAT);

            if ($lng === false || $lat === false) {
                throw new \InvalidArgumentException(__('Coordenadas inválidas: deben ser valores numéricos.'));
            }

            return "$lng $lat";
        })->join(', ');

        return "POLYGON(($wktPoints))";
    }

    /**
     * Ejecuta fetchWkt+validate+upsert para una colección de SigpacCodes.
     * Debe llamarse dentro de una transacción activa.
     * Devuelve ['success' => N, 'errors' => [...string]].
     */
    public function generateForCodes(int $plotId, Collection $sigpacCodes): array
    {
        $success = 0;
        $errors = [];

        foreach ($sigpacCodes as $sigpacCode) {
            try {
                $wkt = $this->fetchWkt($sigpacCode);

                if (! $wkt) {
                    $errors[] = "No se pudieron obtener coordenadas para el código {$sigpacCode->code}";

                    continue;
                }

                if (! preg_match(self::WKT_PATTERN, $wkt)) {
                    $errors[] = "Formato de coordenadas inválido para el código {$sigpacCode->code}";

                    continue;
                }

                $this->upsertGeometry($plotId, $sigpacCode, $wkt);
                $success++;
            } catch (\Exception $e) {
                $errors[] = "Error procesando código {$sigpacCode->code}: ".$e->getMessage();
                Log::error('Error generating map for sigpac code', [
                    'sigpac_code_id' => $sigpacCode->id,
                    'plot_id' => $plotId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return ['success' => $success, 'errors' => $errors];
    }
}
