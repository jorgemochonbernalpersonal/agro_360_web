<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PlotGeometry extends Model
{
    protected $table = 'plot_geometry';

    protected $fillable = [
        'id',
        'coordinates',
        'centroid',
        'created_at',
        'updated_at',
    ];

    /**
     * Relaciones con múltiples plot-sigpac
     */
    public function multiplePlotSigpacs(): HasMany
    {
        return $this->hasMany(MultipartPlotSigpac::class, 'plot_geometry_id');
    }

    /**
     * Guardar coordenadas desde array de puntos (lat, lng)
     * Formato: [['lat' => 40.123, 'lng' => -3.456], ...]
     */
    public function setCoordinatesFromArray(array $points): void
    {
        if (empty($points) || count($points) < 3) {
            throw new \InvalidArgumentException('Se necesitan al menos 3 puntos para crear un polígono.');
        }

        // Cerrar el polígono si no está cerrado
        $firstPoint = $points[0];
        $lastPoint = end($points);
        if ($firstPoint['lat'] != $lastPoint['lat'] || $firstPoint['lng'] != $lastPoint['lng']) {
            $points[] = $firstPoint;
        }

        // Construir WKT (Well-Known Text) con validación estricta de tipos
        $wktPoints = collect($points)->map(function ($point) {
            // Validar y castear a float para prevenir SQL injection
            $lng = filter_var($point['lng'], FILTER_VALIDATE_FLOAT);
            $lat = filter_var($point['lat'], FILTER_VALIDATE_FLOAT);

            if ($lng === false || $lat === false) {
                throw new \InvalidArgumentException('Coordenadas inválidas: deben ser valores numéricos.');
            }

            // Validar rangos geográficos válidos
            if ($lat < -90 || $lat > 90) {
                throw new \InvalidArgumentException("Latitud inválida: $lat. Debe estar entre -90 y 90.");
            }
            if ($lng < -180 || $lng > 180) {
                throw new \InvalidArgumentException("Longitud inválida: $lng. Debe estar entre -180 y 180.");
            }

            return "$lng $lat";
        })->join(', ');

        $wkt = "POLYGON(($wktPoints))";

        // Guardar usando prepared statement - el ID se asignará después del save
        $this->_pending_wkt = $wkt;
    }

    /**
     * Hook que se ejecuta después de guardar el modelo
     * Actualiza las coordenadas usando prepared statements seguros
     */
    protected static function booted()
    {
        static::saved(function ($geometry) {
            if (isset($geometry->_pending_wkt)) {
                $wkt = $geometry->_pending_wkt;

                // Usar prepared statement seguro
                DB::statement(
                    'UPDATE plot_geometry SET 
                        coordinates = ST_GeomFromText(?, 4326),
                        centroid = ST_Centroid(ST_GeomFromText(?, 4326)),
                        updated_at = NOW()
                    WHERE id = ?',
                    [$wkt, $wkt, $geometry->id]
                );

                unset($geometry->_pending_wkt);
            }
        });
    }

    /**
     * Obtener coordenadas como array
     */
    public function getCoordinatesAsArray(): array
    {
        if (!$this->id) {
            return [];
        }

        // Obtener WKT desde MySQL usando ST_AsText
        $result = DB::selectOne(
            'SELECT ST_AsText(coordinates) as wkt FROM plot_geometry WHERE id = ?',
            [$this->id]
        );

        if (!$result || !$result->wkt) {
            return [];
        }

        $wkt = $result->wkt;

        // Parsear WKT POLYGON((lng lat, lng lat, ...))
        preg_match('/POLYGON\(\(([^)]+)\)\)/', $wkt, $matches);
        if (!isset($matches[1])) {
            return [];
        }

        $points = [];
        $coords = explode(',', $matches[1]);

        foreach ($coords as $coord) {
            $parts = explode(' ', trim($coord));
            if (count($parts) >= 2) {
                $points[] = [
                    'lng' => (float) $parts[0],
                    'lat' => (float) $parts[1],
                ];
            }
        }

        return $points;
    }

    /**
     * Obtener centroide como array [lat, lng]
     */
    public function getCentroidAsArray(): ?array
    {
        if (!$this->id) {
            return null;
        }

        $result = DB::selectOne(
            'SELECT ST_AsText(centroid) as wkt FROM plot_geometry WHERE id = ?',
            [$this->id]
        );

        if (!$result || !$result->wkt) {
            return null;
        }

        $wkt = $result->wkt;

        // Parsear WKT POINT(lng lat)
        preg_match('/POINT\(([^)]+)\)/', $wkt, $matches);
        if (!isset($matches[1])) {
            return null;
        }

        $parts = explode(' ', trim($matches[1]));
        if (count($parts) >= 2) {
            return [
                'lat' => (float) $parts[1],
                'lng' => (float) $parts[0],
            ];
        }

        return null;
    }

    /**
     * Obtener coordenadas WKT directamente
     */
    public function getWktCoordinates(): ?string
    {
        if (!$this->id) {
            return null;
        }

        $result = DB::selectOne(
            'SELECT ST_AsText(coordinates) as wkt FROM plot_geometry WHERE id = ?',
            [$this->id]
        );

        return $result?->wkt;
    }
}
