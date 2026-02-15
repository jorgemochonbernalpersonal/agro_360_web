<?php

namespace App\DataTransferObjects\RemoteSensing;

/**
 * Weather Data Transfer Object
 */
final readonly class WeatherDataDTO
{
    public function __construct(
        public float $temperature,
        public float $temperatureMax,
        public float $temperatureMin,
        public float $humidity,
        public float $precipitation,
        public float $windSpeed,
        public int $weatherCode,
        public bool $success = true,
        public bool $mock = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            temperature: $data['temperature'],
            temperatureMax: $data['temperature_max'],
            temperatureMin: $data['temperature_min'],
            humidity: $data['humidity'],
            precipitation: $data['precipitation'],
            windSpeed: $data['wind_speed'],
            weatherCode: $data['weather_code'],
            success: $data['success'] ?? true,
            mock: $data['mock'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'temperature' => $this->temperature,
            'temperature_max' => $this->temperatureMax,
            'temperature_min' => $this->temperatureMin,
            'humidity' => $this->humidity,
            'precipitation' => $this->precipitation,
            'wind_speed' => $this->windSpeed,
            'weather_code' => $this->weatherCode,
            'success' => $this->success,
            'mock' => $this->mock,
        ];
    }
}
