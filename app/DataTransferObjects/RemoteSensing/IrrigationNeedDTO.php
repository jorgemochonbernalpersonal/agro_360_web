<?php

namespace App\DataTransferObjects\RemoteSensing;

/**
 * Irrigation Need Calculation Result
 */
final readonly class IrrigationNeedDTO
{
    public function __construct(
        public float $et0,
        public float $kc,
        public float $etc,
        public float $weeklyNeedMm,
        public float $expectedRainMm,
        public float $soilReserveMm,
        public float $irrigationNeedMm,
        public int $litersPerHa,
        public string $recommendationText,
        public string $recommendationColor,
        public string $recommendationBg,
    ) {}

    public function toArray(): array
    {
        return [
            'et0' => round($this->et0, 2),
            'kc' => $this->kc,
            'etc' => round($this->etc, 2),
            'weekly_need_mm' => round($this->weeklyNeedMm, 1),
            'expected_rain_mm' => round($this->expectedRainMm, 1),
            'soil_reserve_mm' => round($this->soilReserveMm, 1),
            'irrigation_need_mm' => round($this->irrigationNeedMm, 1),
            'liters_per_ha' => $this->litersPerHa,
            'recommendation' => [
                'text' => $this->recommendationText,
                'color' => $this->recommendationColor,
                'bg' => $this->recommendationBg,
            ],
        ];
    }
}
