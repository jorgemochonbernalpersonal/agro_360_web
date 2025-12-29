<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Service for NASA Earthdata API (100% FREE)
 * Uses MODIS/VIIRS NDVI data via AppEEARS API
 * 
 * Register free at: https://urs.earthdata.nasa.gov/
 */
class NasaEarthdataService
{
    private string $username;
    private string $password;
    private string $baseUrl = 'https://appeears.earthdatacloud.nasa.gov/api';
    private bool $useMockData;
    private ?string $token = null;

    public function __construct()
    {
        $this->username = config('services.nasa_earthdata.username') ?? '';
        $this->password = config('services.nasa_earthdata.password') ?? '';
        $this->useMockData = config('services.nasa_earthdata.mock') ?? true;
    }

    /**
     * Get the latest NDVI data for a plot
     */
    public function getLatestData(Plot $plot): ?PlotRemoteSensing
    {
        // Check database first
        $existing = PlotRemoteSensing::where('plot_id', $plot->id)
            ->orderBy('image_date', 'desc')
            ->first();

        if ($existing && $existing->image_date->isToday()) {
            return $existing;
        }

        // Get fresh data
        $data = $this->fetchNdviData($plot);

        if ($data) {
            return $this->storeData($plot, $data);
        }

        return $existing;
    }

    /**
     * Get historical NDVI data for a plot
     */
    public function getHistoricalData(Plot $plot, int $days = 90)
    {
        if ($this->useMockData) {
            return $this->generateMockHistorical($plot, $days);
        }

        return PlotRemoteSensing::where('plot_id', $plot->id)
            ->where('image_date', '>=', now()->subDays($days))
            ->orderBy('image_date', 'desc')
            ->get();
    }

    /**
     * Fetch NDVI data from NASA API or generate mock
     */
    private function fetchNdviData(Plot $plot): ?array
    {
        if ($this->useMockData) {
            return $this->generateMockData($plot);
        }

        try {
            $token = $this->getAuthToken();
            if (!$token) {
                Log::error('NASA Earthdata: Failed to get auth token');
                return $this->generateMockData($plot);
            }

            // Get plot bounding box
            $bbox = $this->getPlotBoundingBox($plot);

            // Request NDVI data from MODIS
            $response = Http::withToken($token)
                ->timeout(60)
                ->get("{$this->baseUrl}/bundle/MOD13Q1.061/point", [
                    'latitude' => $bbox['lat'],
                    'longitude' => $bbox['lon'],
                    'startDate' => now()->subDays(16)->format('m-d-Y'),
                    'endDate' => now()->format('m-d-Y'),
                ]);

            if ($response->successful()) {
                return $this->parseNasaResponse($response->json());
            }

            Log::warning('NASA Earthdata API request failed', [
                'status' => $response->status(),
                'plot_id' => $plot->id,
            ]);

            return $this->generateMockData($plot);

        } catch (\Exception $e) {
            Log::error('NASA Earthdata API error', [
                'error' => $e->getMessage(),
                'plot_id' => $plot->id,
            ]);
            return $this->generateMockData($plot);
        }
    }

    /**
     * Get authentication token
     */
    private function getAuthToken(): ?string
    {
        if ($this->token) {
            return $this->token;
        }

        $cacheKey = 'nasa_earthdata_token';
        $cached = Cache::get($cacheKey);
        
        if ($cached) {
            $this->token = $cached;
            return $this->token;
        }

        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->post("{$this->baseUrl}/login");

            if ($response->successful()) {
                $this->token = $response->json('token');
                Cache::put($cacheKey, $this->token, now()->addHours(24));
                return $this->token;
            }
        } catch (\Exception $e) {
            Log::error('NASA Earthdata auth failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Parse NASA API response
     */
    private function parseNasaResponse(array $response): array
    {
        // Extract NDVI from MODIS response
        // MODIS NDVI is scaled: actual = value * 0.0001
        $ndviRaw = $response['_250m 16 days NDVI'] ?? null;
        $ndvi = $ndviRaw ? $ndviRaw * 0.0001 : 0.5;

        // Clamp to valid range
        $ndvi = max(-1, min(1, $ndvi));

        return [
            'ndvi_mean' => $ndvi,
            'ndvi_min' => $ndvi - 0.05,
            'ndvi_max' => $ndvi + 0.05,
            'cloud_coverage' => $response['cloud_coverage'] ?? 10,
            'image_date' => Carbon::parse($response['date'] ?? now()),
            'image_source' => 'NASA MODIS MOD13Q1',
        ];
    }

    /**
     * Get plot center coordinates
     */
    private function getPlotBoundingBox(Plot $plot): array
    {
        // Try to get from plot geometry
        $multipart = $plot->multipartPlotSigpacs()
            ->whereNotNull('plot_geometry_id')
            ->with('plotGeometry')
            ->first();

        if ($multipart && $multipart->plotGeometry) {
            $wkt = $multipart->plotGeometry->getWktCoordinates();
            // Extract center point from WKT (simplified)
            if (preg_match('/(-?\d+\.?\d*)\s+(-?\d+\.?\d*)/', $wkt, $matches)) {
                return [
                    'lon' => (float) $matches[1],
                    'lat' => (float) $matches[2],
                ];
            }
        }

        // Default to central Spain (placeholder)
        return [
            'lat' => 40.4168,
            'lon' => -3.7038,
        ];
    }

    /**
     * Store NDVI data in database
     */
    private function storeData(Plot $plot, array $data): PlotRemoteSensing
    {
        $healthStatus = $this->calculateHealthStatus($data['ndvi_mean']);

        return PlotRemoteSensing::updateOrCreate(
            [
                'plot_id' => $plot->id,
                'image_date' => $data['image_date']->format('Y-m-d'),
            ],
            [
                'ndvi_mean' => $data['ndvi_mean'],
                'ndvi_min' => $data['ndvi_min'],
                'ndvi_max' => $data['ndvi_max'],
                'cloud_coverage' => $data['cloud_coverage'],
                'image_source' => $data['image_source'],
                'health_status' => $healthStatus,
                'trend' => $this->calculateTrend($plot, $data['ndvi_mean']),
            ]
        );
    }

    /**
     * Calculate health status from NDVI
     */
    private function calculateHealthStatus(float $ndvi): string
    {
        return match (true) {
            $ndvi >= 0.7 => 'excellent',
            $ndvi >= 0.5 => 'good',
            $ndvi >= 0.3 => 'moderate',
            $ndvi >= 0.15 => 'poor',
            default => 'critical',
        };
    }

    /**
     * Calculate trend compared to previous reading
     */
    private function calculateTrend(Plot $plot, float $currentNdvi): string
    {
        $previous = PlotRemoteSensing::where('plot_id', $plot->id)
            ->orderBy('image_date', 'desc')
            ->skip(1)
            ->first();

        if (!$previous) {
            return 'stable';
        }

        $diff = $currentNdvi - $previous->ndvi_mean;

        return match (true) {
            $diff > 0.05 => 'increasing',
            $diff < -0.05 => 'decreasing',
            default => 'stable',
        };
    }

    /**
     * Generate realistic mock data for development
     */
    private function generateMockData(Plot $plot): array
    {
        $month = now()->month;
        
        // Seasonal NDVI for vineyards
        $seasonalBase = match (true) {
            $month >= 6 && $month <= 8 => 0.75,   // Summer: high
            $month >= 4 && $month <= 5 => 0.60,   // Spring: growing
            $month >= 9 && $month <= 10 => 0.55, // Autumn: harvest
            default => 0.25,                      // Winter: dormant
        };

        // Add some randomness
        $variation = (mt_rand(-10, 10) / 100);
        $ndvi = max(0.1, min(0.9, $seasonalBase + $variation));

        // NDWI typically ranges from -1 to 1
        // Healthy vegetation with good water: 0.2 to 0.4
        // Stressed vegetation: -0.2 to 0.1
        $ndwiBase = match (true) {
            $month >= 6 && $month <= 8 => 0.25,   // Summer: moderate water
            $month >= 4 && $month <= 5 => 0.35,   // Spring: high water
            $month >= 9 && $month <= 10 => 0.15, // Autumn: drying
            default => 0.05,                      // Winter: low
        };
        $ndwi = $ndwiBase + (mt_rand(-15, 15) / 100);

        return [
            'ndvi_mean' => round($ndvi, 3),
            'ndvi_min' => round($ndvi - 0.03, 3),
            'ndvi_max' => round($ndvi + 0.03, 3),
            'ndwi_mean' => round($ndwi, 3),
            'ndwi_min' => round($ndwi - 0.05, 3),
            'ndwi_max' => round($ndwi + 0.05, 3),
            'evi_mean' => round($ndvi * 0.9, 3),
            'cloud_coverage' => mt_rand(0, 30),
            'image_date' => now(),
            'image_source' => 'NASA MODIS (Mock)',
        ];
    }

    /**
     * Generate mock historical data
     */
    private function generateMockHistorical(Plot $plot, int $days): \Illuminate\Support\Collection
    {
        $data = collect();
        $currentDate = now();

        for ($i = 0; $i < 20; $i++) { // Generate 20 data points
            $date = $currentDate->copy()->subDays($i * 16);
            $month = $date->month;

            $seasonalBase = match (true) {
                $month >= 6 && $month <= 8 => 0.75,
                $month >= 4 && $month <= 5 => 0.60,
                $month >= 9 && $month <= 10 => 0.55,
                default => 0.25,
            };

            $variation = (mt_rand(-8, 8) / 100);
            $ndvi = max(0.1, min(0.9, $seasonalBase + $variation));

            // NDWI varies with season
            $ndwiBase = match (true) {
                $month >= 6 && $month <= 8 => 0.25,
                $month >= 4 && $month <= 5 => 0.35,
                $month >= 9 && $month <= 10 => 0.15,
                default => 0.05,
            };
            $ndwi = $ndwiBase + (mt_rand(-15, 15) / 100);

            // Temperature varies with season (Spain)
            $tempBase = match (true) {
                $month >= 6 && $month <= 8 => 32,
                $month >= 4 && $month <= 5 => 20,
                $month >= 9 && $month <= 10 => 18,
                default => 8,
            };
            $temp = $tempBase + mt_rand(-5, 5);

            $record = new PlotRemoteSensing([
                'plot_id' => $plot->id,
                'ndvi_mean' => round($ndvi, 3),
                'ndvi_min' => round($ndvi - 0.03, 3),
                'ndvi_max' => round($ndvi + 0.03, 3),
                'ndwi_mean' => round($ndwi, 3),
                'ndwi_min' => round($ndwi - 0.05, 3),
                'ndwi_max' => round($ndwi + 0.05, 3),
                'cloud_coverage' => mt_rand(0, 30),
                'image_date' => $date,
                'image_source' => 'NASA MODIS (Mock)',
                'health_status' => $this->calculateHealthStatus($ndvi),
                'trend' => 'stable',
                'temperature' => $temp,
                'precipitation' => mt_rand(0, 20),
                'humidity' => mt_rand(40, 80),
                'soil_moisture' => mt_rand(15, 45),
                'et0' => round(mt_rand(30, 70) / 10, 1),
            ]);

            $data->push($record);
        }

        return $data;
    }
}
