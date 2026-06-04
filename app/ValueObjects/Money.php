<?php

namespace App\ValueObjects;

class Money
{
    public function __construct(
        public readonly float $amount,
        public readonly string $currency = 'EUR'
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException(__('El monto no puede ser negativo.'));
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
     * Crear desde string
     */
    public static function fromString(string $value, string $currency = 'EUR'): self
    {
        return new self((float) $value, $currency);
    }

    /**
     * Crear EUR
     */
    public static function EUR(float $amount): self
    {
        return new self($amount, 'EUR');
    }

    /**
     * Sumar dos montos
     */
    public function add(Money $other): self
    {
        $this->ensureSameCurrency($other);

        return new self($this->amount + $other->amount, $this->currency);
    }

    /**
     * Restar dos montos
     */
    public function subtract(Money $other): self
    {
        $this->ensureSameCurrency($other);

        return new self($this->amount - $other->amount, $this->currency);
    }

    /**
     * Multiplicar por un factor
     */
    public function multiply(float $factor): self
    {
        return new self($this->amount * $factor, $this->currency);
    }

    /**
     * Dividir por un divisor
     */
    public function divide(float $divisor): self
    {
        if ($divisor === 0.0) {
            throw new \InvalidArgumentException(__('No se puede dividir por cero.'));
        }

        return new self($this->amount / $divisor, $this->currency);
    }

    /**
     * Calcular porcentaje
     */
    public function percentage(float $percentage): self
    {
        return new self($this->amount * ($percentage / 100), $this->currency);
    }

    /**
     * Aplicar impuesto
     */
    public function applyTax(float $taxRate): self
    {
        return $this->add($this->percentage($taxRate));
    }

    /**
     * Verificar si es mayor que
     */
    public function isGreaterThan(Money $other): bool
    {
        $this->ensureSameCurrency($other);

        return $this->amount > $other->amount;
    }

    /**
     * Verificar si es menor que
     */
    public function isLessThan(Money $other): bool
    {
        $this->ensureSameCurrency($other);

        return $this->amount < $other->amount;
    }

    /**
     * Verificar si es igual a
     */
    public function equals(Money $other): bool
    {
        return $this->currency === $other->currency
            && abs($this->amount - $other->amount) < 0.01;
    }

    /**
     * Verificar si es cero
     */
    public function isZero(): bool
    {
        return abs($this->amount) < 0.01;
    }

    /**
     * Formatear para mostrar
     */
    public function format(int $decimals = 2): string
    {
        return number_format($this->amount, $decimals, ',', '.').' '.$this->currency;
    }

    /**
     * Asegurar que las monedas son iguales
     */
    protected function ensureSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException(__('Las monedas deben ser iguales.'));
        }
    }
}
