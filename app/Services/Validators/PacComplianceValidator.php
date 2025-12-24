<?php

namespace App\Services\Validators;

use App\Models\AgriculturalActivity;
use Illuminate\Support\Collection;

/**
 * Validador de cumplimiento de la Política Agraria Común (PAC)
 * 
 * Valida que las actividades agrícolas cumplan con los requisitos
 * de la PAC española, incluyendo la presencia de códigos SIGPAC,
 * superficies declaradas, y otros datos obligatorios.
 */
class PacComplianceValidator
{
    /**
     * Validar cumplimiento PAC de un conjunto de actividades
     * 
     * @param Collection $activities Colección de AgriculturalActivity
     * @return array ['is_compliant' => bool, 'errors' => array, 'warnings' => array, 'stats' => array]
     */
    public function validateActivities(Collection $activities): array
    {
        $errors = [];
        $warnings = [];
        $stats = [
            'total_activities' => $activities->count(),
            'with_sigpac' => 0,
            'without_sigpac' => 0,
            'with_valid_sigpac' => 0,
            'missing_plot' => 0,
        ];

        foreach ($activities as $activity) {
            $activityErrors = $this->validateActivity($activity);
            
            if (!empty($activityErrors['errors'])) {
                $errors[] = [
                    'activity_id' => $activity->id,
                    'activity_date' => $activity->activity_date->format('Y-m-d'),
                    'activity_type' => $activity->activity_type,
                    'errors' => $activityErrors['errors'],
                ];
            }

            if (!empty($activityErrors['warnings'])) {
                $warnings[] = [
                    'activity_id' => $activity->id,
                    'activity_date' => $activity->activity_date->format('Y-m-d'),
                    'activity_type' => $activity->activity_type,
                    'warnings' => $activityErrors['warnings'],
                ];
            }

            // Actualizar estadísticas
            if ($activityErrors['has_plot']) {
                if ($activityErrors['has_sigpac']) {
                    $stats['with_sigpac']++;
                    if ($activityErrors['sigpac_valid']) {
                        $stats['with_valid_sigpac']++;
                    }
                } else {
                    $stats['without_sigpac']++;
                }
            } else {
                $stats['missing_plot']++;
            }
        }

        return [
            'is_compliant' => empty($errors),
            'has_warnings' => !empty($warnings),
            'errors' => $errors,
            'warnings' => $warnings,
            'stats' => $stats,
        ];
    }

    /**
     * Validar una actividad individual
     * 
     * @param AgriculturalActivity $activity
     * @return array
     */
    protected function validateActivity(AgriculturalActivity $activity): array
    {
        $errors = [];
        $warnings = [];
        $has_plot = false;
        $has_sigpac = false;
        $sigpac_valid = false;

        // 1. Validar que tenga parcela asignada
        if (!$activity->plot_id || !$activity->plot) {
            $errors[] = 'Actividad sin parcela asignada';
        } else {
            $has_plot = true;
            $plot = $activity->plot;

            // 2. Validar que la parcela tenga código SIGPAC
            if ($plot->sigpacCodes->isEmpty()) {
                $errors[] = 'Parcela sin código SIGPAC asociado';
            } else {
                $has_sigpac = true;
                $sigpacCode = $plot->sigpacCodes->first();

                // 3. Validar formato del código SIGPAC
                if (strlen($sigpacCode->code) !== 19) {
                    $warnings[] = 'Código SIGPAC no tiene el formato correcto (19 dígitos)';
                } else {
                    $sigpac_valid = true;
                }

                // 4. Validar superficie (solo para tratamientos fitosanitarios)
                if ($activity->activity_type === 'phytosanitary' && $activity->phytosanitaryTreatment) {
                    $areaTreated = $activity->phytosanitaryTreatment->area_treated;
                    $plotArea = $plot->area;

                    if ($areaTreated && $plotArea && $areaTreated > $plotArea) {
                        $warnings[] = sprintf(
                            'Área tratada (%.2f ha) excede superficie de parcela (%.2f ha)',
                            $areaTreated,
                            $plotArea
                        );
                    }
                }

                // 5. Validar que tenga uso SIGPAC definido
                if ($plot->sigpacUses->isEmpty()) {
                    $warnings[] = 'Parcela sin uso SIGPAC definido';
                }
            }

            // 6. Validar superficie total de la parcela
            if (!$plot->area || $plot->area <= 0) {
                $warnings[] = 'Parcela sin superficie total definida';
            }
        }


        
        // 8. Validar Estadio Fenológico (Recomendado para todas las actividades)
        if (!$activity->phenological_stage) {
            $warnings[] = 'Falta estadio fenológico (recomendado para trazabilidad)';
        }

        // 7. Validaciones específicas por tipo de actividad
        if ($activity->activity_type === 'phytosanitary') {
            $phytoErrors = $this->validatePhytosanitaryTreatment($activity);
            $errors = array_merge($errors, $phytoErrors['errors']);
            $warnings = array_merge($warnings, $phytoErrors['warnings']);
        } elseif ($activity->activity_type === 'irrigation') {
            $irrigationErrors = $this->validateIrrigation($activity);
            $errors = array_merge($errors, $irrigationErrors['errors']);
            $warnings = array_merge($warnings, $irrigationErrors['warnings']);
        } elseif ($activity->activity_type === 'fertilization') {
            $fertilizationErrors = $this->validateFertilization($activity);
            $errors = array_merge($errors, $fertilizationErrors['errors']);
            $warnings = array_merge($warnings, $fertilizationErrors['warnings']);
        } elseif ($activity->activity_type === 'harvest') {
            $harvestErrors = $this->validateHarvest($activity);
            $errors = array_merge($errors, $harvestErrors['errors']);
            $warnings = array_merge($warnings, $harvestErrors['warnings']);
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'has_plot' => $has_plot,
            'has_sigpac' => $has_sigpac,
            'sigpac_valid' => $sigpac_valid,
        ];
    }

    /**
     * Validar tratamiento fitosanitario específicamente
     * 
     * @param AgriculturalActivity $activity
     * @return array
     */
    protected function validatePhytosanitaryTreatment(AgriculturalActivity $activity): array
    {
        $errors = [];
        $warnings = [];

        if (!$activity->phytosanitaryTreatment) {
            $errors[] = 'Tratamiento fitosanitario sin datos específicos';
            return ['errors' => $errors, 'warnings' => $warnings];
        }

        $treatment = $activity->phytosanitaryTreatment;

        // Validar producto
        if (!$treatment->product_id || !$treatment->product) {
            $errors[] = 'Tratamiento sin producto fitosanitario definido';
        } else {
            // Validar número de registro del producto
            if (!$treatment->product->registration_number) {
                $warnings[] = 'Producto sin número de registro oficial';
            }

            // Validar plazo de seguridad
            if (!$treatment->product->withdrawal_period_days) {
                $warnings[] = 'Producto sin plazo de seguridad definido';
            }
        }

        // Validar dosis
        if (!$treatment->dose_per_hectare) {
            $warnings[] = 'Tratamiento sin dosis por hectárea especificada';
        }

        // Validar área tratada
        if (!$treatment->area_treated) {
            $warnings[] = 'Tratamiento sin área tratada especificada';
        }

        // NUEVAS VALIDACIONES PAC OBLIGATORIAS
        
        // 1. Justificación del tratamiento (obligatorio)
        if (!$treatment->treatment_justification || trim($treatment->treatment_justification) === '') {
            $errors[] = 'Falta justificación del tratamiento (plaga/enfermedad detectada) - Campo PAC obligatorio';
        }

        // 2. Número ROPO del aplicador (recomendado)
        if (!$treatment->applicator_ropo_number || trim($treatment->applicator_ropo_number) === '') {
            $warnings[] = 'Falta número ROPO del aplicador - Campo PAC recomendado';
        }

        // 3. Plazo de reentrada (obligatorio)
        if (!$treatment->reentry_period_days) {
            $errors[] = 'Falta plazo de reentrada (días sin acceso a parcela) - Campo PAC obligatorio';
        }

        // 4. Volumen de caldo (obligatorio)
        if (!$treatment->spray_volume || $treatment->spray_volume <= 0) {
            $errors[] = 'Falta volumen de caldo aplicado - Campo PAC obligatorio';
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validar actividad de riego (PAC condicionalidad reforzada)
     * 
     * @param AgriculturalActivity $activity
     * @return array
     */
    protected function validateIrrigation(AgriculturalActivity $activity): array
    {
        $errors = [];
        $warnings = [];

        if (!$activity->irrigation) {
            $errors[] = 'Actividad de riego sin datos específicos';
            return ['errors' => $errors, 'warnings' => $warnings];
        }

        $irrigation = $activity->irrigation;

        // 1. Origen del agua (obligatorio)
        if (!$irrigation->water_source) {
            $errors[] = 'Falta origen del agua (pozo, río, etc.) - Campo PAC obligatorio';
        }

        // 2. Número de concesión (obligatorio)
        if (!$irrigation->water_concession) {
            $errors[] = 'Falta número de concesión de agua - Campo PAC obligatorio';
        }

        // 3. Caudal (obligatorio)
        if (!$irrigation->flow_rate || $irrigation->flow_rate <= 0) {
            $errors[] = 'Falta caudal de riego (L/h) - Campo PAC obligatorio';
        }
        
        // 4. Volumen total (recomendado/obligatorio según CCAA)
        if (!$irrigation->water_volume || $irrigation->water_volume <= 0) {
            $warnings[] = 'Falta volumen total de agua aplicada - Campo PAC importante';
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }
    
    /**
     * Validar actividad de fertilización (PAC Nutrición)
     */
    protected function validateFertilization(AgriculturalActivity $activity): array
    {
        $errors = [];
        $warnings = [];

        if (!$activity->fertilization) {
            $errors[] = 'Actividad de fertilización sin datos específicos';
            return ['errors' => $errors, 'warnings' => $warnings];
        }

        $fert = $activity->fertilization;

        // 1. Unidades Fertilizantes (Altamente recomendado/obligatorio en Zonas Vulnerables)
        if ($fert->nitrogen_uf === null && $fert->phosphorus_uf === null && $fert->potassium_uf === null) {
            $warnings[] = 'Faltan Unidades Fertilizantes (UF) de N/P/K - Dato esencial para balance de nutrientes';
        }

        // 2. Si es orgánico, validar detalles de estiércol
        // Heurística simple: si el tipo menciona orgánico/estiércol/purín
        $type = strtolower($fert->fertilizer_type ?? '');
        $isOrganic = str_contains($type, 'organico') || str_contains($type, 'orgánico') || 
                     str_contains($type, 'estiercol') || str_contains($type, 'estiércol') || 
                     str_contains($type, 'purin') || str_contains($type, 'purín');

        if ($isOrganic) {
            if (!$fert->manure_type) {
                $errors[] = 'Fertilizante orgánico: Falta especificar el tipo de estiércol - Obligatorio PAC';
            }
            if (!$fert->burial_date) {
                $errors[] = 'Fertilizante orgánico: Falta fecha de enterrado - Importante para reducción de emisiones';
            }
            if (!$fert->emission_reduction_method) {
                $errors[] = 'Fertilizante orgánico: Falta método de reducción de emisiones';
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validar actividad de cosecha (Trazabilidad)
     */
    protected function validateHarvest(AgriculturalActivity $activity): array
    {
        $errors = [];
        $warnings = [];

        if (!$activity->harvest) {
            $errors[] = 'Actividad de cosecha sin datos específicos';
            return ['errors' => $errors, 'warnings' => $warnings];
        }

        $harvest = $activity->harvest;

        // 1. Documento de Transporte (Obligatorio para movimiento de uva)
        if (!$harvest->transport_document_number) {
            // Si el destino NO es autoconsumo, es obligatorio/muy recomendado
            if ($harvest->destination_type !== 'self_consumption') {
                $warnings[] = 'Falta Documento de Transporte/Guía - Obligatorio para trazabilidad';
            }
        }

        // 2. Código REGA de destino
        if (!$harvest->destination_rega_code) {
             if ($harvest->destination_type !== 'self_consumption') {
                $warnings[] = 'Falta Código REGA de destino - Esencial para trazabilidad SIEX';
             }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Generar reporte de cumplimiento en formato de texto
     * 
     * @param array $validation Resultado de validateActivities()
     * @return string
     */
    public function generateComplianceReport(array $validation): string
    {
        $report = "=== REPORTE DE CUMPLIMIENTO PAC ===\n\n";

        // Resumen
        $stats = $validation['stats'];
        $report .= "RESUMEN:\n";
        $report .= sprintf("- Total actividades analizadas: %d\n", $stats['total_activities']);
        $report .= sprintf("- Actividades con SIGPAC: %d (%.1f%%)\n", 
            $stats['with_sigpac'],
            $stats['total_activities'] > 0 ? ($stats['with_sigpac'] / $stats['total_activities']) * 100 : 0
        );
        $report .= sprintf("- Actividades sin SIGPAC: %d\n", $stats['without_sigpac']);
        $report .= sprintf("- SIGPAC válidos: %d\n", $stats['with_valid_sigpac']);
        $report .= sprintf("- Actividades sin parcela: %d\n", $stats['missing_plot']);

        // Estado de cumplimiento
        $report .= "\nESTADO: ";
        if ($validation['is_compliant']) {
            $report .= "✓ CUMPLE CON REQUISITOS PAC\n";
        } else {
            $report .= "✗ NO CUMPLE - Se requiere corrección\n";
        }

        if ($validation['has_warnings']) {
            $report .= "⚠ Hay advertencias que revisar\n";
        }

        // Errores críticos
        if (!empty($validation['errors'])) {
            $report .= "\nERRORES CRÍTICOS (deben corregirse):\n";
            foreach ($validation['errors'] as $error) {
                $report .= sprintf(
                    "\n- Actividad #%d (%s - %s):\n",
                    $error['activity_id'],
                    $error['activity_date'],
                    $error['activity_type']
                );
                foreach ($error['errors'] as $msg) {
                    $report .= "  • $msg\n";
                }
            }
        }

        // Advertencias
        if (!empty($validation['warnings'])) {
            $report .= "\nADVERTENCIAS (recomendado revisar):\n";
            $count = 0;
            foreach ($validation['warnings'] as $warning) {
                if ($count >= 10) {
                    $report .= sprintf("\n... y %d advertencias más.\n", count($validation['warnings']) - 10);
                    break;
                }
                $report .= sprintf(
                    "\n- Actividad #%d (%s - %s):\n",
                    $warning['activity_id'],
                    $warning['activity_date'],
                    $warning['activity_type']
                );
                foreach ($warning['warnings'] as $msg) {
                    $report .= "  • $msg\n";
                }
                $count++;
            }
        }

        return $report;
    }

    /**
     * Verificar si las parcelas de las actividades tienen SIGPAC
     * 
     * @param Collection $activities
     * @return bool
     */
    public function allActivitiesHaveSigpac(Collection $activities): bool
    {
        foreach ($activities as $activity) {
            if (!$activity->plot || $activity->plot->sigpacCodes->isEmpty()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Obtener porcentaje de cumplimiento
     * 
     * @param array $validation Resultado de validateActivities()
     * @return float Porcentaje de 0 a 100
     */
    public function getCompliancePercentage(array $validation): float
    {
        $stats = $validation['stats'];
        if ($stats['total_activities'] === 0) {
            return 100.0;
        }

        // Calcular actividades conformes (sin errores críticos)
        $activitiesWithErrors = count($validation['errors']);
        $compliantActivities = $stats['total_activities'] - $activitiesWithErrors;

        return ($compliantActivities / $stats['total_activities']) * 100;
    }
    
    /**
     * Obtener estadísticas detalladas de cumplimiento por tipo de actividad
     * 
     * @param Collection $activities
     * @return array
     */
    public function getComplianceStats(Collection $activities): array
    {
        $statsByType = [];
        $activityTypes = ['phytosanitary', 'irrigation', 'fertilization', 'harvest', 'cultural', 'observation'];
        
        foreach ($activityTypes as $type) {
            $typeActivities = $activities->where('activity_type', $type);
            
            if ($typeActivities->isEmpty()) {
                continue;
            }
            
            $validation = $this->validateActivities($typeActivities);
            
            $statsByType[$type] = [
                'total' => $typeActivities->count(),
                'compliant' => $typeActivities->count() - count($validation['errors']),
                'errors' => count($validation['errors']),
                'warnings' => count($validation['warnings']),
                'percentage' => $this->getCompliancePercentage($validation),
            ];
        }
        
        return $statsByType;
    }
}
