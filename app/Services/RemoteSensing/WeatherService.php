<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Service for weather data via Open-Meteo API (100% FREE)
 * https://open-meteo.com/
 * 
 * Provides: temperature, precipitation, humidity, wind, soil moisture, solar radiation
 */
class WeatherService
{
    private string $baseUrl = 'https://api.open-meteo.com/v1/forecast';
    private bool $useMockData;

    public function __construct()
    {
        $this->useMockData = config('services.open_meteo.mock') ?? true;
    }

    /**
     * Get current weather data for a plot
     */
    public function getCurrentWeather(Plot $plot): array
    {
        if ($this->useMockData) {
            return $this->generateMockWeatherData();
        }

        $coords = $this->getPlotCoordinates($plot);
        
        return Cache::remember("weather_{$plot->id}", 3600, function () use ($coords) {
            return $this->fetchWeatherData($coords['lat'], $coords['lon']);
        });
    }

    /**
     * Get weather forecast for next 7 days
     */
    public function getForecast(Plot $plot, int $days = 7): array
    {
        if ($this->useMockData) {
            return $this->generateMockForecast($days);
        }

        $coords = $this->getPlotCoordinates($plot);
        
        return Cache::remember("forecast_{$plot->id}_{$days}", 3600, function () use ($coords, $days) {
            return $this->fetchForecastData($coords['lat'], $coords['lon'], $days);
        });
    }

    /**
     * Get soil data (moisture, temperature)
     */
    public function getSoilData(Plot $plot): array
    {
        if ($this->useMockData) {
            return $this->generateMockSoilData();
        }

        $coords = $this->getPlotCoordinates($plot);
        
        return Cache::remember("soil_{$plot->id}", 3600, function () use ($coords) {
            return $this->fetchSoilData($coords['lat'], $coords['lon']);
        });
    }

    /**
     * Get solar radiation data
     */
    public function getSolarData(Plot $plot): array
    {
        if ($this->useMockData) {
            return $this->generateMockSolarData();
        }

        $coords = $this->getPlotCoordinates($plot);
        
        return Cache::remember("solar_{$plot->id}", 3600, function () use ($coords) {
            return $this->fetchSolarData($coords['lat'], $coords['lon']);
        });
    }

    /**
     * Fetch current weather from Open-Meteo
     */
    private function fetchWeatherData(float $lat, float $lon): array
    {
        try {
            $response = Http::timeout(30)->get($this->baseUrl, [
                'latitude' => $lat,
                'longitude' => $lon,
                'current' => 'temperature_2m,relative_humidity_2m,precipitation,wind_speed_10m,weather_code',
                'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum',
                'timezone' => 'Europe/Madrid',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Validar estructura de respuesta
                if (!isset($data['current']) || !isset($data['daily'])) {
                    Log::warning('Open-Meteo: Invalid response structure', [
                        'has_current' => isset($data['current']),
                        'has_daily' => isset($data['daily']),
                        'lat' => $lat,
                        'lon' => $lon,
                    ]);
                    return $this->generateMockWeatherData();
                }
                
                $weatherData = [
                    'temperature' => $data['current']['temperature_2m'] ?? null,
                    'humidity' => $data['current']['relative_humidity_2m'] ?? null,
                    'precipitation' => $data['current']['precipitation'] ?? null,
                    'wind_speed' => $data['current']['wind_speed_10m'] ?? null,
                    'weather_code' => $data['current']['weather_code'] ?? null,
                    'temperature_max' => $data['daily']['temperature_2m_max'][0] ?? null,
                    'temperature_min' => $data['daily']['temperature_2m_min'][0] ?? null,
                    'success' => true,
                ];
                
                // Si los datos críticos son null, usar mock
                if (is_null($weatherData['temperature']) && 
                    is_null($weatherData['humidity']) && 
                    is_null($weatherData['wind_speed'])) {
                    Log::warning('Open-Meteo: Empty data returned', [
                        'lat' => $lat,
                        'lon' => $lon,
                        'data' => $data,
                    ]);
                    return $this->generateMockWeatherData();
                }
                
                return $weatherData;
            } else {
                Log::error('Open-Meteo API request failed', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 200),
                    'lat' => $lat,
                    'lon' => $lon,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Open-Meteo API error', [
                'error' => $e->getMessage(),
                'lat' => $lat,
                'lon' => $lon,
            ]);
        }

        return $this->generateMockWeatherData();
    }

    /**
     * Fetch forecast data
     */
    private function fetchForecastData(float $lat, float $lon, int $days): array
    {
        try {
            $response = Http::timeout(30)->get($this->baseUrl, [
                'latitude' => $lat,
                'longitude' => $lon,
                'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum,wind_speed_10m_max,weather_code',
                'timezone' => 'Europe/Madrid',
                'forecast_days' => $days,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Validar estructura
                if (!isset($data['daily']) || !isset($data['daily']['time'])) {
                    Log::warning('Open-Meteo forecast: Invalid response structure', [
                        'has_daily' => isset($data['daily']),
                        'has_time' => isset($data['daily']['time']),
                        'lat' => $lat,
                        'lon' => $lon,
                    ]);
                    return $this->generateMockForecast($days);
                }
                
                $forecast = [];
                
                for ($i = 0; $i < $days; $i++) {
                    $forecast[] = [
                        'date' => $data['daily']['time'][$i] ?? null,
                        'temp_max' => $data['daily']['temperature_2m_max'][$i] ?? null,
                        'temp_min' => $data['daily']['temperature_2m_min'][$i] ?? null,
                        'precipitation' => $data['daily']['precipitation_sum'][$i] ?? null,
                        'wind_speed' => $data['daily']['wind_speed_10m_max'][$i] ?? null,
                        'weather_code' => $data['daily']['weather_code'][$i] ?? null,
                    ];
                }
                
                return ['forecast' => $forecast, 'success' => true];
            } else {
                Log::error('Open-Meteo forecast API failed', [
                    'status' => $response->status(),
                    'lat' => $lat,
                    'lon' => $lon,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Open-Meteo forecast error', [
                'error' => $e->getMessage(),
                'lat' => $lat,
                'lon' => $lon,
            ]);
        }

        return $this->generateMockForecast($days);
    }

    /**
     * Fetch soil data from Open-Meteo
     */
    private function fetchSoilData(float $lat, float $lon): array
    {
        try {
            $response = Http::timeout(30)->get($this->baseUrl, [
                'latitude' => $lat,
                'longitude' => $lon,
                'hourly' => 'soil_moisture_0_to_1cm,soil_temperature_0cm',
                'timezone' => 'Europe/Madrid',
                'forecast_days' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Validar estructura
                if (!isset($data['hourly'])) {
                    Log::warning('Open-Meteo soil: Invalid response structure', [
                        'has_hourly' => isset($data['hourly']),
                        'lat' => $lat,
                        'lon' => $lon,
                    ]);
                    return $this->generateMockSoilData();
                }
                
                $hour = now()->hour;
                
                return [
                    'soil_moisture' => $data['hourly']['soil_moisture_0_to_1cm'][$hour] ?? null,
                    'soil_temperature' => $data['hourly']['soil_temperature_0cm'][$hour] ?? null,
                    'success' => true,
                ];
            } else {
                Log::error('Open-Meteo soil API failed', [
                    'status' => $response->status(),
                    'lat' => $lat,
                    'lon' => $lon,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Open-Meteo soil error', [
                'error' => $e->getMessage(),
                'lat' => $lat,
                'lon' => $lon,
            ]);
        }

        return $this->generateMockSoilData();
    }

    /**
     * Fetch solar radiation data
     */
    private function fetchSolarData(float $lat, float $lon): array
    {
        try {
            $response = Http::timeout(30)->get($this->baseUrl, [
                'latitude' => $lat,
                'longitude' => $lon,
                'daily' => 'shortwave_radiation_sum,et0_fao_evapotranspiration,sunshine_duration',
                'timezone' => 'Europe/Madrid',
                'forecast_days' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'solar_radiation' => $data['daily']['shortwave_radiation_sum'][0] ?? null,
                    'et0' => $data['daily']['et0_fao_evapotranspiration'][0] ?? null,
                    'sunshine_hours' => ($data['daily']['sunshine_duration'][0] ?? 0) / 3600,
                    'success' => true,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Open-Meteo solar error', ['error' => $e->getMessage()]);
        }

        return $this->generateMockSolarData();
    }

    /**
     * Get plot coordinates
     */
    private function getPlotCoordinates(Plot $plot): array
    {
        // Try to get from geometry
        $multipart = $plot->multipartPlotSigpacs()
            ->whereNotNull('plot_geometry_id')
            ->with('plotGeometry')
            ->first();

        if ($multipart && $multipart->plotGeometry) {
            $wkt = $multipart->plotGeometry->getWktCoordinates();
            if (preg_match('/(-?\d+\.?\d*)\s+(-?\d+\.?\d*)/', $wkt, $matches)) {
                return [
                    'lon' => (float) $matches[1],
                    'lat' => (float) $matches[2],
                ];
            }
        }

        // Default: Central Spain (Madrid area for Ribera del Duero)
        return ['lat' => 41.6167, 'lon' => -3.7033];
    }

    /**
     * Generate mock weather data
     */
    private function generateMockWeatherData(): array
    {
        $month = now()->month;
        
        // Seasonal temperatures for Ribera del Duero
        $baseTemp = match (true) {
            $month >= 6 && $month <= 8 => mt_rand(25, 35),
            $month >= 3 && $month <= 5 => mt_rand(12, 22),
            $month >= 9 && $month <= 11 => mt_rand(10, 20),
            default => mt_rand(-2, 12),
        };

        return [
            'temperature' => $baseTemp,
            'temperature_max' => $baseTemp + mt_rand(3, 8),
            'temperature_min' => $baseTemp - mt_rand(5, 12),
            'humidity' => mt_rand(30, 80),
            'precipitation' => mt_rand(0, 100) > 80 ? mt_rand(1, 15) : 0,
            'wind_speed' => mt_rand(5, 30),
            'weather_code' => [0, 1, 2, 3, 61, 63][array_rand([0, 1, 2, 3, 61, 63])],
            'success' => true,
            'mock' => true,
        ];
    }

    /**
     * Generate mock forecast
     */
    private function generateMockForecast(int $days): array
    {
        $forecast = [];
        $baseWeather = $this->generateMockWeatherData();
        
        for ($i = 0; $i < $days; $i++) {
            $variation = mt_rand(-5, 5);
            $forecast[] = [
                'date' => now()->addDays($i)->format('Y-m-d'),
                'temp_max' => $baseWeather['temperature_max'] + $variation,
                'temp_min' => $baseWeather['temperature_min'] + $variation,
                'precipitation' => mt_rand(0, 100) > 75 ? mt_rand(1, 20) : 0,
                'wind_speed' => mt_rand(5, 35),
                'weather_code' => [0, 1, 2, 3, 61][array_rand([0, 1, 2, 3, 61])],
            ];
        }
        
        return ['forecast' => $forecast, 'success' => true, 'mock' => true];
    }

    /**
     * Generate mock soil data
     */
    private function generateMockSoilData(): array
    {
        $month = now()->month;
        
        // Seasonal soil moisture
        $baseMoisture = match (true) {
            $month >= 6 && $month <= 8 => mt_rand(10, 25), // Summer: dry
            $month >= 11 || $month <= 2 => mt_rand(30, 50), // Winter: wet
            default => mt_rand(20, 40), // Spring/Fall: moderate
        };

        return [
            'soil_moisture' => $baseMoisture,
            'soil_temperature' => mt_rand(5, 25),
            'success' => true,
            'mock' => true,
        ];
    }

    /**
     * Generate mock solar data
     */
    private function generateMockSolarData(): array
    {
        $month = now()->month;
        
        // Seasonal radiation
        $baseRadiation = match (true) {
            $month >= 6 && $month <= 8 => mt_rand(25, 32), // Summer: high
            $month >= 3 && $month <= 5 => mt_rand(18, 25),
            $month >= 9 && $month <= 11 => mt_rand(12, 20),
            default => mt_rand(6, 14), // Winter: low
        };

        $sunshineHours = match (true) {
            $month >= 6 && $month <= 8 => mt_rand(10, 14),
            $month >= 3 && $month <= 5 => mt_rand(7, 11),
            $month >= 9 && $month <= 11 => mt_rand(6, 9),
            default => mt_rand(4, 7),
        };

        return [
            'solar_radiation' => $baseRadiation,
            'et0' => round($baseRadiation * 0.15 + mt_rand(0, 100) / 100, 2),
            'sunshine_hours' => $sunshineHours,
            'success' => true,
            'mock' => true,
        ];
    }

    /**
     * Get weather icon from WMO code
     */
    public static function getWeatherIcon(int $code): string
    {
        return match (true) {
            $code === 0 => '☀️',
            $code <= 3 => '⛅',
            $code <= 49 => '🌫️',
            $code <= 59 => '🌧️',
            $code <= 69 => '🌨️',
            $code <= 79 => '❄️',
            $code <= 99 => '⛈️',
            default => '🌤️',
        };
    }

    /**
     * Get weather description from WMO code
     */
    public static function getWeatherDescription(int $code): string
    {
        return match (true) {
            $code === 0 => 'Despejado',
            $code <= 3 => 'Parcialmente nublado',
            $code <= 49 => 'Niebla',
            $code <= 59 => 'Lluvia',
            $code <= 69 => 'Aguanieve',
            $code <= 79 => 'Nieve',
            $code <= 99 => 'Tormenta',
            default => 'Variable',
        };
    }
}
