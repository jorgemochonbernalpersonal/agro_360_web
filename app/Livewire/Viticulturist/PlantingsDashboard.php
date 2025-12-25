<?php

namespace App\Livewire\Viticulturist;

use App\Models\PlotPlanting;
use App\Services\Validators\PlantingRightsValidator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PlantingsDashboard extends Component
{
    public function render()
    {
        $plantings = PlotPlanting::whereHas('plot', function ($query) {
            $query->forUser(Auth::user());
        })->with(['plot', 'grapeVariety', 'certifications'])->get();
        
        // Métricas generales
        $totalPlantings = $plantings->count();
        $totalSurface = $plantings->sum('area_planted');
        $varieties = $plantings->pluck('grape_variety_id')->unique()->count();
        
        // Métricas por estado
        $statusStats = $plantings->groupBy('status')->map(function ($group) {
            return [
                'count' => $group->count(),
                'surface' => $group->sum('area_planted'),
            ];
        });
        
        // Distribución por edad
        $ageStats = [
            'joven' => $plantings->filter(fn($p) => $p->life_cycle_stage === 'joven')->count(),
            'desarrollo' => $plantings->filter(fn($p) => $p->life_cycle_stage === 'desarrollo')->count(),
            'productiva' => $plantings->filter(fn($p) => $p->life_cycle_stage === 'productiva')->count(),
            'madura' => $plantings->filter(fn($p) => $p->life_cycle_stage === 'madura')->count(),
            'vieja' => $plantings->filter(fn($p) => $p->life_cycle_stage === 'vieja')->count(),
        ];
        
        $needsReplanting = $plantings->filter(fn($p) => $p->needsReplanting())->count();
        
        // Certificaciones
        $totalCertifications = $plantings->sum(fn($p) => $p->certifications()->active()->count());
        $expiringCertifications = $plantings->flatMap(fn($p) => $p->certifications()->expiringSoon()->get())->count();
        $certifiedPlantings = $plantings->filter(fn($p) => $p->certifications()->active()->count() > 0)->count();
        
        // Tratamientos fitosanitarios (últimos 30 días)
        $plotIds = $plantings->pluck('plot_id')->unique();
        $recentTreatments = \App\Models\PhytosanitaryTreatment::whereHas('activity', function($q) use ($plotIds) {
            $q->whereIn('plot_id', $plotIds)
              ->where('activity_date', '>=', now()->subDays(30));
        })->with(['pest', 'product'])->get();
        
        $activeTreatments = $recentTreatments->count();
        $uniquePests = $recentTreatments->pluck('pest_id')->unique()->count();
        
        // Cumplimiento PAC - Autorizaciones
        $validator = new PlantingRightsValidator();
        $withAuthorization = 0;
        $needsAuthorization = 0;
        $missingAuthorization = 0;
        
        $alerts = [];
        
        foreach ($plantings as $planting) {
            // Validación PAC
            $validation = $validator->validate($planting);
            
            if ($validation['requires_authorization']) {
                $needsAuthorization++;
                
                if ($planting->planting_authorization) {
                    $withAuthorization++;
                } else {
                    $missingAuthorization++;
                    $alerts[] = [
                        'type' => 'error',
                        'planting' => $planting->name ?: $planting->grapeVariety->name ?? 'Sin nombre',
                        'plot' => $planting->plot->name,
                        'message' => 'Falta autorización de plantación (obligatorio post-2016)',
                    ];
                }
            }
            
            // Añadir errores de validación
            if (!$validation['valid']) {
                foreach ($validation['errors'] as $error) {
                    $alerts[] = [
                        'type' => 'error',
                        'planting' => $planting->name ?: $planting->grapeVariety->name ?? 'Sin nombre',
                        'plot' => $planting->plot->name,
                        'message' => $error,
                    ];
                }
            }
            
            // Alertas de replantación
            if ($planting->needsReplanting()) {
                $alerts[] = [
                    'type' => 'warning',
                    'planting' => $planting->name ?: $planting->grapeVariety->name ?? 'Sin nombre',
                    'plot' => $planting->plot->name,
                    'message' => "Plantación antigua ({$planting->age} años). Considerar replantación.",
                ];
            }
            
            // Alertas de certificaciones
            foreach ($planting->certifications()->expiringSoon()->get() as $cert) {
                $alerts[] = [
                    'type' => 'warning',
                    'planting' => $planting->name ?: $planting->grapeVariety->name ?? 'Sin nombre',
                    'plot' => $planting->plot->name,
                    'message' => "Certificación {$cert->type} vence el {$cert->expiry_date->format('d/m/Y')}",
                ];
            }
        }
        
        $authorizationPercentage = $needsAuthorization > 0 
            ? ($withAuthorization / $needsAuthorization) * 100 
            : 100;
        
        return view('livewire.viticulturist.plantings-dashboard', [
            'totalPlantings' => $totalPlantings,
            'totalSurface' => round($totalSurface, 2),
            'varieties' => $varieties,
            'statusStats' => $statusStats,
            'ageStats' => $ageStats,
            'needsReplanting' => $needsReplanting,
            'totalCertifications' => $totalCertifications,
            'expiringCertifications' => $expiringCertifications,
            'certifiedPlantings' => $certifiedPlantings,
            'activeTreatments' => $activeTreatments,
            'uniquePests' => $uniquePests,
            'needsAuthorization' => $needsAuthorization,
            'withAuthorization' => $withAuthorization,
            'missingAuthorization' => $missingAuthorization,
            'authorizationPercentage' => round($authorizationPercentage, 1),
            'alerts' => collect($alerts)->sortByDesc(fn($a) => $a['type'] === 'error')->take(15),
            'totalAlerts' => count($alerts),
        ]);
    }
}
