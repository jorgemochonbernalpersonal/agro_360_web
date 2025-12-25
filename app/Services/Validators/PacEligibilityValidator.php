<?php

namespace App\Services\Validators;

use App\Models\Plot;

class PacEligibilityValidator
{
    /**
     * Validate PAC eligibility for a plot
     */
    public function validate(Plot $plot): array
    {
        $totalArea = $plot->area ?? 0;
        $eligibleArea = $plot->pac_eligible_area ?? $totalArea;
        $nonEligibleArea = $plot->non_eligible_area ?? 0;
        
        $errors = [];
        $warnings = [];
        
        // Validar coherencia de superficies
        $sumAreas = $eligibleArea + $nonEligibleArea;
        $tolerance = 0.05; // 5% de tolerancia
        
        if ($sumAreas > $totalArea * (1 + $tolerance)) {
            $errors[] = sprintf(
                'Superficie admisible (%.3f ha) + no admisible (%.3f ha) = %.3f ha excede superficie total (%.3f ha)',
                $eligibleArea,
                $nonEligibleArea,
                $sumAreas,
                $totalArea
            );
        }
        
        // Validar que superficie admisible no sea mayor que total
        if ($eligibleArea > $totalArea) {
            $errors[] = sprintf(
                'Superficie admisible (%.3f ha) no puede ser mayor que superficie total (%.3f ha)',
                $eligibleArea,
                $totalArea
            );
        }
        
        // Calcular coeficiente
        $coefficient = $totalArea > 0 ? $eligibleArea / $totalArea : 0;
        
        // Advertencia si coeficiente es bajo
        if ($coefficient < 0.85 && $coefficient > 0) {
            $warnings[] = sprintf(
                'Coeficiente de admisibilidad bajo (%.2f%%). Revisar superficie no admisible.',
                $coefficient * 100
            );
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'coefficient' => round($coefficient, 4),
            'eligible_area' => $eligibleArea,
            'non_eligible_area' => $nonEligibleArea,
        ];
    }
    
    /**
     * Calculate and update eligibility coefficient
     */
    public function updateCoefficient(Plot $plot): void
    {
        $totalArea = $plot->area ?? 0;
        $eligibleArea = $plot->pac_eligible_area ?? $totalArea;
        
        $coefficient = $totalArea > 0 ? $eligibleArea / $totalArea : 1.0;
        $plot->eligibility_coefficient = round($coefficient, 4);
    }
}
