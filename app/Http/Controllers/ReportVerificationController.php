<?php

namespace App\Http\Controllers;

use App\Models\OfficialReport;
use App\Models\AgriculturalActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportVerificationController extends Controller
{
    /**
     * Verificar autenticidad de un informe oficial
     * 
     * @param string $code Código de verificación
     * @return \Illuminate\View\View
     */
    public function verify(string $code)
    {
        // Buscar el informe por código de verificación
        $report = OfficialReport::where('verification_code', $code)
            ->with(['user.profile.province'])
            ->first();

        if (!$report) {
            return view('reports.verification-result', [
                'found' => false,
                'code' => $code,
                'message' => 'Código de verificación inválido o informe no encontrado.',
            ]);
        }

        // Incrementar contador de verificaciones
        $report->incrementVerificationCount();

        // Verificar si el informe es válido
        $isValid = $report->isValid();
        
        // Verificar integridad del hash
        $integrityValid = $this->verifyIntegrity($report);
        
        $message = $isValid && $integrityValid
            ? '✓ Este informe es válido y auténtico. La integridad del documento ha sido verificada.' 
            : '✗ Este informe ha sido invalidado o modificado.';

        if (!$isValid && $report->invalidation_reason) {
            $message .= ' Motivo: ' . $report->invalidation_reason;
        } elseif (!$integrityValid) {
            $message .= ' El contenido del documento ha sido modificado después de la firma.';
        }

        return view('reports.verification-result', [
            'found' => true,
            'report' => $report,
            'is_valid' => $isValid && $integrityValid,
            'integrity_valid' => $integrityValid,
            'message' => $message,
            'code' => $code,
        ]);
    }

    /**
     * Verificar la integridad del hash del informe
     * 
     * @param OfficialReport $report
     * @return bool
     */
    protected function verifyIntegrity(OfficialReport $report): bool
    {
        try {
            // MÉTODO 1 (PRIMARIO): Verificar hash del PDF real si existe
            // Esto es más confiable que reconstruir datos que pueden cambiar de orden
            $pdfPath = storage_path('app/' . $report->pdf_path);
            
            if (file_exists($pdfPath)) {
                $metadata = $report->signature_metadata ?? [];
                $originalPdfHash = $metadata['pdf_hash'] ?? null;
                
                if ($originalPdfHash) {
                    $currentPdfHash = hash_file('sha256', $pdfPath);
                    
                    Log::info('Verificando PDF real', [
                        'report_id' => $report->id,
                        'original_hash' => substr($originalPdfHash, 0, 16) . '...',
                        'current_hash' => substr($currentPdfHash, 0, 16) . '...',
                        'match' => $originalPdfHash === $currentPdfHash,
                    ]);
                    
                    // Si el PDF coincide, el informe es íntegro
                    if ($originalPdfHash === $currentPdfHash) {
                        return true;
                    }
                    
                    // Si no coincide, el PDF fue modificado
                    Log::warning('PDF hash mismatch - archivo modificado', [
                        'report_id' => $report->id,
                        'expected' => $originalPdfHash,
                        'actual' => $currentPdfHash,
                    ]);
                    return false;
                }
            }
            
            // MÉTODO 2 (FALLBACK): Reconstruir signatureData y verificar
            // Solo si PDF no existe o no tiene hash guardado
            Log::info('PDF no disponible, verificando via signatureData', [
                'report_id' => $report->id,
            ]);
            
            // Reconstruir los datos de firma según el tipo de informe
            if ($report->report_type === 'phytosanitary_treatments') {
                $treatments = AgriculturalActivity::ofType('phytosanitary')
                    ->forUser($report->user_id)
                    ->whereBetween('activity_date', [$report->period_start, $report->period_end])
                    ->with(['plotPlanting'])
                    ->get();

                $stats = [
                    'total_treatments' => $treatments->count(),
                    'total_area_treated' => $treatments->sum(function ($t) {
                        return $t->phytosanitaryTreatment->area_treated ?? 0;
                    }),
                    'products_used' => $treatments
                        ->pluck('phytosanitaryTreatment.product.name')
                        ->unique()
                        ->filter()
                        ->values()
                        ->toArray(),
                    'plots_affected' => $treatments->pluck('plot.name')->unique()->count(),
                ];

                // Obtener hash del PDF original (almacenado en metadata)
                $metadata = $report->signature_metadata ?? [];
                $originalPdfHash = $metadata['pdf_hash'] ?? null;
                
                // Si no hay hash guardado, el informe no puede verificarse
                if (!$originalPdfHash) {
                    \Log::warning('PDF hash no encontrado en signature_metadata', [
                        'report_id' => $report->id,
                    ]);
                    return false;
                }

                $signatureData = [
                    'type' => 'phytosanitary_treatments',
                    'user_id' => $report->user_id,
                    'period_start' => $report->period_start->toDateString(),
                    'period_end' => $report->period_end->toDateString(),
                    'treatment_ids' => $treatments->pluck('id')->toArray(),
                    'stats' => $stats,
                    'pdf_hash' => $originalPdfHash, // Hash del PDF original usado en la firma
                    'timestamp' => $report->signed_at->toIso8601String(),
                ];

                // Recuperar nonce y versión del metadata (ya cargado arriba)
                if (isset($metadata['nonce'])) {
                    $signatureData['nonce'] = $metadata['nonce'];
                }
                if (isset($metadata['signature_version'])) {
                    $signatureData['signature_version'] = $metadata['signature_version'];
                }

            } elseif ($report->report_type === 'full_digital_notebook') {
                $campaignId = $report->report_metadata['campaign_id'] ?? null;
                
                if (!$campaignId) {
                    return false;
                }

                $activities = AgriculturalActivity::forUser($report->user_id)
                    ->forCampaign($campaignId)
                    ->get();

                $stats = [
                    'total_activities' => $activities->count(),
                    'phytosanitary_count' => $activities->where('activity_type', 'phytosanitary')->count(),
                    'fertilization_count' => $activities->where('activity_type', 'fertilization')->count(),
                    'irrigation_count' => $activities->where('activity_type', 'irrigation')->count(),
                    'cultural_count' => $activities->where('activity_type', 'cultural')->count(),
                    'observation_count' => $activities->where('activity_type', 'observation')->count(),
                    'harvest_count' => $activities->where('activity_type', 'harvest')->count(),
                ];

                // Obtener hash del PDF original (almacenado en metadata)
                $metadata = $report->signature_metadata ?? [];
                $originalPdfHash = $metadata['pdf_hash'] ?? null;
                
                // Si no hay hash guardado, el informe no puede verificarse
                if (!$originalPdfHash) {
                    \Log::warning('PDF hash no encontrado en signature_metadata', [
                        'report_id' => $report->id,
                    ]);
                    return false;
                }

                $signatureData = [
                    'type' => 'full_digital_notebook',
                    'user_id' => $report->user_id,
                    'campaign_id' => $campaignId,
                    'campaign_name' => $report->report_metadata['campaign_name'] ?? '',
                    'period_start' => $report->period_start->toDateString(),
                    'period_end' => $report->period_end->toDateString(),
                    'activity_ids' => $activities->pluck('id')->toArray(),
                    'stats' => $stats,
                    'pdf_hash' => $originalPdfHash, // Hash del PDF original usado en la firma
                    'timestamp' => $report->signed_at->toIso8601String(),
                ];

                // Recuperar nonce y versión del metadata (ya cargado arriba)
                if (isset($metadata['nonce'])) {
                    $signatureData['nonce'] = $metadata['nonce'];
                }
                if (isset($metadata['signature_version'])) {
                    $signatureData['signature_version'] = $metadata['signature_version'];
                }
            } else {
                // Tipo de informe no soportado para verificación de integridad
                return true; // Asumir válido si no podemos verificar
            }

            // Verificar el hash
            return OfficialReport::verifySignatureHash($signatureData, $report->signature_hash);

        } catch (\Exception $e) {
            Log::error('Error verificando integridad del informe', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
            ]);
            
            // En caso de error, asumir que no es válido por seguridad
            return false;
        }
    }
}
