<?php

namespace App\ValueObjects;

class Coordinates
{
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude
    ) {
        if ($latitude < -90 || $latitude > 90) {
            throw new \InvalidArgumentException('La latitud debe estar entre -90 y 90.');
        }

        if ($longitude < -180 || $longitude > 180) {
            throw new \InvalidArgumentException('La longitud debe estar entre -180 y 180.');
        }
    }

    /**
     * Crear desde array [lat, lng]
     */
    public static function fromArray(array $coords): self
    {
        return new self($coords[0], $coords[1]);
    }

    /**
     * Crear desde string "lat,lng"
     */
    public static function fromString(string $coords): self
    {
        [$lat, $lng] = explode(',', $coords);
        return new self((float) trim($lat), (float) trim($lng));
    }

    /**
     * Calcular distancia a otras coordenadas en km
     * Usa fórmula de Haversine
     */
    public function distanceTo(Coordinates $other): float
    {
        $earthRadius = 6371; // km

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($other->latitude);
        $lonTo = deg2rad($other->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Formatear para Google Maps
     */
    public function toGoogleMapsUrl(): string
    {
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    /**
     * Convertir a array
     */
    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    /**
     * Formatear como string
     */
    public function format(int $decimals = 6): string
    {
        return number_format($this->latitude, $decimals) . ', ' . number_format($this->longitude, $decimals);
    }

    /**
     * Convertir a string
     */
    public function __toString(): string
    {
        return $this->format();
    }
}
