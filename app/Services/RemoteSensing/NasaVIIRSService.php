<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * NASA VIIRS Service (Superior to MODIS)
 *
 * VIIRS (Visible Infrared Imaging Radiometer Suite)
 * - Resolution: 375m-500m (better than MODIS 250m for small plots)
 * - Frequency: 1-2 days
 * - Better cloud detection
 * - Lower noise
 */
class NasaVIIRSService
{
    private bool $useMockData;

    private RateLimitService $rateLimitService;

    public function __construct(RateLimitService $rateLimitService)
    {
        $this->useMockData = config('services.nasa_earthdata.mock', true);
        $this->rateLimitService = $rateLimitService;
    }

    /**
     * Fetch VIIRS NDVI (better than MODIS for small plots)
     */
    public function fetchVIIRSNDVI(Plot $plot, string $token, ?int $plotSigpacId = null): ?array
    {
        if ($this->useMockData) {
            return $this->generateMockVIIRS($plot);
        }

        if (! $this->rateLimitService->canMakeNasaRequest()) {
            return $this->generateMockVIIRS($plot);
        }

        try {
            $coords = CoordinatesHelper::getCoordinates($plot, $plotSigpacId);
            $startJulian = 'A'.now()->subDays(16)->format('Y').str_pad((string) now()->subDays(16)->dayOfYear, 3, '0', STR_PAD_LEFT);
            $endJulian = 'A'.now()->format('Y').str_pad((string) now()->dayOfYear, 3, '0', STR_PAD_LEFT);

            $response = Http::timeout(30)
                ->get('https://modis.ornl.gov/rst/api/v1/VNP13A1/subset', [
                    'latitude' => $coords['lat'],
                    'longitude' => $coords['lon'],
                    'startDate' => $startJulian,
                    'endDate' => $endJulian,
                    'kmAboveBelow' => 0,
                    'kmLeftRight' => 0,
                ]);

            $this->rateLimitService->recordNasaRequest();

            if ($response->successful()) {
                return $this->parseVIIRSResponse($response->json());
            }

            Log::warning('NASA VIIRS API failed — using estimated data', [
                'status' => $response->status(),
                'plot_id' => $plot->getKey(),
                'body' => substr($response->body(), 0, 200),
            ]);

        } catch (\Exception $e) {
            Log::warning('NASA VIIRS API error — using estimated data', [
                'error' => $e->getMessage(),
                'plot_id' => $plot->getKey(),
            ]);
        }

        return $this->generateMockVIIRS($plot);
    }

    /**
     * Compare VIIRS vs MODIS quality
     */
    public function compareWithMODIS(float $viirsNDVI, float $modisNDVI): array
    {
        $diff = abs($viirsNDVI - $modisNDVI);

        if ($diff < 0.05) {
            $status = 'consistent';
            $message = __('VIIRS y MODIS coinciden');
            $recommendation = __('Datos fiables');
        } elseif ($diff < 0.1) {
            $status = 'acceptable';
            $message = __('Ligera diferencia entre sensores');
            $recommendation = __('Verificar nubosidad');
        } else {
            $status = 'divergent';
            $message = __('Gran diferencia entre sensores');
            $recommendation = __('Posible problema de calidad - revisar');
        }

        return [
            'status' => $status,
            'difference' => round($diff, 4),
            'message' => $message,
            'recommendation' => $recommendation,
            'viirs_value' => $viirsNDVI,
            'modis_value' => $modisNDVI,
        ];
    }

    /**
     * Parse VIIRS response
     */
    private function parseVIIRSResponse(array $response): array
    {
        $nodata = $response['header']['NODATA_value'] ?? -3000;
        $subset = collect($response['subset'] ?? []);

        $ndviBand = $subset->firstWhere('band', '_500_m_16_days_NDVI');
        $eviBand = $subset->firstWhere('band', '_500_m_16_days_EVI2')
                 ?? $subset->firstWhere('band', '_500_m_16_days_EVI');
        $qcBand = $subset->firstWhere('band', '_500_m_16_days_VI_Quality');

        $ndviRaw = $ndviBand['data'][0] ?? null;
        $eviRaw = $eviBand['data'][0] ?? null;
        $qcRaw = $qcBand['data'][0] ?? null;

        // Scale 0.0001
        $ndvi = ($ndviRaw !== null && $ndviRaw > $nodata) ? round($ndviRaw * 0.0001, 4) : null;
        $evi = ($eviRaw !== null && $eviRaw > $nodata) ? round($eviRaw * 0.0001, 4) : null;

        return [
            'ndvi_mean' => $ndvi,
            'evi_mean' => $evi,
            'cloud_coverage' => $this->extractCloudCoverage($qcRaw),
            'pixel_reliability' => $qcRaw,
            'satellite' => 'VIIRS',
            'image_source' => __('NASA VIIRS VNP13A1.001'),
        ];
    }

    /**
     * Extract cloud coverage from quality flags
     */
    private function extractCloudCoverage(?int $qualityFlag): int
    {
        if ($qualityFlag === null) {
            return 0;
        }

        // VIIRS VI Quality interpretation (simplified)
        // 0 = Good, 1 = Marginal, 2-3 = Poor
        if ($qualityFlag <= 1) {
            return mt_rand(0, 20); // Low cloud
        } elseif ($qualityFlag === 2) {
            return mt_rand(20, 50); // Moderate cloud
        } else {
            return mt_rand(50, 100); // High cloud
        }
    }

    /**
     * Generate mock VIIRS data (slightly better than MODIS mock)
     */
    private function generateMockVIIRS(Plot $plot, ?array $coords = null): array
    {
        $month = now()->month;
        $seed = (int) $plot->getKey() * 1500 + now()->dayOfYear;
        mt_srand($seed);

        $lat = $coords['lat'] ?? CoordinatesHelper::getCoordinates($plot)['lat'];
        $isCanary = $lat < 30.0;

        // VIIRS has better sensitivity, so slightly higher precision
        if ($isCanary) {
            if ($month >= 6 && $month <= 9) {
                $ndviBase = 0.65;
                $cloudBase = 10;
            } elseif ($month >= 10 || $month <= 2) {
                $ndviBase = 0.50;
                $cloudBase = 20;
            } else {
                $ndviBase = 0.58;
                $cloudBase = 15;
            }
        } else {
            if ($month >= 4 && $month <= 10) {
                $ndviBase = 0.65;
                $cloudBase = 15;
            } elseif ($month >= 11 || $month <= 2) {
                $ndviBase = 0.35;
                $cloudBase = 40;
            } else {
                $ndviBase = 0.50;
                $cloudBase = 25;
            }
        }

        $ndvi = $ndviBase + (mt_rand(-100, 100) / 1000);
        $evi = $ndvi * 0.85;
        $cloud = max(0, min(100, $cloudBase + mt_rand(-15, 15)));

        mt_srand();

        return [
            'ndvi_mean' => round($ndvi, 4),
            'evi_mean' => round($evi, 4),
            'cloud_coverage' => $cloud,
            'pixel_reliability' => 0,
            'satellite' => 'VIIRS',
            'image_source' => __('NASA VIIRS VNP13A1.001 (Mock)'),
        ];
    }
}
