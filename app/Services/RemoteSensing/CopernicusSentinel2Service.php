<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use App\Repositories\PlotRemoteSensingRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Copernicus Data Space — Sentinel-2 L2A via Sentinel Hub Statistical API
 *
 * 100% free (with CDSE account). Provides vegetation indices at 10m resolution.
 * Replaces MODIS (250m) for NDVI, GNDVI, NDWI, NDRE.
 *
 * Auth: https://identity.dataspace.copernicus.eu/...
 * API:  https://sh.dataspace.copernicus.eu/api/v1/statistics
 */
class CopernicusSentinel2Service
{
    private const AUTH_URL  = 'https://identity.dataspace.copernicus.eu/auth/realms/CDSE/protocol/openid-connect/token';
    private const STATS_URL = 'https://sh.dataspace.copernicus.eu/api/v1/statistics';
    private const TOKEN_KEY = 'copernicus_access_token';
    private const TOKEN_TTL = 540; // 9 min — token expires in 10

    public function __construct(
        private PlotRemoteSensingRepository $repository,
        private RateLimitService $rateLimitService,
    ) {}

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Fetch Sentinel-2 vegetation indices and store them.
     * Called by UpdatePlotSentinel2Job.
     */
    public function fetchAndStore(Plot $plot, ?int $plotSigpacId = null): ?PlotRemoteSensing
    {
        try {
            // Check monthly Copernicus processing unit budget (improvement #12)
            if (!$this->rateLimitService->canUseCopernicus()) {
                Log::warning('Sentinel-2: monthly processing unit limit reached', ['plot_id' => $plot->id]);
                return $this->repository->getLatestForPlot($plot, $plotSigpacId);
            }

            $token    = $this->authenticate();
            $geometry = $this->getPlotGeometry($plot, $plotSigpacId);
            $areaHa   = (float) ($plot->area ?? 0.5);

            $to   = Carbon::now();
            $from = $to->copy()->subDays(60);

            $rawData = $this->fetchStatistics($token, $geometry, $from, $to);

            if (!$rawData) {
                Log::warning('Sentinel-2: no data returned from Statistical API', ['plot_id' => $plot->id, 'plot_sigpac_id' => $plotSigpacId]);
                return $this->repository->getLatestForPlot($plot, $plotSigpacId);
            }

            $best = $this->findBestInterval($rawData['data'] ?? [], $areaHa);

            if (!$best) {
                Log::warning('Sentinel-2: no valid (cloud-free) interval found in last 60 days', ['plot_id' => $plot->id, 'plot_sigpac_id' => $plotSigpacId]);
                return $this->repository->getLatestForPlot($plot, $plotSigpacId);
            }

            // Track Copernicus processing unit usage (estimate: ~1000 units per ha)
            $estimatedUnits = (int) ceil($areaHa * 1000);
            $this->rateLimitService->incrementCopernicusUsage($estimatedUnits);

            // Use the end of the interval as the image date
            $imageDate = Carbon::parse($best['interval']['to'])->subDay();
            $data      = $this->mapToStorageData($best['interval'], $imageDate, $plot, $plotSigpacId, $best['validCount'], $best['sampleCount']);

            $result = $this->repository->createOrUpdate($plot, $imageDate, $data, $plotSigpacId);

            Log::info('Sentinel-2 data stored', [
                'plot_id'           => $plot->id,
                'plot_sigpac_id'    => $plotSigpacId,
                'image_date'        => $imageDate->toDateString(),
                'ndvi'              => $data['ndvi_mean'],
                'gndvi'             => $data['gndvi'],
                'cloud_pct'         => $data['cloud_coverage'],
                'validCount'        => $best['validCount'],
                'valid_pixel_ratio' => $data['metadata']['valid_pixel_ratio'] ?? null,
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('CopernicusSentinel2Service::fetchAndStore failed', [
                'plot_id' => $plot->id,
                'error'   => $e->getMessage(),
            ]);

            return null;
        }
    }

    // -------------------------------------------------------------------------
    // Authentication
    // -------------------------------------------------------------------------

    /**
     * OAuth2 password-grant against Copernicus Data Space.
     * Token is cached for 9 minutes (expires in 10).
     */
    private function authenticate(): string
    {
        return Cache::remember(self::TOKEN_KEY, self::TOKEN_TTL, function () {
            $response = Http::asForm()
                ->timeout(20)
                ->post(self::AUTH_URL, [
                    'client_id'  => 'cdse-public',
                    'username'   => config('services.copernicus.username'),
                    'password'   => config('services.copernicus.password'),
                    'grant_type' => 'password',
                ]);

            if (!$response->successful()) {
                throw new \RuntimeException(
                    "Copernicus auth failed [{$response->status()}]: " . substr($response->body(), 0, 300)
                );
            }

            $token = $response->json('access_token');

            if (!$token) {
                throw new \RuntimeException('Copernicus auth: response missing access_token');
            }

            return $token;
        });
    }

    // -------------------------------------------------------------------------
    // Geometry
    // -------------------------------------------------------------------------

    /**
     * Build a GeoJSON geometry for the plot.
     * If $plotSigpacId (MultipartPlotSigpac.id) is provided, uses that specific sigpac parcel's polygon.
     * Otherwise falls back to the first geometry, then a 200m bounding box.
     */
    private function getPlotGeometry(Plot $plot, ?int $plotSigpacId = null): array
    {
        if ($plotSigpacId) {
            $mps = \App\Models\MultipartPlotSigpac::where('id', $plotSigpacId)
                ->where('plot_id', $plot->id)
                ->with('plotGeometry')
                ->first();
            $plotGeometry = $mps?->plotGeometry;
        } else {
            $plotGeometry = $plot->plotGeometries()->first();
        }

        if ($plotGeometry) {
            $points = $plotGeometry->getCoordinatesAsArray();

            if (count($points) >= 3) {
                // GeoJSON: [lng, lat] order
                $coords = array_map(fn($p) => [(float) $p['lng'], (float) $p['lat']], $points);

                return [
                    'type'        => 'Polygon',
                    'coordinates' => [$coords],
                ];
            }
        }

        // Fallback: 200 m × 200 m bounding box around the centroid (~0.001°)
        $c   = CoordinatesHelper::getCoordinates($plot);
        $lat = $c['lat'];
        $lon = $c['lon'];
        $d   = 0.001;

        return [
            'type'        => 'Polygon',
            'coordinates' => [[
                [$lon - $d, $lat - $d],
                [$lon + $d, $lat - $d],
                [$lon + $d, $lat + $d],
                [$lon - $d, $lat + $d],
                [$lon - $d, $lat - $d],
            ]],
        ];
    }

    // -------------------------------------------------------------------------
    // Statistical API
    // -------------------------------------------------------------------------

    /**
     * Call the Sentinel Hub Statistical API and return the JSON response.
     */
    private function fetchStatistics(string $token, array $geometry, Carbon $from, Carbon $to): ?array
    {
        $evalscript = $this->buildEvalscript();

        $response = Http::withToken($token)
            ->timeout(60)
            ->post(self::STATS_URL, [
                'input' => [
                    'bounds' => [
                        'geometry'   => $geometry,
                        'properties' => [
                            'crs' => 'http://www.opengis.net/def/crs/EPSG/0/4326',
                        ],
                    ],
                    'data' => [[
                        'dataFilter' => [
                            'timeRange' => [
                                'from' => $from->toIso8601ZuluString(),
                                'to'   => $to->toIso8601ZuluString(),
                            ],
                            'maxCloudCoverage' => 80,
                        ],
                        'type' => 'sentinel-2-l2a',
                    ]],
                ],
                'aggregation' => [
                    'timeRange' => [
                        'from' => $from->toIso8601ZuluString(),
                        'to'   => $to->toIso8601ZuluString(),
                    ],
                    'aggregationInterval' => ['of' => 'P5D'],
                    'evalscript'          => $evalscript,
                    'resx'                => 10,
                    'resy'                => 10,
                ],
                'calculations' => [
                    'ndvi'          => ['statistics' => new \stdClass()],
                    'gndvi'         => ['statistics' => new \stdClass()],
                    'ndwi'          => ['statistics' => new \stdClass()],
                    'ndre'          => ['statistics' => new \stdClass()],
                    'red'           => ['statistics' => new \stdClass()],
                    'nir'           => ['statistics' => new \stdClass()],
                    'green'         => ['statistics' => new \stdClass()],
                    'zone_critical' => ['statistics' => new \stdClass()],
                    'zone_low'      => ['statistics' => new \stdClass()],
                    'zone_moderate' => ['statistics' => new \stdClass()],
                    'zone_good'     => ['statistics' => new \stdClass()],
                    'zone_excellent'=> ['statistics' => new \stdClass()],
                ],
            ]);

        if (!$response->successful()) {
            Log::warning('Sentinel-2 Statistical API error', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);

            return null;
        }

        $json = $response->json();

        Log::info('Sentinel-2 Statistical API response', [
            'status'         => $response->status(),
            'interval_count' => count($json['data'] ?? []),
            'first_interval' => isset($json['data'][0]) ? json_encode($json['data'][0]) : null,
        ]);

        return $json;
    }

    /**
     * Evalscript that computes NDVI, GNDVI, NDWI, NDRE, raw bands (B03/B04/B08),
     * and NDVI-based vigor zone classification per pixel.
     * Clouds/shadows masked via the SCL band.
     */
    private function buildEvalscript(): string
    {
        return '//VERSION=3
function setup() {
    return {
        input: [{ bands: ["B03","B04","B05","B08","B11","SCL"], units: ["REFLECTANCE","REFLECTANCE","REFLECTANCE","REFLECTANCE","REFLECTANCE","DN"] }],
        output: [
            { id: "ndvi",          bands: 1, sampleType: "FLOAT32" },
            { id: "gndvi",         bands: 1, sampleType: "FLOAT32" },
            { id: "ndwi",          bands: 1, sampleType: "FLOAT32" },
            { id: "ndre",          bands: 1, sampleType: "FLOAT32" },
            { id: "red",           bands: 1, sampleType: "FLOAT32" },
            { id: "nir",           bands: 1, sampleType: "FLOAT32" },
            { id: "green",         bands: 1, sampleType: "FLOAT32" },
            { id: "zone_critical", bands: 1, sampleType: "UINT8"   },
            { id: "zone_low",      bands: 1, sampleType: "UINT8"   },
            { id: "zone_moderate", bands: 1, sampleType: "UINT8"   },
            { id: "zone_good",     bands: 1, sampleType: "UINT8"   },
            { id: "zone_excellent",bands: 1, sampleType: "UINT8"   },
            { id: "dataMask",      bands: 1, sampleType: "UINT8"   }
        ]
    };
}
function evaluatePixel(sample) {
    // SCL cloud mask: 3=shadow 8=medium cloud 9=high cloud 10=thin cirrus
    if (sample.SCL==3||sample.SCL==8||sample.SCL==9||sample.SCL==10) {
        return { ndvi:[NaN], gndvi:[NaN], ndwi:[NaN], ndre:[NaN],
                 red:[NaN], nir:[NaN], green:[NaN],
                 zone_critical:[0], zone_low:[0], zone_moderate:[0], zone_good:[0], zone_excellent:[0],
                 dataMask:[0] };
    }
    var eps=1e-10, b03=sample.B03, b04=sample.B04, b05=sample.B05, b08=sample.B08, b11=sample.B11;
    var ndvi=(b08-b04)/(b08+b04+eps);
    return {
        ndvi:          [ndvi],
        gndvi:         [(b08-b03)/(b08+b03+eps)],
        ndwi:          [(b08-b11)/(b08+b11+eps)],
        ndre:          [(b08-b05)/(b08+b05+eps)],
        red:           [b04],
        nir:           [b08],
        green:         [b03],
        zone_critical: [ndvi < 0.2 ? 1 : 0],
        zone_low:      [(ndvi >= 0.2 && ndvi < 0.35) ? 1 : 0],
        zone_moderate: [(ndvi >= 0.35 && ndvi < 0.5) ? 1 : 0],
        zone_good:     [(ndvi >= 0.5 && ndvi < 0.65) ? 1 : 0],
        zone_excellent:[ndvi >= 0.65 ? 1 : 0],
        dataMask:      [1]
    };
}';
    }

    // -------------------------------------------------------------------------
    // Interval selection
    // -------------------------------------------------------------------------

    /**
     * Pick the most recent 5-day interval that has enough valid (non-cloudy) pixels.
     * Returns ['interval' => $interval, 'validCount' => int, 'sampleCount' => int] or null.
     *
     * Improvement #2: minimum pixel threshold is adaptive — at least 5% of estimated
     * pixel count for the parcel area, with a hard minimum of 5 pixels.
     */
    private function findBestInterval(array $intervals, ?float $areaHa = null): ?array
    {
        // Base minimum from estimated area at 10m resolution (5% of expected pixel count)
        $estimatedPixels  = max(1, (int) round(($areaHa ?? 0.5) * 10000)); // ha → m², each pixel = 100m²
        $minFromArea      = max(5, (int) round($estimatedPixels * 0.05));

        // Most recent first
        usort($intervals, fn($a, $b) => strcmp($b['interval']['to'], $a['interval']['to']));

        $debugIntervals = [];

        foreach ($intervals as $interval) {
            $stats = $interval['outputs']['ndvi']['bands']['B0']['stats'] ?? null;

            if (!$stats) {
                $debugIntervals[] = ['interval' => $interval['interval'], 'reason' => 'no_stats'];
                continue;
            }

            $sampleCount = (int) ($stats['sampleCount'] ?? 0);
            $noDataCount = (int) ($stats['noDataCount'] ?? $sampleCount);
            $validCount  = $sampleCount - $noDataCount;
            $mean        = $stats['mean'] ?? null;

            // Cap minPixels by 50% of actual coverage so tiny parcels (sampleCount=1,2...)
            // are never blocked by an unreachable threshold based on area estimate alone.
            $minFromSample = max(1, (int) floor($sampleCount * 0.5));
            $minPixels     = min($minFromArea, $minFromSample);

            $debugIntervals[] = [
                'interval'    => $interval['interval'],
                'sampleCount' => $sampleCount,
                'noDataCount' => $noDataCount,
                'validCount'  => $validCount,
                'minPixels'   => $minPixels,
                'mean'        => $mean,
            ];

            if ($validCount >= $minPixels && $mean !== null && !is_nan((float) $mean)) {
                Log::info('Sentinel-2: best interval selected', [
                    'interval'   => $interval['interval'],
                    'validCount' => $validCount,
                    'minPixels'  => $minPixels,
                    'mean'       => $mean,
                ]);
                return [
                    'interval'    => $interval,
                    'validCount'  => $validCount,
                    'sampleCount' => $sampleCount,
                ];
            }
        }

        Log::warning('Sentinel-2: no valid interval found', ['intervals' => $debugIntervals]);

        return null;
    }

    // -------------------------------------------------------------------------
    // Storage mapping
    // -------------------------------------------------------------------------

    /**
     * Map the API interval to PlotRemoteSensing column values.
     * Improvements #3, #4, #5: adds metadata (valid_pixel_ratio), raw bands, zone stats.
     */
    private function mapToStorageData(
        array $interval,
        Carbon $imageDate,
        Plot $plot,
        ?int $plotSigpacId = null,
        int $validCount = 0,
        int $sampleCount = 0
    ): array {
        $outputs = $interval['outputs'];

        $ndvi   = $this->stat($outputs, 'ndvi', 'mean');
        $gndvi  = $this->stat($outputs, 'gndvi', 'mean');
        $ndwi   = $this->stat($outputs, 'ndwi', 'mean');
        $ndre   = $this->stat($outputs, 'ndre', 'mean');

        $ndviMin   = $this->stat($outputs, 'ndvi', 'min');
        $ndviMax   = $this->stat($outputs, 'ndvi', 'max');
        $ndviStdev = $this->stat($outputs, 'ndvi', 'stDev');

        // Cloud coverage estimate: fraction of masked pixels
        $ndviStats    = $outputs['ndvi']['bands']['B0']['stats'] ?? [];
        $totalPixels  = max(1, (int) ($ndviStats['sampleCount'] ?? 1));
        $noDataCount  = (int) ($ndviStats['noDataCount'] ?? 0);
        $cloudPct     = (int) round(($noDataCount / $totalPixels) * 100);

        $ndviFloat = is_numeric($ndvi) ? max(-1.0, min(1.0, (float) $ndvi)) : 0.0;

        // Improvement #3: valid pixel ratio in metadata
        $validPixelRatio = $sampleCount > 0
            ? round($validCount / $sampleCount * 100, 1)
            : null;

        // Improvement #5: vigor zone percentages from per-pixel zone outputs
        // zone_* outputs return 1/0 per pixel; mean = fraction of valid pixels in that zone
        $zoneCritical  = $this->stat($outputs, 'zone_critical', 'mean');
        $zoneLow       = $this->stat($outputs, 'zone_low', 'mean');
        $zoneModerate  = $this->stat($outputs, 'zone_moderate', 'mean');
        $zoneGood      = $this->stat($outputs, 'zone_good', 'mean');
        $zoneExcellent = $this->stat($outputs, 'zone_excellent', 'mean');

        $ndviStdevFloat = is_numeric($ndviStdev) ? (float) $ndviStdev : 0.0;
        $cv = $ndviStdevFloat / max(0.001, abs($ndviFloat)); // coefficient of variation

        return [
            'ndvi_mean'      => round($ndviFloat, 3),
            'ndvi_min'       => is_numeric($ndviMin) ? round((float) $ndviMin, 3) : null,
            'ndvi_max'       => is_numeric($ndviMax) ? round((float) $ndviMax, 3) : null,
            'ndvi_stddev'    => is_numeric($ndviStdev) ? round((float) $ndviStdev, 4) : null,
            'ndwi_mean'      => is_numeric($ndwi) ? round((float) $ndwi, 3) : null,
            'gndvi'          => is_numeric($gndvi) ? round((float) $gndvi, 3) : null,
            'ndre'           => is_numeric($ndre) ? round((float) $ndre, 3) : null,
            'cloud_coverage' => $cloudPct,
            'image_source'   => 'Copernicus Sentinel-2 L2A',
            'satellite'      => 'SENTINEL-2',
            'data_source'    => 'copernicus',
            'health_status'  => $this->healthStatus($ndviFloat),
            'trend'          => $this->trend($plot, $ndviFloat, $imageDate, $plotSigpacId),
            // Improvement #4: raw band reflectances
            'red_band'       => is_numeric($r = $this->stat($outputs, 'red', 'mean')) ? round((float) $r, 4) : null,
            'nir_band'       => is_numeric($n = $this->stat($outputs, 'nir', 'mean')) ? round((float) $n, 4) : null,
            'green_band'     => is_numeric($g = $this->stat($outputs, 'green', 'mean')) ? round((float) $g, 4) : null,
            // Improvement #3: metadata with coverage quality info
            'metadata' => [
                'valid_pixel_count'  => $validCount,
                'total_pixel_count'  => $sampleCount,
                'valid_pixel_ratio'  => $validPixelRatio,
                'interval_from'      => $interval['interval']['from'],
                'interval_to'        => $interval['interval']['to'],
            ],
            // Improvement #5: vigor zone distribution
            'area_statistics' => [
                'zone_critical_pct'  => is_numeric($zoneCritical)  ? round((float) $zoneCritical * 100, 1)  : null,
                'zone_low_pct'       => is_numeric($zoneLow)       ? round((float) $zoneLow * 100, 1)       : null,
                'zone_moderate_pct'  => is_numeric($zoneModerate)  ? round((float) $zoneModerate * 100, 1)  : null,
                'zone_good_pct'      => is_numeric($zoneGood)      ? round((float) $zoneGood * 100, 1)      : null,
                'zone_excellent_pct' => is_numeric($zoneExcellent) ? round((float) $zoneExcellent * 100, 1) : null,
                'cv'                 => round($cv, 4),
            ],
        ];
    }

    /**
     * Extract a statistic value from the outputs array.
     */
    private function stat(array $outputs, string $index, string $key): mixed
    {
        return $outputs[$index]['bands']['B0']['stats'][$key] ?? null;
    }

    // -------------------------------------------------------------------------
    // Helpers (same thresholds as NasaEarthdataService)
    // -------------------------------------------------------------------------

    private function healthStatus(float $ndvi): string
    {
        return match (true) {
            $ndvi >= 0.7  => 'excellent',
            $ndvi >= 0.5  => 'good',
            $ndvi >= 0.3  => 'moderate',
            $ndvi >= 0.15 => 'poor',
            default       => 'critical',
        };
    }

    private function trend(Plot $plot, float $currentNdvi, Carbon $currentDate, ?int $plotSigpacId = null): string
    {
        $previous = $this->repository->getPreviousData($plot, $currentDate, $plotSigpacId);

        if (!$previous) {
            return 'stable';
        }

        $diff = $currentNdvi - $previous->ndvi_mean;

        return match (true) {
            $diff > 0.05  => 'increasing',
            $diff < -0.05 => 'decreasing',
            default       => 'stable',
        };
    }
}
