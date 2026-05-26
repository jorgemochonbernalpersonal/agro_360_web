<?php

namespace App\ValueObjects;

class Area
{
    public function __construct(
        public readonly float $hectares
    ) {
        if ($hectares < 0) {
            throw new \InvalidArgumentException(__('La superficie no puede ser negativa.'));
        }
    }

    /**
     * Crear desde hectáreas
     */
    public static function fromHectares(float $hectares): self
    {
        return new self($hectares);
    }

    /**
     * Crear desde metros cuadrados
     */
    public static function fromSquareMeters(float $squareMeters): self
    {
        return new self($squareMeters / 10000);
    }

    /**
     * Crear desde acres
     */
    public static function fromAcres(float $acres): self
    {
        return new self($acres * 0.404686);
    }

    /**
     * Obtener en hectáreas
     */
    public function toHectares(): float
    {
        return $this->hectares;
    }

    /**
     * Obtener en metros cuadrados
     */
    public function toSquareMeters(): float
    {
        return $this->hectares * 10000;
    }

    /**
     * Obtener en acres
     */
    public function toAcres(): float
    {
        return $this->hectares / 0.404686;
    }

    /**
     * Sumar áreas
     */
    public function add(Area $other): self
    {
        return new self($this->hectares + $other->hectares);
    }

    /**
     * Restar áreas
     */
    public function subtract(Area $other): self
    {
        return new self(max(0, $this->hectares - $other->hectares));
    }

    /**
     * Multiplicar por un factor
     */
    public function multiply(float $factor): self
    {
        return new self($this->hectares * $factor);
    }

    /**
     * Verificar si es mayor que
     */
    public function isGreaterThan(Area $other): bool
    {
        return $this->hectares > $other->hectares;
    }

    /**
     * Verificar si es cero
     */
    public function isZero(): bool
    {
        return abs($this->hectares) < 0.001;
    }

    /**
     * Formatear para mostrar
     */
    public function format(int $decimals = 2): string
    {
        return number_format($this->hectares, $decimals, ',', '.') . ' ha';
    }

    /**
     * Formatear en metros cuadrados
     */
    public function formatSquareMeters(int $decimals = 0): string
    {
        return number_format($this->toSquareMeters(), $decimals, ',', '.') . ' m²';
    }

    /**
     * Convertir a string
     */
    public function __toString(): string
    {
        return $this->format();
    }
}
