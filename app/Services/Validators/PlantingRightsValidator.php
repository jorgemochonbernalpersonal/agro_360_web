<?php

namespace App\Services\Validators;

use App\Models\PlotPlanting;
use Carbon\Carbon;

class PlantingRightsValidator
{
    /**
     * Validate planting authorization for a planting
     */
    public function validate(PlotPlanting $planting): array
    {
        $errors = [];
        $warnings = [];
        
        $plantingDate = $planting->planting_date;
        
        // Plantaciones después del 1 de enero de 2016 requieren autorización
        $requiresAuthorization = $plantingDate && $plantingDate >= Carbon::parse('2016-01-01');
        
        if ($requiresAuthorization) {
            if (!$planting->planting_authorization) {
                $errors[] = sprintf(
                    'Plantaciones posteriores a 2016 requieren autorización. Fecha de plantación: %s',
                    $plantingDate->format('d/m/Y')
                );
            }
            
            if (!$planting->authorization_date) {
                $warnings[] = 'Falta fecha de autorización';
            }
            
            if (!$planting->right_type) {
                $warnings[] = 'Falta tipo de derecho (nueva/replantación/conversión)';
            }
            
            // Si es replantación, debe tener fecha de arranque
            if ($planting->right_type === 'replantacion' && !$planting->uprooting_date) {
                $warnings[] = 'Las replantaciones deben indicar fecha de arranque';
            }
        }
        
        // Validar coherencia de fechas
        if ($planting->authorization_date && $plantingDate) {
            if ($planting->authorization_date > $plantingDate) {
                $errors[] = 'La fecha de autorización no puede ser posterior a la fecha de plantación';
            }
        }
        
        if ($planting->uprooting_date && $plantingDate) {
            if ($planting->uprooting_date >= $plantingDate) {
                $errors[] = 'La fecha de arranque debe ser anterior a la fecha de plantación';
            }
        }
        
        return [
            'valid' => empty($errors),
            'requires_authorization' => $requiresAuthorization,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }
    
    /**
     * Check if planting needs authorization
     */
    public function needsAuthorization(PlotPlanting $planting): bool
    {
        $plantingDate = $planting->planting_date;
        return $plantingDate && $plantingDate >= Carbon::parse('2016-01-01');
    }
}
