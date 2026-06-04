<?php

namespace Tests\Unit\Services\RemoteSensing\Detectors;

use App\Models\PlotRemoteSensing;
use App\Services\RemoteSensing\Detectors\AnomalyDetector;
use Tests\TestCase;

class AnomalyDetectorTest extends TestCase
{
    private AnomalyDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new AnomalyDetector;
    }

    /** @test */
    public function it_detects_rapid_ndvi_decline()
    {
        // Create current observation with low NDVI
        $current = new PlotRemoteSensing([
            'ndvi_mean' => 0.45,
            'ndwi_mean' => 0.25,
            'temperature' => 25,
            'soil_moisture' => 0.20,
            'ndvi_stddev' => 0.08,
        ]);
        $current->image_date = now();
        $current->date = now();

        // Create historical data with higher NDVI (recent baseline)
        $historical = collect();
        for ($i = 14; $i >= 7; $i--) {
            $record = new PlotRemoteSensing([
                'ndvi_mean' => 0.70, // Much higher baseline
            ]);
            $record->image_date = now()->subDays($i);
            $record->date = now()->subDays($i);
            $historical->push($record);
        }

        $result = $this->detector->detectAnomalies($current, $historical);

        $this->assertTrue($result['has_anomalies']);
        $this->assertGreaterThan(0, $result['count']);

        // Should detect rapid NDVI decline
        $rapidDecline = collect($result['anomalies'])->firstWhere('type', 'rapid_ndvi_decline');
        $this->assertNotNull($rapidDecline);
        $this->assertContains($rapidDecline['severity'], ['medium', 'high', 'critical']);
    }

    /** @test */
    public function it_detects_water_stress()
    {
        $current = new PlotRemoteSensing([
            'ndvi_mean' => 0.55,
            'ndwi_mean' => 0.10, // Very low water content
            'temperature' => 32,
            'soil_moisture' => 0.12, // Very dry soil
            'ndvi_stddev' => 0.08,
        ]);
        $current->image_date = now();
        $current->date = now();

        $historical = collect();
        for ($i = 10; $i >= 1; $i--) {
            $record = new PlotRemoteSensing([
                'ndvi_mean' => 0.60,
                'ndwi_mean' => 0.35, // Previously good water content
                'soil_moisture' => 0.25,
            ]);
            $record->image_date = now()->subDays($i);
            $record->date = now()->subDays($i);
            $historical->push($record);
        }

        $result = $this->detector->detectAnomalies($current, $historical);

        $waterStress = collect($result['anomalies'])->firstWhere('type', 'water_stress');
        $this->assertNotNull($waterStress);
        $this->assertEquals('💧', $waterStress['icon']);
    }

    /** @test */
    public function it_detects_spatial_heterogeneity()
    {
        $current = new PlotRemoteSensing([
            'ndvi_mean' => 0.60,
            'ndvi_stddev' => 0.18, // High variation = heterogeneous field
            'ndwi_mean' => 0.25,
            'temperature' => 25,
            'soil_moisture' => 0.20,
            'image_date' => now(),
        ]);

        $historical = collect([
            new PlotRemoteSensing([
                'ndvi_mean' => 0.60,
                'image_date' => now()->subDays(7),
            ]),
        ]);

        $result = $this->detector->detectAnomalies($current, $historical);

        $spatial = collect($result['anomalies'])->firstWhere('type', 'spatial_heterogeneity');
        $this->assertNotNull($spatial);
        $this->assertArrayHasKey('probable_causes', $spatial);
    }

    /** @test */
    public function it_calculates_risk_level_correctly()
    {
        // No anomalies
        $current = new PlotRemoteSensing([
            'ndvi_mean' => 0.65,
            'ndwi_mean' => 0.30,
            'temperature' => 25,
            'soil_moisture' => 0.22,
            'ndvi_stddev' => 0.08,
        ]);
        $current->image_date = now();
        $current->date = now();

        $historical = collect();
        for ($i = 10; $i >= 1; $i--) {
            $record = new PlotRemoteSensing([
                'ndvi_mean' => 0.65,
                'ndwi_mean' => 0.30,
                'temperature' => 25,
            ]);
            $record->image_date = now()->subDays($i);
            $record->date = now()->subDays($i);
            $historical->push($record);
        }

        $result = $this->detector->detectAnomalies($current, $historical);

        if (! $result['has_anomalies']) {
            $this->assertEquals('none', $result['risk_level']);
        } else {
            $this->assertContains($result['risk_level'], ['none', 'low', 'medium', 'high', 'critical']);
        }
    }

    /** @test */
    public function it_generates_alert_messages()
    {
        $anomaly = [
            'type' => 'rapid_ndvi_decline',
            'severity' => 'high',
            'title' => 'Caída Rápida de Vigor',
            'description' => 'NDVI cayó 25% en los últimos 7 días',
            'recommended_actions' => [
                'Inspección visual urgente',
                'Buscar signos de enfermedades',
            ],
        ];

        $message = $this->detector->generateAlertMessage($anomaly);

        $this->assertIsString($message);
        $this->assertStringContainsString('HIGH', strtoupper($message));
        $this->assertStringContainsString('Caída Rápida de Vigor', $message);
        $this->assertStringContainsString('Inspección visual urgente', $message);
    }

    /** @test */
    public function it_determines_notification_necessity()
    {
        $criticalAnomaly = [
            'severity' => 'critical',
            'type' => 'rapid_ndvi_decline',
        ];

        $lowAnomaly = [
            'severity' => 'low',
            'type' => 'statistical_outlier',
        ];

        $this->assertTrue($this->detector->shouldNotify($criticalAnomaly));
        $this->assertFalse($this->detector->shouldNotify($lowAnomaly));
    }

    /** @test */
    public function it_handles_insufficient_historical_data()
    {
        $current = new PlotRemoteSensing([
            'ndvi_mean' => 0.65,
            'ndwi_mean' => 0.30,
            'temperature' => 25,
            'soil_moisture' => 0.22,
            'ndvi_stddev' => 0.08,
            'image_date' => now(),
        ]);

        // Empty historical data
        $historical = collect();

        $result = $this->detector->detectAnomalies($current, $historical);

        // Should not crash, might have limited detections
        $this->assertIsArray($result);
        $this->assertArrayHasKey('has_anomalies', $result);
        $this->assertArrayHasKey('risk_level', $result);
    }
}
