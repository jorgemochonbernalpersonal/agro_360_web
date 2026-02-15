<?php

namespace App\Macros;

use Illuminate\Support\Collection;

class CollectionMacros
{
    /**
     * Registrar todos los macros personalizados
     */
    public function register(): void
    {
        $this->registerCalculateTotalArea();
        $this->registerCalculateTotalAmount();
        $this->registerGroupByMonth();
        $this->registerToSelectOptions();
        $this->registerPluckWithFallback();
        $this->registerSumWithTax();
        $this->registerWhereActive();
        $this->registerFilterByDateRange();
    }

    /**
     * Calcular área total de parcelas
     */
    protected function registerCalculateTotalArea(): void
    {
        Collection::macro('calculateTotalArea', function (string $areaField = 'area') {
            return $this->sum($areaField);
        });
    }

    /**
     * Calcular monto total
     */
    protected function registerCalculateTotalAmount(): void
    {
        Collection::macro('calculateTotalAmount', function (string $field = 'amount') {
            return $this->sum(fn(object $item) => (float) ($item->$field ?? 0));
        });
    }

    /**
     * Agrupar por mes
     */
    protected function registerGroupByMonth(): void
    {
        Collection::macro('groupByMonth', function (string $dateField = 'created_at') {
            return $this->groupBy(function (object $item) use ($dateField) {
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
    protected function registerToSelectOptions(): void
    {
        Collection::macro('toSelectOptions', function (string $valueField = 'id', string $labelField = 'name') {
            return $this->map(fn(object $item) => [
                'value' => $item->$valueField,
                'label' => $item->$labelField,
            ]);
        });
    }

    /**
     * Pluck con valor por defecto
     */
    protected function registerPluckWithFallback(): void
    {
        Collection::macro('pluckWithFallback', function (string $field, $fallback = null) {
            return $this->map(fn(object $item) => $item->$field ?? $fallback);
        });
    }

    /**
     * Sumar con impuestos
     */
    protected function registerSumWithTax(): void
    {
        Collection::macro('sumWithTax', function (string $amountField = 'amount', string $taxField = 'tax_rate') {
            return $this->sum(function (object $item) use ($amountField, $taxField) {
                $amount = (float) ($item->$amountField ?? 0);
                $taxRate = (float) ($item->$taxField ?? 0);
                return $amount * (1 + $taxRate / 100);
            });
        });
    }

    /**
     * Filtrar solo activos
     */
    protected function registerWhereActive(): void
    {
        Collection::macro('whereActive', function (string $field = 'active') {
            return $this->filter(fn(object $item) => $item->$field == true);
        });
    }

    /**
     * Filtrar por rango de fechas
     */
    protected function registerFilterByDateRange(): void
    {
        Collection::macro('filterByDateRange', function (
            string $dateField,
            $startDate,
            $endDate
        ) {
            $start = $startDate instanceof \Carbon\Carbon ? $startDate : \Carbon\Carbon::parse($startDate);
            $end = $endDate instanceof \Carbon\Carbon ? $endDate : \Carbon\Carbon::parse($endDate);

            return $this->filter(function (object $item) use ($dateField, $start, $end) {
                $itemDate = $item->$dateField instanceof \Carbon\Carbon 
                    ? $item->$dateField 
                    : \Carbon\Carbon::parse($item->$dateField);

                return $itemDate->between($start, $end);
            });
        });
    }
}
