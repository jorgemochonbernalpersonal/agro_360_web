<?php

namespace App\DataTransferObjects\RemoteSensing;

use Carbon\Carbon;

/**
 * Remote Sensing Data Transfer Object
 * Provides type-safe data structure for remote sensing data
 */
final readonly class RemoteSensingDataDTO
{
    public function __construct(
        public float $ndviMean,
        public float $ndviMin,
        public float $ndviMax,
        public ?float $ndwiMean = null,
        public ?float $ndwiMin = null,
        public ?float $ndwiMax = null,
        public ?float $eviMean = null,
        public int $cloudCoverage = 0,
        public Carbon $imageDate,
        public string $imageSource,
    ) {}

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ndviMean: $data['ndvi_mean'],
            ndviMin: $data['ndvi_min'],
            ndviMax: $data['ndvi_max'],
            ndwiMean: $data['ndwi_mean'] ?? null,
            ndwiMin: $data['ndwi_min'] ?? null,
            ndwiMax: $data['ndwi_max'] ?? null,
            eviMean: $data['evi_mean'] ?? null,
            cloudCoverage: $data['cloud_coverage'] ?? 0,
            imageDate: $data['image_date'] instanceof Carbon 
                ? $data['image_date'] 
                : Carbon::parse($data['image_date']),
            imageSource: $data['image_source'],
        );
    }

    /**
     * Convert to array for storage
     */
    public function toArray(): array
    {
        return [
            'ndvi_mean' => $this->ndviMean,
            'ndvi_min' => $this->ndviMin,
            'ndvi_max' => $this->ndviMax,
            'ndwi_mean' => $this->ndwiMean,
            'ndwi_min' => $this->ndwiMin,
            'ndwi_max' => $this->ndwiMax,
            'evi_mean' => $this->eviMean,
            'cloud_coverage' => $this->cloudCoverage,
            'image_date' => $this->imageDate,
            'image_source' => $this->imageSource,
        ];
    }
}
