<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * NASA Area Request Service
 * 
 * Fetches spatial data for entire plot area (not just center point)
 * Generates vigor maps and detects zones with issues
 */
class NasaAreaRequestService
{
    private string $baseUrl;
    private bool $useMockData;
    private RateLimitService $rateLimitService;

    public function __construct(RateLimitService $rateLimitService)
    {
        $this->baseUrl = config('services.nasa_earthdata.api_url');
        $this->useMockData = config('services.nasa_earthdata.mock', true);
        $this->rateLimitService = $rateLimitService;
    }

    /**
     * Request area data for a plot
     *
     * NOTE: Area requests are ASYNC - they create a task that processes in background
     *
     * @param Plot $plot
     * @param string $token NASA auth token
     * @param int|null $plotSigpacId MultipartPlotSigpac (sigpac parcel) para usar su geometría
     * @return array|null Task info or null
     */
    public function requestAreaData(Plot $plot, string $token, ?int $plotSigpacId = null): ?array
    {
        if ($this->useMockData) {
            return $this->generateMockAreaStatistics($plot);
        }

        if (!$this->rateLimitService->canMakeNasaRequest()) {
            Log::warning('NASA API rate limit reached for area request', [
                'plot_id' => $plot->getKey(),
            ]);
            return null;
        }

        try {
            $polygon = $this->getPlotPolygon($plot, $plotSigpacId);
            
            if (!$polygon) {
                Log::warning('Plot has no polygon geometry', ['plot_id' => $plot->getKey()]);
                return null;
            }

            // Submit area task
            /** @var Response $response */
            $response = Http::withToken($token)
                ->timeout(60)
                ->post("{$this->baseUrl}/task", [
                    'task_type' => 'area',
                    'task_name' => "Plot_{$plot->getKey()}_" . now()->format('YmdHis'),
                    'params' => [
                        'dates' => [
                            [
                                'startDate' => now()->subDays(16)->format('m-d-Y'),
                                'endDate' => now()->format('m-d-Y'),
                            ]
                        ],
                        'layers' => [
                            [
                                'product' => 'MOD13Q1.061',
                                'layer' => '_250m_16_days_NDVI',
                            ]
                        ],
                        'coordinates' => $polygon,
                    ]
                ]);

            $this->rateLimitService->recordNasaRequest();

            if ($response->successful()) {
                $taskId = $response->json('task_id');
                
                Log::info('Area request task created', [
                    'plot_id' => $plot->getKey(),
                    'task_id' => $taskId,
                ]);

                return [
                    'task_id' => $taskId,
                    'status' => 'pending',
                    'created_at' => now()->toIso8601String(),
                ];
            }

            Log::warning('Area request failed', [
                'status' => $response->status(),
                'plot_id' => $plot->getKey(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Area request error', [
                'error' => $e->getMessage(),
                'plot_id' => $plot->getKey(),
            ]);
            return null;
        }
    }

    /**
     * Check status of an area request task
     */
    public function checkTaskStatus(string $taskId, string $token): ?array
    {
        try {
            /** @var Response $response */
            $response = Http::withToken($token)
                ->timeout(30)
                ->get("{$this->baseUrl}/task/{$taskId}");

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'task_id' => $taskId,
                    'status' => $data['status'], // pending, processing, done, error
                    'progress' => $data['progress'] ?? 0,
                    'created' => $data['created'] ?? null,
                    'updated' => $data['updated'] ?? null,
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Task status check failed', [
                'task_id' => $taskId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Download area data when task is complete
     */
    public function downloadAreaData(string $taskId, string $token): ?array
    {
        try {
            /** @var Response $response */
            $response = Http::withToken($token)
                ->timeout(120)
                ->get("{$this->baseUrl}/bundle/{$taskId}");

            if ($response->successful()) {
                return $this->parseAreaResponse($response->json());
            }

            Log::warning('Area data download failed', [
                'task_id' => $taskId,
                'status' => $response->status(),
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Area data download error', [
                'task_id' => $taskId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Parse area response and calculate statistics
     */
    private function parseAreaResponse(array $response): array
    {
        // Extract NDVI values for all pixels in the area
        $ndviValues = $response['MOD13Q1.061']['_250m_16_days_NDVI'] ?? [];
        
        if (empty($ndviValues)) {
            return [];
        }

        // Convert to actual NDVI values (MODIS scaling)
        $ndviValues = array_map(fn($v) => $v * 0.0001, $ndviValues);
        
        // Calculate statistics
        sort($ndviValues);
        $count = count($ndviValues);
        
        return [
            'mean' => round(array_sum($ndviValues) / $count, 4),
            'min' => round(min($ndviValues), 4),
            'max' => round(max($ndviValues), 4),
            'stddev' => round($this->calculateStdDev($ndviValues), 4),
            'percentile_25' => round($ndviValues[(int)($count * 0.25)], 4),
            'percentile_50' => round($ndviValues[(int)($count * 0.50)], 4),
            'percentile_75' => round($ndviValues[(int)($count * 0.75)], 4),
            'pixel_count' => $count,
            'coefficient_variation' => round($this->calculateCV($ndviValues), 2),
        ];
    }

    /**
     * Generate mock area statistics
     */
    private function generateMockAreaStatistics(Plot $plot): array
    {
        $seed = (int) $plot->getKey() * 3000;
        mt_srand($seed);

        // Simulate spatial variability
        $mean = 0.60 + (mt_rand(0, 200) / 1000);
        $stddev = mt_rand(50, 150) / 1000;
        
        $min = max(0.1, $mean - ($stddev * 2));
        $max = min(0.9, $mean + ($stddev * 2));
        
        $p25 = $mean - $stddev;
        $p50 = $mean;
        $p75 = $mean + $stddev;

        mt_srand();

        return [
            'mean' => round($mean, 4),
            'min' => round($min, 4),
            'max' => round($max, 4),
            'stddev' => round($stddev, 4),
            'percentile_25' => round($p25, 4),
            'percentile_50' => round($p50, 4),
            'percentile_75' => round($p75, 4),
            'pixel_count' => mt_rand(50, 500),
            'coefficient_variation' => round(($stddev / $mean) * 100, 2),
        ];
    }

    /**
     * Get plot polygon from SIGPAC geometry (uses selected sigpac parcel if plotSigpacId is passed)
     */
    private function getPlotPolygon(Plot $plot, ?int $plotSigpacId = null): ?array
    {
        $query = $plot->multiplePlotSigpacs()
            ->whereNotNull('plot_geometry_id')
            ->with('plotGeometry');

        if ($plotSigpacId) {
            $query->where('id', $plotSigpacId);
        }

        $multipart = $query->first();

        if (!$multipart || !$multipart->plotGeometry) {
            return null;
        }

        try {
            $wkt = $multipart->plotGeometry->getWktCoordinates();
            
            // Parse WKT POLYGON format
            // Format: POLYGON((lon1 lat1, lon2 lat2, ...))
            if (preg_match('/POLYGON\(\((.*?)\)\)/', $wkt, $matches)) {
                $coordsString = $matches[1];
                $coordPairs = explode(',', $coordsString);
                
                $polygon = [];
                foreach ($coordPairs as $pair) {
                    $coords = preg_split('/\s+/', trim($pair));
                    if (count($coords) >= 2) {
                        $polygon[] = [
                            'longitude' => (float) $coords[0],
                            'latitude' => (float) $coords[1],
                        ];
                    }
                }
                
                return $polygon;
            }
        } catch (\Exception $e) {
            Log::error('Failed to parse plot polygon', [
                'plot_id' => $plot->getKey(),
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Create vigor zones from area statistics
     */
    public function createVigorZones(array $statistics): array
    {
        $mean = $statistics['mean'];
        $stddev = $statistics['stddev'];
        
        return [
            'low_vigor' => [
                'label' => 'Bajo Vigor',
                'color' => 'red',
                'range' => [
                    'min' => $statistics['min'],
                    'max' => $mean - $stddev,
                ],
                'description' => 'Zonas con problemas - Requieren atención',
                'icon' => '🔴',
            ],
            'medium_vigor' => [
                'label' => 'Vigor Medio',
                'color' => 'yellow',
                'range' => [
                    'min' => $mean - $stddev,
                    'max' => $mean + $stddev,
                ],
                'description' => 'Zonas normales',
                'icon' => '🟡',
            ],
            'high_vigor' => [
                'label' => 'Alto Vigor',
                'color' => 'green',
                'range' => [
                    'min' => $mean + $stddev,
                    'max' => $statistics['max'],
                ],
                'description' => 'Zonas con excelente desarrollo',
                'icon' => '🟢',
            ],
        ];
    }

    /**
     * Calculate standard deviation
     */
    private function calculateStdDev(array $values): float
    {
        $count = count($values);
        if ($count === 0) return 0.0;
        
        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $values)) / $count;
        
        return sqrt($variance);
    }

    /**
     * Calculate coefficient of variation
     */
    private function calculateCV(array $values): float
    {
        $mean = array_sum($values) / count($values);
        $stddev = $this->calculateStdDev($values);
        
        return $mean > 0 ? ($stddev / $mean) * 100 : 0;
    }
}
