<?php

namespace App\Services\Validators;

use App\Models\AgriculturalActivity;
use App\Models\Harvest;
use Illuminate\Support\Collection;

/**
 * Validador de plazo de seguridad para cosechas
 * 
 * Verifica que se respete el plazo de seguridad de productos fitosanitarios
 * antes de permitir la cosecha (Real Decreto 1311/2012)
 */
class WithdrawalPeriodValidator
{
    /**
     * Validar plazo de seguridad para una cosecha
     * 
     * @param Harvest $harvest
     * @return array ['is_valid' => bool, 'errors' => array, 'warnings' => array, 'last_treatment' => array|null]
     */
    public function validateHarvest(Harvest $harvest): array
    {
        $errors = [];
        $warnings = [];
        $lastTreatmentInfo = null;

        $activity = $harvest->activity;
        if (!$activity || !$activity->plot) {
            $errors[] = 'Cosecha sin parcela asociada';
            return [
                'is_valid' => false,
                'errors' => $errors,
                'warnings' => $warnings,
                'last_treatment' => null,
            ];
        }

        $harvestDate = $activity->activity_date;
        $plot = $activity->plot;

        // Buscar el último tratamiento fitosanitario en la parcela
        $lastTreatment = AgriculturalActivity::where('plot_id', $plot->id)
            ->where('activity_type', 'phytosanitary')
            ->where('activity_date', '<=', $harvestDate)
            ->orderBy('activity_date', 'desc')
            ->with(['phytosanitaryTreatment.product'])
            ->first();

        if (!$lastTreatment) {
            // No hay tratamientos previos, OK
            return [
                'is_valid' => true,
                'errors' => [],
                'warnings' => [],
                'last_treatment' => null,
            ];
        }

        $treatment = $lastTreatment->phytosanitaryTreatment;
        if (!$treatment || !$treatment->product) {
            $warnings[] = 'Último tratamiento sin producto definido';
            return [
                'is_valid' => true,
                'errors' => [],
                'warnings' => $warnings,
                'last_treatment' => null,
            ];
        }

        $product = $treatment->product;
        $withdrawalPeriod = $product->withdrawal_period_days;
        $daysSinceTreatment = $lastTreatment->activity_date->diffInDays($harvestDate);

        $lastTreatmentInfo = [
            'treatment_date' => $lastTreatment->activity_date->format('d/m/Y'),
            'product_name' => $product->name,
            'withdrawal_period' => $withdrawalPeriod,
            'days_since_treatment' => $daysSinceTreatment,
            'days_remaining' => max(0, $withdrawalPeriod - $daysSinceTreatment),
        ];

        // Validar plazo de seguridad
        if ($daysSinceTreatment < $withdrawalPeriod) {
            $daysRemaining = $withdrawalPeriod - $daysSinceTreatment;
            $errors[] = sprintf(
                'No se puede cosechar: faltan %d día(s) para cumplir el plazo de seguridad. ' .
                'Último tratamiento: %s (%s) el %s. Plazo de seguridad: %d días.',
                $daysRemaining,
                $product->name,
                $product->registration_number,
                $lastTreatment->activity_date->format('d/m/Y'),
                $withdrawalPeriod
            );
        }

        // Advertencia si está muy cerca del límite (menos de 3 días de margen)
        if ($daysSinceTreatment >= $withdrawalPeriod && $daysSinceTreatment < ($withdrawalPeriod + 3)) {
            $warnings[] = sprintf(
                'Cosecha muy cercana al plazo de seguridad. Solo %d día(s) de margen.',
                $daysSinceTreatment - $withdrawalPeriod
            );
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'last_treatment' => $lastTreatmentInfo,
        ];
    }

    /**
     * Validar múltiples cosechas
     * 
     * @param Collection $harvests
     * @return array
     */
    public function validateHarvests(Collection $harvests): array
    {
        $results = [];
        $totalErrors = 0;
        $totalWarnings = 0;

        foreach ($harvests as $harvest) {
            $validation = $this->validateHarvest($harvest);
            
            if (!$validation['is_valid']) {
                $totalErrors++;
            }
            
            if (!empty($validation['warnings'])) {
                $totalWarnings++;
            }

            $results[] = [
                'harvest_id' => $harvest->id,
                'harvest_date' => $harvest->activity->activity_date->format('d/m/Y'),
                'validation' => $validation,
            ];
        }

        return [
            'total_harvests' => $harvests->count(),
            'total_errors' => $totalErrors,
            'total_warnings' => $totalWarnings,
            'results' => $results,
        ];
    }

    /**
     * Calcular fecha mínima de cosecha para una parcela
     * 
     * @param int $plotId
     * @return array ['min_harvest_date' => Carbon|null, 'last_treatment' => array|null]
     */
    public function getMinimumHarvestDate(int $plotId): array
    {
        $lastTreatment = AgriculturalActivity::where('plot_id', $plotId)
            ->where('activity_type', 'phytosanitary')
            ->orderBy('activity_date', 'desc')
            ->with(['phytosanitaryTreatment.product'])
            ->first();

        if (!$lastTreatment || !$lastTreatment->phytosanitaryTreatment || !$lastTreatment->phytosanitaryTreatment->product) {
            return [
                'min_harvest_date' => null,
                'last_treatment' => null,
            ];
        }

        $product = $lastTreatment->phytosanitaryTreatment->product;
        $withdrawalPeriod = $product->withdrawal_period_days;
        $minHarvestDate = $lastTreatment->activity_date->copy()->addDays($withdrawalPeriod);

        return [
            'min_harvest_date' => $minHarvestDate,
            'last_treatment' => [
                'date' => $lastTreatment->activity_date,
                'product' => $product->name,
                'withdrawal_period' => $withdrawalPeriod,
            ],
        ];
    }
}
