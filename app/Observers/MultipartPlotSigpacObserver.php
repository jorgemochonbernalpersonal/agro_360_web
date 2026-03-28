<?php

namespace App\Observers;

use App\Models\MultipartPlotSigpac;
use App\Models\Municipality;
use Illuminate\Support\Facades\DB;

/**
 * Recalcula las coordenadas del municipio usando el promedio real
 * de los centroides de todas las parcelas con geometría SIGPAC del municipio.
 *
 * Se dispara al enlazar o desenlazar una geometría SIGPAC a una parcela.
 */
class MultipartPlotSigpacObserver
{
    public function created(MultipartPlotSigpac $mps): void
    {
        $this->recalculate($mps->plot?->municipality_id);
    }

    public function deleted(MultipartPlotSigpac $mps): void
    {
        $this->recalculate($mps->plot?->municipality_id);
    }

    private function recalculate(?int $municipalityId): void
    {
        if (! $municipalityId) return;

        $result = DB::selectOne(
            "SELECT AVG(ST_Y(pg.centroid)) AS lat,
                    AVG(ST_X(pg.centroid)) AS lng
             FROM   multipart_plot_sigpac mps
             JOIN   plot_geometry         pg ON mps.plot_geometry_id = pg.id
             JOIN   plots                 p  ON mps.plot_id          = p.id
             WHERE  p.municipality_id = ?
               AND  p.active          = 1
               AND  pg.centroid IS NOT NULL",
            [$municipalityId]
        );

        // Solo actualiza si hay geometrías reales — si no, conserva las coords del CSV
        if ($result && $result->lat !== null && $result->lng !== null) {
            Municipality::where('id', $municipalityId)->update([
                'lat' => round((float) $result->lat, 6),
                'lng' => round((float) $result->lng, 6),
            ]);
        }
    }
}
