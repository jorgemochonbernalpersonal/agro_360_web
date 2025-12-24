<?php

namespace App\Services\Exporters;

use App\Models\OfficialReport;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Exportador de informes oficiales a formato CSV compatible con SIEX
 * 
 * Cumple con estándares españoles:
 * - Delimitador: punto y coma (;)
 * - Formato de fechas: DD/MM/YYYY
 * - Codificación: UTF-8 con BOM
 */
class SiexCsvExporter
{
    /**
     * Exportar informe de tratamientos fitosanitarios a CSV
     * 
     * @param OfficialReport $report
     * @param User $user
     * @param Collection $treatments
     * @param array $stats
     * @return string Path del archivo CSV generado
     */
    public function exportPhytosanitaryTreatments(
        OfficialReport $report,
        User $user,
        Collection $treatments,
        array $stats
    ): string {
        // Preparar datos CSV
        $csvData = [];
        
        // Cabecera del archivo con información del informe
        $csvData[] = ['INFORME OFICIAL DE TRATAMIENTOS FITOSANITARIOS'];
        $csvData[] = ['Código de Verificación', $report->verification_code];
        $csvData[] = ['Viticultor', $user->name];
        $csvData[] = ['Email', $user->email];
        $csvData[] = ['Periodo', $report->period_start->format('d/m/Y') . ' - ' . $report->period_end->format('d/m/Y')];
        $csvData[] = ['Fecha de Generación', $report->signed_at->format('d/m/Y H:i:s')];
        $csvData[] = ['Total Tratamientos', $stats['total_treatments']];
        $csvData[] = []; // Línea vacía
        
        // Cabecera de las columnas de datos
        $csvData[] = [
            'Fecha',
            'Estadio Fenológico',
            'Parcela',
            'Plantación',
            'Variedad',
            'Código SIGPAC',
            'Provincia',
            'Municipio',
            'Polígono',
            'Parcela SIGPAC',
            'Recinto',
            'Superficie Total (ha)',
            'Uso SIGPAC',
            'Producto',
            'Nº Registro',
            'Dosis (L/ha o kg/ha)',
            'Área Tratada (ha)',
            'Plazo Seguridad (días)',
            'Justificación Tratamiento',
            'Nº ROPO Aplicador',
            'Plazo Reentrada (días)',
            'Volumen Caldo (L)',
            'Operador',
            'Temperatura (°C)',
            'Observaciones'
        ];
        
        // Datos de tratamientos
        foreach ($treatments as $treatment) {
            $phyto = $treatment->phytosanitaryTreatment;
            $plot = $treatment->plot;
            
            // Obtener primer código SIGPAC de la parcela (si existe)
            $sigpacCode = $plot?->sigpacCodes->first();
            $sigpacUse = $plot?->sigpacUses->first();
            
            $csvData[] = [
                $treatment->activity_date->format('d/m/Y'),
                $treatment->phenological_stage ?? '',  // Estadio fenológico
                $plot?->name ?? 'N/A',
                $treatment->plotPlanting?->name ?? 'N/A',
                $treatment->plotPlanting?->grapeVariety?->name ?? 'N/A',
                $sigpacCode?->formatted_code ?? 'Sin SIGPAC',
                $sigpacCode?->code_province ?? '',
                $sigpacCode?->code_municipality ?? '',
                $sigpacCode?->code_polygon ?? '',
                $sigpacCode?->code_plot ?? '',
                $sigpacCode?->code_enclosure ?? '',
                $plot?->area ?? 'N/A',
                $sigpacUse?->description ?? '',
                $phyto?->product?->name ?? 'N/A',
                $phyto?->product?->registration_number ?? 'N/A',
                $phyto?->dose_per_hectare ?? 'N/A',
                $phyto?->area_treated ?? 'N/A',
                $phyto?->product?->withdrawal_period_days ?? 'N/A',
                $this->cleanCsvValue($phyto?->treatment_justification ?? ''),
                $phyto?->applicator_ropo_number ?? '',
                $phyto?->reentry_period_days ?? '',
                $phyto?->spray_volume ?? '',
                $treatment->crewMember?->name ?? 'N/A',
                $treatment->temperature ?? 'N/A',
                $this->cleanCsvValue($treatment->notes ?? '')
            ];
        }
        
        // Generar contenido CSV
        $csvContent = $this->generateCsvContent($csvData);
        
        // Guardar archivo
        $filename = 'tratamientos_fitosanitarios_' . $report->verification_code . '.csv';
        $path = 'official_reports/' . $filename;
        
        Storage::disk('local')->put($path, $csvContent);
        
        return $path;
    }

    /**
     * Exportar cuaderno digital completo a CSV
     * 
     * @param OfficialReport $report
     * @param User $user
     * @param Collection $activities
     * @param array $stats
     * @return string Path del archivo CSV generado
     */
    public function exportFullNotebook(
        OfficialReport $report,
        User $user,
        Collection $activities,
        array $stats
    ): string {
        // Preparar datos CSV
        $csvData = [];
        
        // Cabecera del archivo con información del informe
        $csvData[] = ['CUADERNO DIGITAL DE EXPLOTACIÓN AGRÍCOLA'];
        $csvData[] = ['Código de Verificación', $report->verification_code];
        $csvData[] = ['Viticultor', $user->name];
        $csvData[] = ['Email', $user->email];
        
        // Obtener datos de la campaña desde metadata
        $campaignName = $report->report_metadata['campaign_name'] ?? 'N/A';
        $csvData[] = ['Campaña', $campaignName];
        
        $csvData[] = ['Periodo', $report->period_start->format('d/m/Y') . ' - ' . $report->period_end->format('d/m/Y')];
        $csvData[] = ['Fecha de Generación', $report->signed_at->format('d/m/Y H:i:s')];
        $csvData[] = ['Total Actividades', $stats['total_activities']];
        $csvData[] = []; // Línea vacía
        
        // Cabecera de las columnas de datos
        $csvData[] = [
            'Fecha',
            'Tipo de Actividad',
            'Estadio Fenológico',
            'Parcela',
            'Plantación',
            'Variedad',
            'Código SIGPAC',
            'Provincia',
            'Municipio',
            'Polígono',
            'Parcela SIGPAC',
            'Recinto',
            'Superficie Total (ha)',
            'Uso SIGPAC',
            'Operador',
            'Duración (horas)',
            'Temperatura (°C)',
            'Origen Agua',
            'Concesión',
            'Caudal (L/h)',
            'N (UF)',
            'P (UF)',
            'K (UF)',
            'Tipo Estiércol',
            'Fecha Enterrado',
            'Método Reducción',
            'Documento Transporte',
            'REGA Destino',
            'Matrícula',
            'Observaciones'
        ];
        
        // Datos de actividades
        foreach ($activities as $activity) {
            $plot = $activity->plot;
            
            // Obtener primer código SIGPAC de la parcela (si existe)
            $sigpacCode = $plot?->sigpacCodes->first();
            $sigpacUse = $plot?->sigpacUses->first();
            
            // Mapear tipo de actividad
            $activityTypes = [
                'phytosanitary' => 'Tratamiento Fitosanitario',
                'fertilization' => 'Fertilización',
                'irrigation' => 'Riego',
                'pruning' => 'Poda',
                'harvest' => 'Cosecha',
                'planting' => 'Plantación',
                'maintenance' => 'Mantenimiento',
                'other' => 'Otra'
            ];
            
            $csvData[] = [
                $activity->activity_date->format('d/m/Y'),
                $activityTypes[$activity->activity_type] ?? $activity->activity_type,
                $activity->phenological_stage ?? '',
                $plot?->name ?? 'N/A',
                $activity->plotPlanting?->name ?? 'N/A',
                $activity->plotPlanting?->grapeVariety?->name ?? 'N/A',
                $sigpacCode?->formatted_code ?? 'Sin SIGPAC',
                $sigpacCode?->code_province ?? '',
                $sigpacCode?->code_municipality ?? '',
                $sigpacCode?->code_polygon ?? '',
                $sigpacCode?->code_plot ?? '',
                $sigpacCode?->code_enclosure ?? '',
                $plot?->area ?? 'N/A',
                $sigpacUse?->description ?? '',
                $activity->crewMember?->name ?? 'N/A',
                $activity->duration_hours ?? 'N/A',
                $activity->temperature ?? 'N/A',
                $activity->irrigation?->water_source ?? '',
                $activity->irrigation?->water_concession ?? '',
                $activity->irrigation?->flow_rate ?? '',
                $activity->fertilization?->nitrogen_uf ?? '',
                $activity->fertilization?->phosphorus_uf ?? '',
                $activity->fertilization?->potassium_uf ?? '',
                $activity->fertilization?->manure_type ?? '',
                $activity->fertilization?->burial_date ? $activity->fertilization->burial_date->format('d/m/Y') : '',
                $activity->fertilization?->emission_reduction_method ?? '',
                $activity->harvest?->transport_document_number ?? '',
                $activity->harvest?->destination_rega_code ?? '',
                $activity->harvest?->vehicle_plate ?? '',
                $this->cleanCsvValue($activity->notes ?? '')
            ];
        }
        
        // Generar contenido CSV
        $csvContent = $this->generateCsvContent($csvData);
        
        // Guardar archivo
        $filename = 'cuaderno_digital_' . $report->verification_code . '.csv';
        $path = 'official_reports/' . $filename;
        
        Storage::disk('local')->put($path, $csvContent);
        
        return $path;
    }

    /**
     * Generar contenido CSV con formato español
     * 
     * @param array $data
     * @return string
     */
    protected function generateCsvContent(array $data): string
    {
        // UTF-8 BOM para compatibilidad con Excel
        $csvContent = "\xEF\xBB\xBF";
        
        foreach ($data as $row) {
            // Escapar y formatear cada celda
            $escapedRow = array_map(function($cell) {
                // Convertir a string
                $cell = (string)$cell;
                
                // Si contiene punto y coma, comillas o saltos de línea, encerrar entre comillas
                if (str_contains($cell, ';') || str_contains($cell, '"') || str_contains($cell, "\n")) {
                    // Duplicar comillas internas
                    $cell = str_replace('"', '""', $cell);
                    $cell = '"' . $cell . '"';
                }
                
                return $cell;
            }, $row);
            
            // Unir con punto y coma (estándar europeo)
            $csvContent .= implode(';', $escapedRow) . "\r\n";
        }
        
        return $csvContent;
    }

    /**
     * Limpiar valor para CSV (eliminar saltos de línea extras)
     * 
     * @param string $value
     * @return string
     */
    protected function cleanCsvValue(string $value): string
    {
        // Reemplazar múltiples saltos de línea por uno solo
        $value = preg_replace('/\r\n|\r|\n/', ' ', $value);
        
        // Eliminar espacios extras
        $value = trim($value);
        
        return $value;
    }
}
