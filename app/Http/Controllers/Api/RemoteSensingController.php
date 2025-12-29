<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plot;
use App\Models\MultipartPlotSigpac;
use App\Services\RemoteSensing\NasaEarthdataService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * API Controller for Remote Sensing data
 */
class RemoteSensingController extends Controller
{
    use AuthorizesRequests;
    /**
     * Get NDVI colors for map polygons of a plot
     * 
     * Returns an array of polygon IDs with their NDVI-based colors
     */
    public function getPlotNdviColors(Plot $plot)
    {
        $this->authorize('view', $plot);
        
        $service = new NasaEarthdataService();
        $data = $service->getLatestData($plot);
        
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'No NDVI data available',
            ]);
        }
        
        // Obtener todos los multiparts de la parcela
        $multiparts = MultipartPlotSigpac::where('plot_id', $plot->id)
            ->whereNotNull('plot_geometry_id')
            ->get();
        
        // Generar colores basados en NDVI
        $ndviColor = $this->getNdviColor($data->ndvi_mean);
        
        $polygons = $multiparts->map(function ($multipart) use ($ndviColor, $data) {
            return [
                'id' => $multipart->id,
                'fill' => $ndviColor['fill'],
                'line' => $ndviColor['line'],
                'ndvi' => $data->ndvi_mean,
                'health_status' => $data->health_status,
            ];
        })->values();
        
        return response()->json([
            'success' => true,
            'plot_id' => $plot->id,
            'ndvi_mean' => $data->ndvi_mean,
            'health_status' => $data->health_status,
            'health_text' => $data->health_text,
            'health_emoji' => $data->health_emoji,
            'image_date' => $data->image_date->format('Y-m-d'),
            'polygons' => $polygons,
            'color' => $ndviColor,
        ]);
    }
    
    /**
     * Get NDVI color based on value
     */
    private function getNdviColor(float $ndvi): array
    {
        // Color scale from red (low NDVI) to green (high NDVI)
        return match (true) {
            $ndvi >= 0.7 => ['fill' => 'rgba(34, 197, 94, 0.6)', 'line' => '#16a34a'],    // Green
            $ndvi >= 0.5 => ['fill' => 'rgba(52, 211, 153, 0.6)', 'line' => '#10b981'],   // Emerald
            $ndvi >= 0.3 => ['fill' => 'rgba(250, 204, 21, 0.6)', 'line' => '#ca8a04'],   // Yellow
            $ndvi >= 0.15 => ['fill' => 'rgba(251, 146, 60, 0.6)', 'line' => '#ea580c'],  // Orange
            default => ['fill' => 'rgba(239, 68, 68, 0.6)', 'line' => '#dc2626'],         // Red
        };
    }
    
    /**
     * Get all plots NDVI summary for map overview
     */
    public function getAllPlotsNdvi(Request $request)
    {
        $user = auth()->user();
        $service = new NasaEarthdataService();
        
        $plots = Plot::forUser($user)->get();
        
        $data = $plots->map(function ($plot) use ($service) {
            $sensing = $service->getLatestData($plot);
            
            if (!$sensing) {
                return null;
            }
            
            return [
                'plot_id' => $plot->id,
                'plot_name' => $plot->name,
                'ndvi' => $sensing->ndvi_mean,
                'health_status' => $sensing->health_status,
                'color' => $this->getNdviColor($sensing->ndvi_mean),
            ];
        })->filter()->values();
        
        return response()->json([
            'success' => true,
            'plots' => $data,
            'count' => $data->count(),
        ]);
    }
}
