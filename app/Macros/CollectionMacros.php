<?php

namespace App\Macros;

use Illuminate\Support\Collection;

class CollectionMacros
{
    /**
     * Registrar todos los macros personalizados
     */
    public static function register(): void
    {
        self::registerCalculateTotalArea();
        self::registerCalculateTotalAmount();
        self::registerGroupByMonth();
        self::registerToSelectOptions();
        self::registerPluckWithFallback();
        self::registerSumWithTax();
        self::registerWhereActive();
        self::registerFilterByDateRange();
    }

    /**
     * Calcular área total de parcelas
     */
    protected static function registerCalculateTotalArea(): void
    {
        Collection::macro('calculateTotalArea', function (string $areaField = 'area') {
            return $this->sum($areaField);
        });
    }

    /**
     * Calcular monto total
     */
    protected static function registerCalculateTotalAmount(): void
    {
        Collection::macro('calculateTotalAmount', function (string $field = 'amount') {
            return $this->sum(fn($item) => (float) ($item->$field ?? 0));
        });
    }

    /**
     * Agrupar por mes
     */
    protected static function registerGroupByMonth(): void
    {
        Collection::macro('groupByMonth', function (string $dateField = 'created_at') {
            return $this->groupBy(function ($item) use ($dateField) {
                $date = $item->$dateField;
                return $date instanceof \Carbon\Carbon 
                    ? $date->format('Y-m')
                    : date('Y-m', strtotime($date));
            });
        });
    }

    /**
     * Convertir a opciones de select
     */
    protected static function registerToSelectOptions(): void
    {
        Collection::macro('toSelectOptions', function (string $valueField = 'id', string $labelField = 'name') {
            return $this->map(fn($item) => [
                'value' => $item->$valueField,
                'label' => $item->$labelField,
            ]);
        });
    }

    /**
     * Pluck con valor por defecto
     */
    protected static function registerPluckWithFallback(): void
    {
        Collection::macro('pluckWithFallback', function (string $field, $fallback = null) {
            return $this->map(fn($item) => $item->$field ?? $fallback);
        });
    }

    /**
     * Sumar con impuestos
     */
    protected static function registerSumWithTax(): void
    {
        Collection::macro('sumWithTax', function (string $amountField = 'amount', string $taxField = 'tax_rate') {
            return $this->sum(function ($item) use ($amountField, $taxField) {
                $amount = (float) ($item->$amountField ?? 0);
                $taxRate = (float) ($item->$taxField ?? 0);
                return $amount * (1 + $taxRate / 100);
            });
        });
    }

    /**
     * Filtrar solo activos
     */
    protected static function registerWhereActive(): void
    {
        Collection::macro('whereActive', function (string $field = 'active') {
            return $this->filter(fn($item) => $item->$field == true);
        });
    }

    /**
     * Filtrar por rango de fechas
     */
    protected static function registerFilterByDateRange(): void
    {
        Collection::macro('filterByDateRange', function (
            string $dateField,
            $startDate,
            $endDate
        ) {
            $start = $startDate instanceof \Carbon\Carbon ? $startDate : \Carbon\Carbon::parse($startDate);
            $end = $endDate instanceof \Carbon\Carbon ? $endDate : \Carbon\Carbon::parse($endDate);

            return $this->filter(function ($item) use ($dateField, $start, $end) {
                $itemDate = $item->$dateField instanceof \Carbon\Carbon 
                    ? $item->$dateField 
                    : \Carbon\Carbon::parse($item->$dateField);

                return $itemDate->between($start, $end);
            });
        });
    }
}
