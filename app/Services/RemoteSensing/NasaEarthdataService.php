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
use App\Services\RemoteSensing\CoordinatesHelper;

/**
 * Service for NASA Earthdata API (100% FREE)
 * Uses MODIS/VIIRS NDVI data via AppEEARS API
 * 
 * Register free at: https://urs.earthdata.nasa.gov/
 */
class NasaEarthdataService implements RemoteSensingProviderInterface
{
    private ?NasaLSTService $lstService = null;
    private ?NasaAreaRequestService $areaService = null;
    private ?NasaVIIRSService $viirsService = null;
    private ?NasaSpectralBandsService $spectralService = null;
    private ?NasaLAIService $laiService = null;
    private ?NasaSMAPService $smapService = null;
    private ?NasaETService $etService = null;

    public function __construct(
        private PlotRemoteSensingRepository $repository,
        private RemoteSensingCacheService $cacheService,
        private RateLimitService $rateLimitService
    ) {
        $this->username = config('services.nasa_earthdata.username') ?? '';
        $this->password = config('services.nasa_earthdata.password') ?? '';
        $this->useMockData = config('services.nasa_earthdata.mock') ?? true;
        
        // Initialize all NASA services
        $this->lstService = app(NasaLSTService::class);
        $this->areaService = app(NasaAreaRequestService::class);
        $this->viirsService = app(NasaVIIRSService::class);
        $this->spectralService = app(NasaSpectralBandsService::class);
        $this->laiService = app(NasaLAIService::class);
        $this->smapService = app(NasaSMAPService::class);
        $this->etService = app(NasaETService::class);
    }
    private string $username;
    private string $password;
    private string $baseUrl = 'https://appeears.earthdatacloud.nasa.gov/api';
    private string $ornlBaseUrl = 'https://modis.ornl.gov/rst/api/v1';
    private bool $useMockData;
    private ?string $token = null;

    /**
     * Get the latest NDVI data for a plot
     * 
     * @param array|null $coordinates Override ['lat'=>x,'lon'=>y] of the selected sigpac parcel (MultipartPlotSigpac)
     */
    public function getLatestData(Plot $plot, bool $forceRefresh = false, ?array $coordinates = null): ?PlotRemoteSensing
    {
        // Check database first if not forced
        if (!$forceRefresh) {
            $existing = $this->repository->getTodayForPlot($plot);
            
            if ($existing) {
                return $existing;
            }
        }

        // Get fresh data
        $data = $this->fetchNdviData($plot, $coordinates);

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
     * 
     * @param array|null $coordinates Override for the selected sigpac parcel ['lat','lng'|'lon']
     */
    private function fetchNdviData(Plot $plot, ?array $coordinates = null): ?array
    {
        if ($this->useMockData) {
            return $this->generateMockData($plot);
        }

        // Check rate limit before making request
        if (!$this->rateLimitService->canMakeNasaRequest()) {
            Log::warning('NASA API rate limit reached — using estimated data', [
                'plot_id' => $plot->id,
                'usage' => $this->rateLimitService->getUsage('nasa'),
            ]);

            return $this->generateMockData($plot);
        }

        try {
            // Get plot coordinates from geometry centroid (or override)
            $bbox = $this->getPlotBoundingBox($plot, $coordinates);

            // Cache 24h — MODIS updates every 16 days
            $cacheKey = "ornl_modis_{$plot->id}";
            $cached   = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }

            // ORNL DAAC MODIS Subset API (synchronous, no auth required)
            // Endpoint: /MOD13Q1/subset?latitude=&longitude=&startDate=A{YYYYDOY}&endDate=A{YYYYDOY}
            $startJulian = 'A' . now()->subDays(16)->format('Y') . str_pad(now()->subDays(16)->dayOfYear, 3, '0', STR_PAD_LEFT);
            $endJulian   = 'A' . now()->format('Y') . str_pad(now()->dayOfYear, 3, '0', STR_PAD_LEFT);

            $response = Http::timeout(30)
                ->get("{$this->ornlBaseUrl}/MOD13Q1/subset", [
                    'latitude'     => $bbox['lat'],
                    'longitude'    => $bbox['lon'],
                    'startDate'    => $startJulian,
                    'endDate'      => $endJulian,
                    'kmAboveBelow' => 0,
                    'kmLeftRight'  => 0,
                ]);

            // Record successful API call
            $this->rateLimitService->recordNasaRequest();

            if ($response->successful()) {
                $result = $this->parseOrnlResponse($response->json());
                Cache::put($cacheKey, $result, now()->addHours(24));
                return $result;
            }

            Log::warning('ORNL DAAC MODIS API request failed — using estimated data', [
                'status'  => $response->status(),
                'plot_id' => $plot->id,
                'lat'     => $bbox['lat'],
                'lon'     => $bbox['lon'],
                'body'    => substr($response->body(), 0, 200),
            ]);

            $fallback = $this->generateMockData($plot);
            Cache::put($cacheKey, $fallback, now()->addHour());
            return $fallback;

        } catch (\Exception $e) {
            Log::warning('NASA Earthdata API error — using estimated data', [
                'error'   => $e->getMessage(),
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
     * Parse ORNL DAAC MODIS Subset API response
     *
     * Response format:
     *   { "header": { "NODATA_value": -3000, ... },
     *     "subset": [{ "band": "_250m_16_days_NDVI", "data": [3000],
     *                  "scale": "0.0001", "calendar_date": "2024-01-01" }] }
     */
    private function parseOrnlResponse(array $response): array
    {
        $nodata = $response['header']['NODATA_value'] ?? -3000;

        $ndviSubset = collect($response['subset'] ?? [])
            ->firstWhere('band', '_250m_16_days_NDVI');

        $rawValue = $ndviSubset['data'][0] ?? null;
        $scale    = (float) ($ndviSubset['scale'] ?? '0.0001');

        if ($rawValue === null || $rawValue <= $nodata) {
            $ndvi = 0.5; // fallback when pixel is cloud/nodata
        } else {
            $ndvi = $rawValue * $scale;
        }

        $ndvi = max(-1.0, min(1.0, $ndvi));

        $imageDate = Carbon::parse($ndviSubset['calendar_date'] ?? now());

        return [
            'ndvi_mean'      => round($ndvi, 3),
            'ndvi_min'       => round($ndvi - 0.03, 3),
            'ndvi_max'       => round($ndvi + 0.03, 3),
            'cloud_coverage' => 0,
            'image_date'     => $imageDate,
            'image_source'   => 'NASA MODIS MOD13Q1 (ORNL DAAC)',
        ];
    }

    /**
     * Get plot center coordinates (uses selected sigpac parcel if coordinates are passed)
     */
    private function getPlotBoundingBox(Plot $plot, ?array $coordinates = null): array
    {
        $coords = $coordinates
            ? CoordinatesHelper::getCoordinates($plot, null, $coordinates)
            : CoordinatesHelper::getCoordinates($plot);

        return ['lat' => $coords['lat'], 'lon' => $coords['lon']];
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
    private function generateMockData(Plot $plot, ?array $coords = null): array
    {
        $month = now()->month;
        $day = now()->day;

        // Use plot ID as seed for consistent data per plot
        $seed = $plot->id * 1000 + ($month * 100) + $day;
        mt_srand($seed);

        $lat = $coords['lat'] ?? CoordinatesHelper::getCoordinates($plot)['lat'];
        $isCanary = $lat < 30.0;

        // Seasonal NDVI for vineyards
        $seasonalBase = $isCanary
            ? match (true) {
                $month >= 6 && $month <= 9  => 0.68,  // Verano subtropical
                $month >= 10 || $month <= 2 => 0.48,  // Invierno suave (sin dormancia real)
                default                     => 0.58,  // Primavera/Otoño
            }
            : match (true) {
                $month >= 6 && $month <= 8  => 0.75,
                $month >= 4 && $month <= 5  => 0.60,
                $month >= 9 && $month <= 10 => 0.55,
                default                     => 0.25,
            };

        // Add some randomness (but consistent for the same day)
        $variation = (mt_rand(-10, 10) / 100);
        $ndvi = max(0.1, min(0.9, $seasonalBase + $variation));

        // NDWI typically ranges from -1 to 1
        // Healthy vegetation with good water: 0.2 to 0.4
        // Stressed vegetation: -0.2 to 0.1
        $ndwiBase = $isCanary
            ? match (true) {
                $month >= 6 && $month <= 9  => 0.10,  // Verano seco
                $month >= 10 || $month <= 2 => 0.20,  // Invierno más húmedo
                default                     => 0.15,
            }
            : match (true) {
                $month >= 6 && $month <= 8  => 0.25,
                $month >= 4 && $month <= 5  => 0.35,
                $month >= 9 && $month <= 10 => 0.15,
                default                     => 0.05,
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
            'image_source' => $this->useMockData ? 'NASA MODIS (Mock)' : 'NASA MODIS (Estimado)',
        ];
    }

    /**
     * Generate and persist mock historical data to database
     * Only creates data if it doesn't already exist for that date
     */
    private function generateAndPersistMockHistorical(Plot $plot): void
    {
        $currentDate = now();
        $plotCoords = CoordinatesHelper::getCoordinates($plot);
        $isCanary = $plotCoords['lat'] < 30.0;

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

            $seasonalBase = $isCanary
                ? match (true) {
                    $month >= 6 && $month <= 9  => 0.68,
                    $month >= 10 || $month <= 2 => 0.48,
                    default                     => 0.58,
                }
                : match (true) {
                    $month >= 6 && $month <= 8  => 0.75,
                    $month >= 4 && $month <= 5  => 0.60,
                    $month >= 9 && $month <= 10 => 0.55,
                    default                     => 0.25,
                };

            $variation = (mt_rand(-8, 8) / 100);
            $ndvi = max(0.1, min(0.9, $seasonalBase + $variation));

            // NDWI varies with season
            $ndwiBase = $isCanary
                ? match (true) {
                    $month >= 6 && $month <= 9  => 0.10,
                    $month >= 10 || $month <= 2 => 0.20,
                    default                     => 0.15,
                }
                : match (true) {
                    $month >= 6 && $month <= 8  => 0.25,
                    $month >= 4 && $month <= 5  => 0.35,
                    $month >= 9 && $month <= 10 => 0.15,
                    default                     => 0.05,
                };
            $ndwi = $ndwiBase + (mt_rand(-15, 15) / 100);

            // Temperature varies with season (Spain)
            $tempBase = $isCanary
                ? match (true) {
                    $month >= 6 && $month <= 9  => 28,
                    $month >= 10 || $month <= 2 => 18,
                    default                     => 22,
                }
                : match (true) {
                    $month >= 6 && $month <= 8  => 32,
                    $month >= 4 && $month <= 5  => 20,
                    $month >= 9 && $month <= 10 => 18,
                    default                     => 8,
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
        }
        
        Log::info('Mock historical data persisted', [
            'plot_id' => $plot->id,
            'records_created' => 20,
        ]);
    }

    /**
     * Fetch enriched data (NDVI + LST + Area stats)
     *
     * @param Plot $plot
     * @param bool $includeArea Whether to include area statistics
     * @param array|null $coordinates Coordinates of the selected sigpac parcel ['lat','lng']
     * @param int|null $plotSigpacId ID of MultipartPlotSigpac for polygon area
     * @return PlotRemoteSensing|null
     */
    public function fetchEnrichedData(Plot $plot, bool $includeArea = false, ?array $coordinates = null, ?int $plotSigpacId = null): ?PlotRemoteSensing
    {
        $result = $this->getLatestData($plot, true, $coordinates);

        if (!$result) {
            return null;
        }

        try {
            $token = $this->getAuthToken();

            if (!$token) {
                Log::warning('Cannot fetch enriched data without auth token');
                return $result;
            }

            $lstData = $this->lstService->fetchLSTData($plot, $token, $coordinates);

            if ($lstData) {
                // Calculate CWSI if we have both LST and air temp
                if ($lstData['lst_day'] && $result->temperature && $result->humidity) {
                    $cwsi = $this->lstService->calculateCWSI(
                        $lstData['lst_day'],
                        $result->temperature,
                        $result->humidity
                    );
                    $lstData['cwsi'] = $cwsi;
                }

                // Update with LST data
                $result->update($lstData);
            }

            if ($includeArea) {
                $areaStats = $this->areaService->requestAreaData($plot, $token, $plotSigpacId);
                
                if ($areaStats && isset($areaStats['task_id'])) {
                    // Store task ID in metadata for later retrieval
                    $metadata = $result->metadata ?? [];
                    $metadata['area_task_id'] = $areaStats['task_id'];
                    $metadata['area_task_created'] = $areaStats['created_at'];
                    $result->update(['metadata' => $metadata]);
                }
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to fetch enriched data', [
                'plot_id' => $plot->id,
                'error' => $e->getMessage(),
            ]);
            
            return $result;
        }
    }

    /**
     * Get LST service instance
     */
    public function getLSTService(): NasaLSTService
    {
        return $this->lstService;
    }

    /**
     * Get Area service instance
     */
    public function getAreaService(): NasaAreaRequestService
    {
        return $this->areaService;
    }

    /**
     * Fetch ultra-enriched data (ALL NASA products)
     * 
     * Includes:
     * - VIIRS NDVI (better than MODIS)
     * - Spectral bands (real GNDVI/NDRE)
     * - Official LAI
     * - LST
     * - SMAP Soil Moisture
     * - Official ET
     * - Optional Area Request
     * 
     * @param Plot $plot
     * @param bool $includeArea Whether to include area statistics
     * @return PlotRemoteSensing|null
     */
    public function fetchUltraEnrichedData(Plot $plot, bool $includeArea = false, ?int $plotSigpacId = null): ?PlotRemoteSensing
    {
        $token = $this->getAuthToken();

        if (!$token) {
            Log::warning('Cannot fetch ultra-enriched data without auth token');
            return $this->getLatestData($plot);
        }

        try {
            // Start with VIIRS NDVI (better than MODIS)
            $viirsData = $this->viirsService->fetchVIIRSNDVI($plot, $token, $plotSigpacId);

            // Fetch spectral bands for real indices
            $spectralData = $this->spectralService->fetchSpectralBands($plot, $token, $plotSigpacId);

            // Fetch official LAI
            $laiData = $this->laiService->fetchOfficialLAI($plot, $token, $plotSigpacId);

            // Fetch LST
            $lstData = $this->lstService->fetchLSTData($plot, $token, null, $plotSigpacId);

            // Fetch SMAP soil moisture
            $smapData = $this->smapService->fetchSoilMoisture($plot, $token, $plotSigpacId);

            // Fetch official ET
            $etData = $this->etService->fetchEvapotranspiration($plot, $token, $plotSigpacId);

            // Merge all data
            $mergedData = array_merge(
                $viirsData ?? [],
                $spectralData ?? [],
                $laiData ?? [],
                $lstData ?? [],
                $smapData ?? [],
                $etData ?? []
            );

            // Extract image_date for unique key (services may not provide it)
            $imageDate = isset($mergedData['image_date'])
                ? Carbon::parse($mergedData['image_date'])
                : Carbon::today();

            // Prepare data for repository: map service keys to model columns, exclude image_date
            $data = $this->prepareUltraEnrichedDataForStorage($plot, $mergedData, $imageDate);

            // Store in database
            $result = $this->repository->createOrUpdate($plot, $imageDate, $data);

            // Area request (async) if requested
            if ($includeArea && $result) {
                $areaStats = $this->areaService->requestAreaData($plot, $token);
                
                if ($areaStats && isset($areaStats['task_id'])) {
                    $metadata = $result->metadata ?? [];
                    $metadata['area_task_id'] = $areaStats['task_id'];
                    $metadata['area_task_created'] = $areaStats['created_at'];
                    $result->update(['metadata' => $metadata]);
                }
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to fetch ultra-enriched data', [
                'plot_id' => $plot->id,
                'error' => $e->getMessage(),
            ]);
            
            return $this->getLatestData($plot);
        }
    }

    /**
     * Prepare merged ultra-enriched data for storage.
     * Maps service keys to model columns and computes health_status/trend.
     */
    private function prepareUltraEnrichedDataForStorage(Plot $plot, array $mergedData, Carbon $imageDate): array
    {
        // Map service-specific keys to model column names
        $data = [];
        $keyMap = [
            'soil_moisture_surface' => 'soil_moisture_surface_smap',
            'soil_moisture_rootzone' => 'soil_moisture_rootzone_smap',
            'et_daily' => 'et_nasa',
            'pet_daily' => 'pet_nasa',
        ];

        foreach ($mergedData as $key => $value) {
            if ($key === 'image_date') {
                continue; // Used for unique key, not in update data
            }
            $modelKey = $keyMap[$key] ?? $key;
            $data[$modelKey] = $value;
        }

        // Compute health_status and trend from NDVI if available
        $ndvi = $mergedData['ndvi_mean'] ?? null;
        if ($ndvi !== null && is_numeric($ndvi)) {
            $ndviFloat = (float) $ndvi;
            $data['health_status'] = $this->calculateHealthStatus($ndviFloat);
            $data['trend'] = $this->calculateTrend($plot, $ndviFloat, $imageDate);
        }

        return $data;
    }

    /**
     * Get all NASA services (for external use)
     */
    public function getAllServices(): array
    {
        return [
            'lst' => $this->lstService,
            'area' => $this->areaService,
            'viirs' => $this->viirsService,
            'spectral' => $this->spectralService,
            'lai' => $this->laiService,
            'smap' => $this->smapService,
            'et' => $this->etService,
        ];
    }
}
