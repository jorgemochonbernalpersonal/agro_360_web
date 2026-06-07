<?php

namespace App\ValueObjects;

use Carbon\Carbon;

class DateRange
{
    public function __construct(
        public readonly Carbon $start,
        public readonly Carbon $end
    ) {
        if ($this->start->isAfter($this->end)) {
            throw new \InvalidArgumentException('La fecha de inicio debe ser anterior a la fecha de fin.');
        }
    }

    /**
     * Convertir a string
     */
    public function __toString(): string
    {
        return $this->format();
    }

    /**
     * Crear desde strings
     */
    public static function fromStrings(string $start, string $end): self
    {
        return new self(
            Carbon::parse($start),
            Carbon::parse($end)
        );
    }

    /**
     * Crear para el mes actual
     */
    public static function currentMonth(): self
    {
        return new self(
            now()->startOfMonth(),
            now()->endOfMonth()
        );
    }

    /**
     * Crear para el año actual
     */
    public static function currentYear(): self
    {
        return new self(
            now()->startOfYear(),
            now()->endOfYear()
        );
    }

    /**
     * Crear para una campaña agrícola
     */
    public static function forCampaign(int $year): self
    {
        return new self(
            Carbon::create($year, 1, 1),
            Carbon::create($year, 12, 31)
        );
    }

    /**
     * Crear para últimos N días
     */
    public static function lastDays(int $days): self
    {
        return new self(
            now()->subDays($days)->startOfDay(),
            now()->endOfDay()
        );
    }

    /**
     * Obtener duración en días
     */
    public function durationInDays(): int
    {
        return $this->start->diffInDays($this->end);
    }

    /**
     * Verificar si una fecha está dentro del rango
     */
    public function contains(Carbon $date): bool
    {
        return $date->between($this->start, $this->end);
    }

    /**
     * Verificar si se solapa con otro rango
     */
    public function overlaps(DateRange $other): bool
    {
        return $this->start->isBefore($other->end)
            && $this->end->isAfter($other->start);
    }

    /**
     * Formatear para mostrar
     */
    public function format(string $format = 'd/m/Y'): string
    {
        return $this->start->format($format).' - '.$this->end->format($format);
    }

    /**
     * Convertir a array
     */
    public function toArray(): array
    {
        return [
            'start' => $this->start->toDateString(),
            'end' => $this->end->toDateString(),
        ];
    }
}
