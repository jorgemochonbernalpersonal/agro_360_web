<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
            return;
        }
        
        // Cerrar el polígono si no está cerrado
        $firstPoint = $points[0];
        $lastPoint = end($points);
        if ($firstPoint['lat'] != $lastPoint['lat'] || $firstPoint['lng'] != $lastPoint['lng']) {
            $points[] = $firstPoint;
        }
        
        // Construir WKT (Well-Known Text) para MySQL
        $wktPoints = collect($points)->map(function($point) {
            return "{$point['lng']} {$point['lat']}";
        })->join(', ');
        
        $wkt = "POLYGON(($wktPoints))";
        
        // Escapar comillas simples en WKT
        $wkt = str_replace("'", "''", $wkt);
        
        // Usar DB::raw para insertar geometry en MySQL
        $this->coordinates = DB::raw("ST_GeomFromText('$wkt', 4326)");
        $this->centroid = DB::raw("ST_Centroid(ST_GeomFromText('$wkt', 4326))");
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
            "SELECT ST_AsText(coordinates) as wkt FROM plot_geometry WHERE id = ?",
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
            "SELECT ST_AsText(centroid) as wkt FROM plot_geometry WHERE id = ?",
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
}

