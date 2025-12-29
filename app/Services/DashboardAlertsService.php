<?php

namespace App\Services;

use App\Models\AgriculturalActivity;
use App\Models\Container;
use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardAlertsService
{
    /**
     * Get all dashboard alerts for a user
     */
    public function getAlerts(User $user): Collection
    {
        $alerts = collect();

        // Check containers
        $this->checkContainerAlerts($user, $alerts);
        
        // Check activities
        $this->checkActivityAlerts($user, $alerts);
        
        // Check remote sensing
        $this->checkRemoteSensingAlerts($user, $alerts);

        return $alerts;
    }

    /**
     * Check container-related alerts
     */
    private function checkContainerAlerts(User $user, Collection $alerts): void
    {
        $availableContainers = Container::where('user_id', $user->id)
            ->whereDoesntHave('harvests')
            ->where('archived', false)
            ->count();

        if ($availableContainers < 5 && $availableContainers > 0) {
            $alerts->push([
                'id' => 'low_containers',
                'type' => 'warning',
                'icon' => '📦',
                'title' => 'Contenedores bajos',
                'message' => "Solo quedan {$availableContainers} contenedores disponibles",
                'action_url' => route('viticulturist.digital-notebook.containers.index'),
                'action_text' => 'Ver contenedores',
            ]);
        } elseif ($availableContainers === 0) {
            $alerts->push([
                'id' => 'no_containers',
                'type' => 'danger',
                'icon' => '🚨',
                'title' => 'Sin contenedores',
                'message' => 'No hay contenedores disponibles',
                'action_url' => route('viticulturist.digital-notebook.containers.index'),
                'action_text' => 'Añadir contenedores',
            ]);
        }
    }

    /**
     * Check activity-related alerts
     */
    private function checkActivityAlerts(User $user, Collection $alerts): void
    {
        $activitiesThisMonth = AgriculturalActivity::where('viticulturist_id', $user->id)
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->count();

        if ($activitiesThisMonth === 0) {
            $alerts->push([
                'id' => 'no_activities_month',
                'type' => 'info',
                'icon' => '📝',
                'title' => 'Sin actividades',
                'message' => 'No has registrado actividades este mes',
                'action_url' => route('viticulturist.digital-notebook'),
                'action_text' => 'Registrar actividad',
            ]);
        }
    }

    /**
     * Check remote sensing alerts
     */
    private function checkRemoteSensingAlerts(User $user, Collection $alerts): void
    {
        $userPlotIds = Plot::forUser($user)->pluck('id');
        
        // Check for low NDVI
        $lowNdviPlots = PlotRemoteSensing::whereIn('plot_id', $userPlotIds)
            ->where('ndvi_mean', '<', 0.35)
            ->whereDate('image_date', '>=', now()->subDays(7))
            ->with('plot')
            ->get()
            ->unique('plot_id');

        foreach ($lowNdviPlots as $data) {
            $alerts->push([
                'id' => 'low_ndvi_' . $data->plot_id,
                'type' => 'warning',
                'icon' => '🌱',
                'title' => 'NDVI bajo',
                'message' => "{$data->plot->name}: NDVI {$data->ndvi_mean} (estrés detectado)",
                'action_url' => route('remote-sensing.dashboard'),
                'action_text' => 'Ver teledetección',
            ]);
        }
    }

    /**
     * Get alert count
     */
    public function getAlertCount(User $user): int
    {
        return $this->getAlerts($user)->count();
    }
}
