<?php

namespace App\Services;

use App\Models\MultipartPlotSigpac;
use App\Models\SigpacCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SigpacGeometryService
{
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

            if (!is_array($data) || empty($data) || !isset($data[0]['wkt'])) {
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
                'plot_id'          => $plotId,
                'sigpac_code_id'   => $sigpacCode->id,
                'plot_geometry_id' => $geometryId,
            ]);
        }

        return $geometryId;
    }
}
