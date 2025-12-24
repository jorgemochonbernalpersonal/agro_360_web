<?php

namespace App\Services\Exporters;

use App\Models\OfficialReport;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use DOMDocument;
use DOMElement;

/**
 * Exportador de informes oficiales a formato XML compatible con SIEX
 * 
 * Genera XML estructurado jerárquicamente con metadatos de verificación
 */
class SiexXmlExporter
{
    /**
     * Exportar informe de tratamientos fitosanitarios a XML
     * 
     * @param OfficialReport $report
     * @param User $user
     * @param Collection $treatments
     * @param array $stats
     * @return string Path del archivo XML generado
     */
    public function exportPhytosanitaryTreatments(
        OfficialReport $report,
        User $user,
        Collection $treatments,
        array $stats
    ): string {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Elemento raíz
        $root = $xml->createElement('InformeTratamientosFitosanitarios');
        $root->setAttribute('version', '1.0');
        $root->setAttribute('xmlns', 'http://agro365.es/schema/siex');
        $xml->appendChild($root);
        
        // Metadatos del informe
        $metadata = $xml->createElement('Metadatos');
        $this->addElement($xml, $metadata, 'CodigoVerificacion', $report->verification_code);
        $this->addElement($xml, $metadata, 'FechaGeneracion', $report->signed_at->format('Y-m-d\TH:i:s'));
        $this->addElement($xml, $metadata, 'HashFirma', $report->signature_hash);
        $this->addElement($xml, $metadata, 'IPFirma', $report->signed_ip);
        $this->addElement($xml, $metadata, 'URLVerificacion', $report->verification_url);
        $root->appendChild($metadata);
        
        // Información del viticultor
        $viticultor = $xml->createElement('Viticultor');
        $this->addElement($xml, $viticultor, 'Nombre', $user->name);
        $this->addElement($xml, $viticultor, 'Email', $user->email);
        $root->appendChild($viticultor);
        
        // Periodo del informe
        $periodo = $xml->createElement('Periodo');
        $this->addElement($xml, $periodo, 'FechaInicio', $report->period_start->format('Y-m-d'));
        $this->addElement($xml, $periodo, 'FechaFin', $report->period_end->format('Y-m-d'));
        $root->appendChild($periodo);
        
        // Estadísticas
        $estadisticas = $xml->createElement('Estadisticas');
        $this->addElement($xml, $estadisticas, 'TotalTratamientos', (string)$stats['total_treatments']);
        $this->addElement($xml, $estadisticas, 'AreaTotalTratada', (string)($stats['total_area_treated'] ?? 0));
        $this->addElement($xml, $estadisticas, 'ParcelasAfectadas', (string)($stats['plots_affected'] ?? 0));
        $root->appendChild($estadisticas);
        
        // Tratamientos
        $tratamientos = $xml->createElement('Tratamientos');
        
        foreach ($treatments as $treatment) {
            $phyto = $treatment->phytosanitaryTreatment;
            
            $tratamiento = $xml->createElement('Tratamiento');
            $tratamiento->setAttribute('id', (string)$treatment->id);
            
            $this->addElement($xml, $tratamiento, 'Fecha', $treatment->activity_date->format('Y-m-d'));
            $this->addElement($xml, $tratamiento, 'EstadioFenologico', $treatment->phenological_stage ?? '');
            
            // Ubicación
            $ubicacion = $xml->createElement('Ubicacion');
            $this->addElement($xml, $ubicacion, 'Parcela', $treatment->plot?->name ?? '');
            $this->addElement($xml, $ubicacion, 'Plantacion', $treatment->plotPlanting?->name ?? '');
            $this->addElement($xml, $ubicacion, 'Variedad', $treatment->plotPlanting?->grapeVariety?->name ?? '');
            
            // Datos SIGPAC
            $plot = $treatment->plot;
            $sigpacCode = $plot?->sigpacCodes->first();
            $sigpacUse = $plot?->sigpacUses->first();
            
            if ($sigpacCode) {
                $sigpacNode = $xml->createElement('DatosSIGPAC');
                $this->addElement($xml, $sigpacNode, 'CodigoCompleto', $sigpacCode->code ?? '');
                $this->addElement($xml, $sigpacNode, 'CodigoFormateado', $sigpacCode->formatted_code ?? '');
                $this->addElement($xml, $sigpacNode, 'Provincia', $sigpacCode->code_province ?? '');
                $this->addElement($xml, $sigpacNode, 'Municipio', $sigpacCode->code_municipality ?? '');
                $this->addElement($xml, $sigpacNode, 'Poligono', $sigpacCode->code_polygon ?? '');
                $this->addElement($xml, $sigpacNode, 'Parcela', $sigpacCode->code_plot ?? '');
                $this->addElement($xml, $sigpacNode, 'Recinto', $sigpacCode->code_enclosure ?? '');
                $this->addElement($xml, $sigpacNode, 'SuperficieTotal', (string)($plot?->area ?? ''));
                $this->addElement($xml, $sigpacNode, 'UsoSIGPAC', $sigpacUse?->description ?? '');
                $ubicacion->appendChild($sigpacNode);
            }
            
            $tratamiento->appendChild($ubicacion);
            
            
            // Producto fitosanitario
            if ($phyto && $phyto->product) {
                $producto = $xml->createElement('ProductoFitosanitario');
                $this->addElement($xml, $producto, 'Nombre', $phyto->product->name);
                $this->addElement($xml, $producto, 'NumeroRegistro', $phyto->product->registration_number ?? '');
                $this->addElement($xml, $producto, 'DosisPorHectarea', (string)($phyto->dose_per_hectare ?? ''));
                $this->addElement($xml, $producto, 'AreaTratada', (string)($phyto->area_treated ?? ''));
                $this->addElement($xml, $producto, 'PlazoSeguridad', (string)($phyto->product->withdrawal_period_days ?? ''));
                
                // Campos PAC obligatorios
                $this->addElement($xml, $producto, 'JustificacionTratamiento', $phyto->treatment_justification ?? '');
                $this->addElement($xml, $producto, 'NumeroROPOAplicador', $phyto->applicator_ropo_number ?? '');
                $this->addElement($xml, $producto, 'PlazoReentrada', (string)($phyto->reentry_period_days ?? ''));
                $this->addElement($xml, $producto, 'VolumenCaldo', (string)($phyto->spray_volume ?? ''));
                
                $tratamiento->appendChild($producto);
            }
            
            // Operador
            if ($treatment->crewMember) {
                $this->addElement($xml, $tratamiento, 'Operador', $treatment->crewMember->name);
            }
            
            // Condiciones
            $condiciones = $xml->createElement('Condiciones');
            $this->addElement($xml, $condiciones, 'Temperatura', (string)($treatment->temperature ?? ''));
            $tratamiento->appendChild($condiciones);
            
            // Observaciones
            if ($treatment->notes) {
                $this->addElement($xml, $tratamiento, 'Observaciones', $treatment->notes);
            }
            
            $tratamientos->appendChild($tratamiento);
        }
        
        $root->appendChild($tratamientos);
        
        // Guardar archivo
        $xmlContent = $xml->saveXML();
        $filename = 'tratamientos_fitosanitarios_' . $report->verification_code . '.xml';
        $path = 'official_reports/' . $filename;
        
        Storage::disk('local')->put($path, $xmlContent);
        
        return $path;
    }

    /**
     * Exportar cuaderno digital completo a XML
     * 
     * @param OfficialReport $report
     * @param User $user
     * @param Collection $activities
     * @param array $stats
     * @return string Path del archivo XML generado
     */
    public function exportFullNotebook(
        OfficialReport $report,
        User $user,
        Collection $activities,
        array $stats
    ): string {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Elemento raíz
        $root = $xml->createElement('CuadernoDigitalExplotacion');
        $root->setAttribute('version', '1.0');
        $root->setAttribute('xmlns', 'http://agro365.es/schema/siex');
        $xml->appendChild($root);
        
        // Metadatos del informe
        $metadata = $xml->createElement('Metadatos');
        $this->addElement($xml, $metadata, 'CodigoVerificacion', $report->verification_code);
        $this->addElement($xml, $metadata, 'FechaGeneracion', $report->signed_at->format('Y-m-d\TH:i:s'));
        $this->addElement($xml, $metadata, 'HashFirma', $report->signature_hash);
        $this->addElement($xml, $metadata, 'IPFirma', $report->signed_ip);
        $this->addElement($xml, $metadata, 'URLVerificacion', $report->verification_url);
        $root->appendChild($metadata);
        
        // Información del viticultor
        $viticultor = $xml->createElement('Viticultor');
        $this->addElement($xml, $viticultor, 'Nombre', $user->name);
        $this->addElement($xml, $viticultor, 'Email', $user->email);
        $root->appendChild($viticultor);
        
        // Campaña
        $campana = $xml->createElement('Campana');
        $campaignName = $report->report_metadata['campaign_name'] ?? 'N/A';
        $this->addElement($xml, $campana, 'Nombre', $campaignName);
        $this->addElement($xml, $campana, 'FechaInicio', $report->period_start->format('Y-m-d'));
        $this->addElement($xml, $campana, 'FechaFin', $report->period_end->format('Y-m-d'));
        $root->appendChild($campana);
        
        // Estadísticas
        $estadisticas = $xml->createElement('Estadisticas');
        $this->addElement($xml, $estadisticas, 'TotalActividades', (string)$stats['total_activities']);
        $root->appendChild($estadisticas);
        
        // Actividades
        $actividades = $xml->createElement('Actividades');
        
        foreach ($activities as $activity) {
            $actividadElement = $xml->createElement('Actividad');
            $actividadElement->setAttribute('id', (string)$activity->id);
            $actividadElement->setAttribute('tipo', $activity->activity_type);
            
            $this->addElement($xml, $actividadElement, 'Fecha', $activity->activity_date->format('Y-m-d'));
            $this->addElement($xml, $actividadElement, 'EstadioFenologico', $activity->phenological_stage ?? '');
            
            // Ubicación
            $ubicacion = $xml->createElement('Ubicacion');
            $this->addElement($xml, $ubicacion, 'Parcela', $activity->plot?->name ?? '');
            $this->addElement($xml, $ubicacion, 'Plantacion', $activity->plotPlanting?->name ?? '');
            $this->addElement($xml, $ubicacion, 'Variedad', $activity->plotPlanting?->grapeVariety?->name ?? '');
            
            // Datos SIGPAC
            $plot = $activity->plot;
            $sigpacCode = $plot?->sigpacCodes->first();
            $sigpacUse = $plot?->sigpacUses->first();
            
            if ($sigpacCode) {
                $sigpacNode = $xml->createElement('DatosSIGPAC');
                $this->addElement($xml, $sigpacNode, 'CodigoCompleto', $sigpacCode->code ?? '');
                $this->addElement($xml, $sigpacNode, 'CodigoFormateado', $sigpacCode->formatted_code ?? '');
                $this->addElement($xml, $sigpacNode, 'Provincia', $sigpacCode->code_province ?? '');
                $this->addElement($xml, $sigpacNode, 'Municipio', $sigpacCode->code_municipality ?? '');
                $this->addElement($xml, $sigpacNode, 'Poligono', $sigpacCode->code_polygon ?? '');
                $this->addElement($xml, $sigpacNode, 'Parcela', $sigpacCode->code_plot ?? '');
                $this->addElement($xml, $sigpacNode, 'Recinto', $sigpacCode->code_enclosure ?? '');
                $this->addElement($xml, $sigpacNode, 'SuperficieTotal', (string)($plot?->area ?? ''));
                $this->addElement($xml, $sigpacNode, 'UsoSIGPAC', $sigpacUse?->description ?? '');
                $ubicacion->appendChild($sigpacNode);
            }
            
            $actividadElement->appendChild($ubicacion);
            
            // Operador
            if ($activity->crewMember) {
                $this->addElement($xml, $actividadElement, 'Operador', $activity->crewMember->name);
            }
            
            // Detalles
            $detalles = $xml->createElement('Detalles');
            $this->addElement($xml, $detalles, 'DuracionHoras', (string)($activity->duration_hours ?? ''));
            $this->addElement($xml, $detalles, 'Temperatura', (string)($activity->temperature ?? ''));
            
            // Si es riego, añadir detalles PAC
            if ($activity->activity_type === 'irrigation' && $activity->irrigation) {
                $riego = $xml->createElement('RiegoPAC');
                $this->addElement($xml, $riego, 'OrigenAgua', $activity->irrigation->water_source ?? '');
                $this->addElement($xml, $riego, 'Concesion', $activity->irrigation->water_concession ?? '');
                $this->addElement($xml, $riego, 'Caudal', (string)($activity->irrigation->flow_rate ?? ''));
                $actividadElement->appendChild($riego);
            }
            
            // Si es fertilización, añadir detalles PAC
            if ($activity->activity_type === 'fertilization' && $activity->fertilization) {
                $fert = $xml->createElement('FertilizacionPAC');
                $f = $activity->fertilization;
                
                if ($f->nitrogen_uf !== null) $this->addElement($xml, $fert, 'NitrogenoUF', (string)$f->nitrogen_uf);
                if ($f->phosphorus_uf !== null) $this->addElement($xml, $fert, 'FosforoUF', (string)$f->phosphorus_uf);
                if ($f->potassium_uf !== null) $this->addElement($xml, $fert, 'PotasioUF', (string)$f->potassium_uf);
                
                if ($f->manure_type) $this->addElement($xml, $fert, 'TipoEstiercol', $f->manure_type);
                if ($f->burial_date) $this->addElement($xml, $fert, 'FechaEnterrado', $f->burial_date->format('Y-m-d'));
                if ($f->emission_reduction_method) $this->addElement($xml, $fert, 'MetodoReduccion', $f->emission_reduction_method);
                
                $actividadElement->appendChild($fert);
            }

            // Si es cosecha, añadir detalles trazabilidad (PAC)
            if ($activity->activity_type === 'harvest' && $activity->harvest) {
                $cosecha = $xml->createElement('CosechaPAC');
                $h = $activity->harvest;
                if ($h->transport_document_number) $this->addElement($xml, $cosecha, 'DocumentoTransporte', $h->transport_document_number);
                if ($h->destination_rega_code) $this->addElement($xml, $cosecha, 'REGADestino', $h->destination_rega_code);
                if ($h->vehicle_plate) $this->addElement($xml, $cosecha, 'Matricula', $h->vehicle_plate);
                
                $actividadElement->appendChild($cosecha);
            }
            
            $actividadElement->appendChild($detalles);
            
            // Observaciones
            if ($activity->notes) {
                $this->addElement($xml, $actividadElement, 'Observaciones', $activity->notes);
            }
            
            $actividades->appendChild($actividadElement);
        }
        
        $root->appendChild($actividades);
        
        // Guardar archivo
        $xmlContent = $xml->saveXML();
        $filename = 'cuaderno_digital_' . $report->verification_code . '.xml';
        $path = 'official_reports/' . $filename;
        
        Storage::disk('local')->put($path, $xmlContent);
        
        return $path;
    }

    /**
     * Añadir elemento al XML con CDATA si es necesario
     * 
     * @param DOMDocument $xml
     * @param DOMElement $parent
     * @param string $name
     * @param string $value
     * @return DOMElement
     */
    protected function addElement(DOMDocument $xml, DOMElement $parent, string $name, string $value): DOMElement
    {
        $element = $xml->createElement($name);
        
        // Si el valor contiene caracteres especiales XML, usar CDATA
        if ($this->needsCData($value)) {
            $cdata = $xml->createCDATASection($value);
            $element->appendChild($cdata);
        } else {
            $element->nodeValue = htmlspecialchars($value, ENT_XML1, 'UTF-8');
        }
        
        $parent->appendChild($element);
        
        return $element;
    }

    /**
     * Verificar si un valor necesita CDATA
     * 
     * @param string $value
     * @return bool
     */
    protected function needsCData(string $value): bool
    {
        return str_contains($value, '<') || 
               str_contains($value, '>') || 
               str_contains($value, '&') ||
               str_contains($value, "\n");
    }
}
