<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
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
     * Fetch VIIRS NDVI (better than MODIS for small plots)
     */
    public function fetchVIIRSNDVI(Plot $plot, string $token): ?array
    {
        if ($this->useMockData) {
            return $this->generateMockVIIRS($plot);
        }

        if (!$this->rateLimitService->canMakeNasaRequest()) {
            Log::warning('NASA API rate limit reached for VIIRS', ['plot_id' => $plot->id]);
            return null;
        }

        try {
            $coords = $this->getPlotCoordinates($plot);

            // VNP13A1: VIIRS Vegetation Indices 500m, 16-day
            $response = Http::withToken($token)
                ->timeout(60)
                ->get("{$this->baseUrl}/bundle/VNP13A1.001/point", [
                    'latitude' => $coords['lat'],
                    'longitude' => $coords['lon'],
                    'startDate' => now()->subDays(16)->format('m-d-Y'),
                    'endDate' => now()->format('m-d-Y'),
                ]);

            $this->rateLimitService->recordNasaRequest();

            if ($response->successful()) {
                return $this->parseVIIRSResponse($response->json());
            }

            Log::warning('NASA VIIRS API request failed', [
                'status' => $response->status(),
                'plot_id' => $plot->id,
            ]);

            if (config('app.env') !== 'production') {
                return $this->generateMockVIIRS($plot);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('NASA VIIRS API error', [
                'error' => $e->getMessage(),
                'plot_id' => $plot->id,
            ]);

            if (config('app.env') !== 'production') {
                return $this->generateMockVIIRS($plot);
            }

            return null;
        }
    }

    /**
     * Parse VIIRS response
     */
    private function parseVIIRSResponse(array $response): array
    {
        // VIIRS scaling: multiply by 0.0001
        $ndviRaw = $response['_500_m_16_days_NDVI'] ?? null;
        $eviRaw = $response['_500_m_16_days_EVI'] ?? null;
        
        $ndvi = $ndviRaw ? $ndviRaw * 0.0001 : null;
        $evi = $eviRaw ? $eviRaw * 0.0001 : null;
        
        // Quality flags
        $reliability = $response['_500_m_16_days_VI_Quality'] ?? null;
        $cloudCoverage = $this->extractCloudCoverage($reliability);

        return [
            'ndvi_mean' => $ndvi,
            'evi_mean' => $evi,
            'cloud_coverage' => $cloudCoverage,
            'pixel_reliability' => $reliability,
            'satellite' => 'VIIRS',
            'image_source' => 'NASA VIIRS VNP13A1.001',
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
    private function generateMockVIIRS(Plot $plot): array
    {
        $month = now()->month;
        $seed = $plot->id * 1500 + now()->dayOfYear;
        mt_srand($seed);

        // VIIRS has better sensitivity, so slightly higher precision
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
            'image_source' => 'NASA VIIRS VNP13A1.001 (Mock)',
        ];
    }

    /**
     * Get plot coordinates
     */
    private function getPlotCoordinates(Plot $plot): array
    {
        return [
            'lat' => $plot->latitude ?? 40.4168,
            'lon' => $plot->longitude ?? -3.7038,
        ];
    }

    /**
     * Compare VIIRS vs MODIS quality
     */
    public function compareWithMODIS(float $viirsNDVI, float $modisNDVI): array
    {
        $diff = abs($viirsNDVI - $modisNDVI);
        
        if ($diff < 0.05) {
            $status = 'consistent';
            $message = 'VIIRS y MODIS coinciden';
            $recommendation = 'Datos fiables';
        } elseif ($diff < 0.1) {
            $status = 'acceptable';
            $message = 'Ligera diferencia entre sensores';
            $recommendation = 'Verificar nubosidad';
        } else {
            $status = 'divergent';
            $message = 'Gran diferencia entre sensores';
            $recommendation = 'Posible problema de calidad - revisar';
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
}
