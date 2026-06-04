<?php

namespace App\Services\Validators;

use App\Models\PlotPlanting;

class PlantingRightsValidator
{
    /**
     * Validate planting authorization for a planting
     */
    public function validate(PlotPlanting $planting): array
    {
        $errors = [];
        $warnings = [];

        // Plantaciones desde 2016 requieren autorización
        $requiresAuthorization = $planting->planting_year && $planting->planting_year >= 2016;

        if ($requiresAuthorization) {
            if (! $planting->planting_authorization) {
                $errors[] = sprintf(
                    'Plantaciones desde 2016 requieren autorización. Año de plantación: %s',
                    $planting->planting_year
                );
            }

            if (! $planting->authorization_date) {
                $warnings[] = 'Falta fecha de autorización';
            }

            if (! $planting->right_type) {
                $warnings[] = 'Falta tipo de derecho (nueva/replantación/conversión)';
            }

            // Si es replantación, debe tener fecha de arranque
            if ($planting->right_type === 'replantacion' && ! $planting->uprooting_date) {
                $warnings[] = 'Las replantaciones deben indicar fecha de arranque';
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
        return $planting->planting_year && $planting->planting_year >= 2016;
    }
}
