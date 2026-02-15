<?php

namespace App\Services\RemoteSensing;

use App\Contracts\RemoteSensing\RemoteSensingProviderInterface;
use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use App\Repositories\PlotRemoteSensingRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Service for NASA Earthdata API (100% FREE)
 * Uses MODIS/VIIRS NDVI data via AppEEARS API
 * 
 * Register free at: https://urs.earthdata.nasa.gov/
 */
class NasaEarthdataService implements RemoteSensingProviderInterface
{
    public function __construct(
        private PlotRemoteSensingRepository $repository,
        private RemoteSensingCacheService $cacheService,
        private RateLimitService $rateLimitService
    ) {
        $this->username = config('services.nasa_earthdata.username') ?? '';
        $this->password = config('services.nasa_earthdata.password') ?? '';
        $this->useMockData = config('services.nasa_earthdata.mock') ?? true;
    }
    private string $username;
    private string $password;
    private string $baseUrl = 'https://appeears.earthdatacloud.nasa.gov/api';
    private bool $useMockData;
    private ?string $token = null;

    /**
     * Get the latest NDVI data for a plot
     */
    public function getLatestData(Plot $plot, bool $forceRefresh = false): ?PlotRemoteSensing
    {
        // Check database first if not forced
        if (!$forceRefresh) {
            $existing = $this->repository->getTodayForPlot($plot);
            
            if ($existing) {
                return $existing;
            }
        }

        // Get fresh data
        $data = $this->fetchNdviData($plot);

        if ($data) {
            return $this->storeData($plot, $data);
        }

        return $this->repository->getLatestForPlot($plot);
    }

    /**
     * Fetch and store NDVI data (used by UpdatePlotNdviJob)
     * 
     * @param Plot $plot
     * @return PlotRemoteSensing|null
     */
    public function fetchAndStoreNdvi(Plot $plot): ?PlotRemoteSensing
    {
        return $this->getLatestData($plot, true);
    }
    
    /**
     * Clear all cached data for a plot
     */
    public function clearCache(Plot $plot): void
    {
        $this->cacheService->clearNdviForPlot($plot);
    }

    /**
     * Get historical NDVI data for a plot
     */
    public function getHistoricalData(Plot $plot, int $days = 90): Collection
    {
        // Always check database first
        $existingData = $this->repository->getHistoricalForPlot($plot, $days);

        // If we have enough recent data, return it
        if ($existingData->count() >= 10) {
            return $existingData;
        }

        // If in mock mode and no data, generate and persist
        if ($this->useMockData && $existingData->isEmpty()) {
            $this->generateAndPersistMockHistorical($plot);
            
            // Re-fetch from database
            return $this->repository->getHistoricalForPlot($plot, $days);
        }

        return $existingData;
    }

    /**
     * Fetch NDVI data from NASA API or generate mock
     */
    private function fetchNdviData(Plot $plot): ?array
    {
        if ($this->useMockData) {
            return $this->generateMockData($plot);
        }

        // Check rate limit before making request
        if (!$this->rateLimitService->canMakeNasaRequest()) {
            Log::warning('NASA API rate limit reached', [
                'plot_id' => $plot->id,
                'usage' => $this->rateLimitService->getUsage('nasa'),
            ]);
            
            // Return null to use existing data from database
            return null;
        }

        try {
            $token = $this->getAuthToken();
            if (!$token) {
                Log::error('NASA Earthdata: Failed to get auth token', [
                    'plot_id' => $plot->id,
                    'username' => $this->username,
                    'env' => config('app.env'),
                ]);
                
                // En producción, mejor retornar null y usar datos existentes
                if (config('app.env') === 'production') {
                    return null;
                }
                
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

            // Record successful API call
            $this->rateLimitService->recordNasaRequest();

            if ($response->successful()) {
                return $this->parseNasaResponse($response->json());
            }

            Log::warning('NASA Earthdata API request failed', [
                'status' => $response->status(),
                'plot_id' => $plot->id,
                'username' => $this->username,
            ]);

            // En producción, retornar null para usar datos existentes
            if (config('app.env') === 'production') {
                return null;
            }
            
            return $this->generateMockData($plot);

        } catch (\Exception $e) {
            Log::error('NASA Earthdata API error', [
                'error' => $e->getMessage(),
                'plot_id' => $plot->id,
                'trace' => $e->getTraceAsString(),
            ]);
            
            // En producción, retornar null para usar datos existentes
            if (config('app.env') === 'production') {
                return null;
            }
            
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

        $cacheKey = $this->cacheService->getNasaTokenKey();
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
                Cache::put($cacheKey, $this->token, $this->cacheService->getNasaTokenTtl());
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
        $trend = $this->calculateTrend($plot, $data['ndvi_mean'], $data['image_date']);

        return $this->repository->createOrUpdate(
            $plot,
            $data['image_date'],
            [
                'ndvi_mean' => $data['ndvi_mean'],
                'ndvi_min' => $data['ndvi_min'],
                'ndvi_max' => $data['ndvi_max'],
                'cloud_coverage' => $data['cloud_coverage'],
                'image_source' => $data['image_source'],
                'health_status' => $healthStatus,
                'trend' => $trend,
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
    private function calculateTrend(Plot $plot, float $currentNdvi, Carbon $currentDate): string
    {
        $previous = $this->repository->getPreviousData($plot, $currentDate);

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
     * Uses plot_id as seed for consistency
     */
    private function generateMockData(Plot $plot): array
    {
        $month = now()->month;
        $day = now()->day;
        
        // Use plot ID as seed for consistent data per plot
        $seed = $plot->id * 1000 + ($month * 100) + $day;
        mt_srand($seed);
        
        // Seasonal NDVI for vineyards
        $seasonalBase = match (true) {
            $month >= 6 && $month <= 8 => 0.75,   // Summer: high
            $month >= 4 && $month <= 5 => 0.60,   // Spring: growing
            $month >= 9 && $month <= 10 => 0.55, // Autumn: harvest
            default => 0.25,                      // Winter: dormant
        };

        // Add some randomness (but consistent for the same day)
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
        
        // Reset random seed
        mt_srand();

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
     * Generate and persist mock historical data to database
     * Only creates data if it doesn't already exist for that date
     */
    private function generateAndPersistMockHistorical(Plot $plot): void
    {
        $currentDate = now();

        for ($i = 0; $i < 20; $i++) { // Generate 20 data points (every 16 days for ~1 year)
            $date = $currentDate->copy()->subDays($i * 16);
            $month = $date->month;
            
            // Skip if data already exists for this date
            if ($this->repository->existsForDate($plot, $date)) {
                continue;
            }

            // Use consistent seed based on plot_id and date
            $seed = $plot->id * 10000 + ($date->year * 100) + $date->dayOfYear;
            mt_srand($seed);

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
            
            // Calculate trend from previous data
            $trend = $this->calculateTrend($plot, $ndvi, $date);
            
            // Reset seed
            mt_srand();

            // Create record in database
            $this->repository->createOrUpdate($plot, $date, [
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
                'trend' => $trend,
                'temperature' => $temp,
                'precipitation' => mt_rand(0, 20),
                'humidity' => mt_rand(40, 80),
                'soil_moisture' => mt_rand(15, 45),
                'et0' => round(mt_rand(30, 70) / 10, 1),
            ]);
        
        Log::info('Mock historical data persisted', [
            'plot_id' => $plot->id,
            'records_created' => 20,
        ]);
    }
}
