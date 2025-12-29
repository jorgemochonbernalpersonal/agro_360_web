<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use App\Services\RemoteSensing\NasaEarthdataService;
use Livewire\Component;

/**
 * Widget compacto para mostrar resumen de teledetección en el dashboard principal
 */
class DashboardWidget extends Component
{
    public array $stats = [];
    public array $alerts = [];
    public bool $isLoading = true;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->isLoading = true;
        
        $user = auth()->user();
        $plots = Plot::forUser($user)->get();
        $service = new NasaEarthdataService();

        $excellent = 0;
        $good = 0;
        $moderate = 0;
        $poor = 0;
        $critical = 0;
        $totalNdvi = 0;
        $ndviCount = 0;
        $alertPlots = [];

        foreach ($plots as $plot) {
            $data = $service->getLatestData($plot);
            if ($data) {
                $ndviCount++;
                $totalNdvi += $data->ndvi_mean ?? 0;
                
                match ($data->health_status) {
                    'excellent' => $excellent++,
                    'good' => $good++,
                    'moderate' => $moderate++,
                    'poor' => $poor++,
                    'critical' => $critical++,
                    default => null,
                };

                // Añadir a alertas si está en mal estado
                if (in_array($data->health_status, ['poor', 'critical'])) {
                    $alertPlots[] = [
                        'id' => $plot->id,
                        'name' => $plot->name,
                        'ndvi' => $data->ndvi_mean,
                        'status' => $data->health_status,
                        'emoji' => $data->health_emoji,
                        'trend' => $data->trend,
                        'trend_icon' => $data->trend_icon,
                    ];
                }
            }
        }

        $this->stats = [
            'total' => $ndviCount,
            'average_ndvi' => $ndviCount > 0 ? round($totalNdvi / $ndviCount, 2) : 0,
            'excellent' => $excellent,
            'good' => $good,
            'moderate' => $moderate,
            'poor' => $poor,
            'critical' => $critical,
            'alerts' => $poor + $critical,
            'healthy' => $excellent + $good,
            'healthy_percent' => $ndviCount > 0 ? round((($excellent + $good) / $ndviCount) * 100, 0) : 0,
        ];

        $this->alerts = array_slice($alertPlots, 0, 5); // Max 5 alertas
        $this->isLoading = false;
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.dashboard-widget');
    }
}
