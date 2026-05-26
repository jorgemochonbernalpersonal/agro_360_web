<?php

namespace App\Livewire\Viticulturist;

use App\Models\Plot;
use App\Services\Validators\PacEligibilityValidator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PlotsDashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        
        // ✅ OPTIMIZACIÓN: Cargar solo campos necesarios para el dashboard
        $plots = Plot::forUser($user)
            ->select([
                'id',
                'name',
                'area',
                'pac_eligible_area',
                'non_eligible_area',
                'eligibility_coefficient',
                'active',
                'is_locked',
            ])
            ->get();

        // Métricas de superficie (optimizado: cálculos en memoria)
        $totalSurface = $plots->sum('area');
        $eligibleSurface = $plots->sum('pac_eligible_area') ?: $totalSurface;
        $nonEligibleSurface = $plots->sum('non_eligible_area');
        $eligibilityPercentage = $totalSurface > 0 ? ($eligibleSurface / $totalSurface) * 100 : 0;
        
        // ✅ OPTIMIZACIÓN: Validar en batch para reducir overhead
        $alerts = [];
        $validator = new PacEligibilityValidator();
        
        foreach ($plots as $plot) {
            // Alertas de superficie
            if (!$plot->pac_eligible_area) {
                $alerts[] = [
                    'type' => 'warning',
                    'plot' => $plot->name,
                    'message' => __('Falta definir superficie admisible PAC'),
                ];
            }
            
            // Validar coherencia (no hace queries, solo cálculos)
            $validation = $validator->validate($plot);
            if (!$validation['valid']) {
                foreach ($validation['errors'] as $error) {
                    $alerts[] = [
                        'type' => 'error',
                        'plot' => $plot->name,
                        'message' => $error,
                    ];
                }
            }
            
            if (!empty($validation['warnings'])) {
                foreach ($validation['warnings'] as $warning) {
                    $alerts[] = [
                        'type' => 'warning',
                        'plot' => $plot->name,
                        'message' => $warning,
                    ];
                }
            }
            
        }
        
        return view('livewire.viticulturist.plots-dashboard', [
            'totalPlots' => $plots->count(),
            'activePlots' => $plots->where('active', true)->count(),
            'lockedPlots' => $plots->where('is_locked', true)->count(),
            'totalSurface' => round($totalSurface, 2),
            'eligibleSurface' => round($eligibleSurface, 2),
            'nonEligibleSurface' => round($nonEligibleSurface, 2),
            'eligibilityPercentage' => round($eligibilityPercentage, 1),
            'alerts' => collect($alerts)->take(10), // Mostrar solo las 10 primeras
            'totalAlerts' => count($alerts),
        ]);
    }
}
