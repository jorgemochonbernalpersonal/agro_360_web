<?php

namespace App\Services;

use App\Models\AgriculturalActivity;
use App\Models\Container;
use App\Models\NotebookAccessRequest;
use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Models\WineStockSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardAlertsService
{
    /**
     * Get all dashboard alerts for a user
     */
    public function getAlerts(User $user): Collection
    {
        $cached = Cache::remember("dashboard_alerts_{$user->id}", 300, function () use ($user) {
            $alerts = collect();

            if ($user->hasWineryAccess()) {
                $this->checkWineryAlerts($user, $alerts);
            }

            if ($user->hasViticulturistAccess()) {
                $this->checkContainerAlerts($user, $alerts);
                $this->checkActivityAlerts($user, $alerts);
                $this->checkRemoteSensingAlerts($user, $alerts);
                $this->checkNotebookAccessAlerts($user, $alerts);
            }

            return $alerts->values()->all();
        });

        return collect($cached);
    }

    /**
     * Winery-specific alerts: INFOVI deadlines and snapshot freshness
     */
    private function checkWineryAlerts(User $user, Collection $alerts): void
    {
        $this->checkInforviDeadlineAlert($user, $alerts);
        $this->checkSnapshotFreshnessAlert($user, $alerts);
    }

    private function checkInforviDeadlineAlert(User $user, Collection $alerts): void
    {
        $today   = now()->startOfDay();
        $isLarge = $this->isLargeProducer($user->id);

        $deadlines = $isLarge
            ? $this->monthlyDeadlines($today)
            : $this->semiAnnualDeadlines($today);

        foreach ($deadlines as $deadline) {
            $daysLeft = (int) $today->diffInDays(Carbon::parse($deadline['date']), false);

            if ($daysLeft >= 0 && $daysLeft <= 7) {
                $urgency = $daysLeft <= 1 ? 'danger' : 'warning';
                $alerts->push([
                    'id'          => 'infovi_deadline_' . $deadline['date'],
                    'type'        => $urgency,
                    'icon'        => $daysLeft <= 1 ? '⚠️' : '📋',
                    'title'       => 'Declaración INFOVI' . ($daysLeft === 0 ? ' — vence hoy' : ' en ' . $daysLeft . ' ' . ($daysLeft === 1 ? 'día' : 'días')),
                    'message'     => $deadline['label'],
                    'action_url'  => route('winery.silicie.infovi'),
                    'action_text' => 'Ver INFOVI',
                ]);
                break; // only show the nearest deadline
            }
        }
    }

    private function checkSnapshotFreshnessAlert(User $user, Collection $alerts): void
    {
        $lastSnapshot = WineStockSnapshot::where('user_id', $user->id)
            ->max('snapshot_date');

        if (!$lastSnapshot) {
            $alerts->push([
                'id'          => 'silicie_no_snapshot',
                'type'        => 'info',
                'icon'        => '📷',
                'title'       => 'SILICIE sin instantánea',
                'message'     => 'Registra una instantánea de existencias para mantener el libro al día',
                'action_url'  => route('winery.silicie.dashboard'),
                'action_text' => 'Ir a SILICIE',
            ]);
            return;
        }

        $daysSince = (int) now()->diffInDays(Carbon::parse($lastSnapshot));
        if ($daysSince > 30) {
            $alerts->push([
                'id'          => 'silicie_stale_snapshot',
                'type'        => 'warning',
                'icon'        => '📷',
                'title'       => 'Instantánea SILICIE desactualizada',
                'message'     => 'La última instantánea tiene ' . $daysSince . ' días — recomendable actualizar mensualmente',
                'action_url'  => route('winery.silicie.dashboard'),
                'action_text' => 'Actualizar instantánea',
            ]);
        }
    }

    /**
     * Viticulturist: pending notebook access requests from wineries/DOs
     */
    private function checkNotebookAccessAlerts(User $user, Collection $alerts): void
    {
        $pending = NotebookAccessRequest::where('viticulturist_id', $user->id)
            ->where('status', NotebookAccessRequest::STATUS_PENDING)
            ->count();

        if ($pending > 0) {
            $label = $pending === 1 ? '1 solicitud pendiente' : "{$pending} solicitudes pendientes";
            $alerts->push([
                'id'          => 'notebook_access_pending',
                'type'        => 'info',
                'icon'        => '📓',
                'title'       => 'Acceso al cuaderno de campo',
                'message'     => $label . ' de acceso sin responder',
                'action_url'  => route('viticulturist.winery-access.index'),
                'action_text' => 'Gestionar accesos',
            ]);
        }
    }

    private function isLargeProducer(int $wineryId): bool
    {
        $avgHl = WineStockSnapshot::where('user_id', $wineryId)
            ->where('is_must', false)
            ->selectRaw('YEAR(snapshot_date) as yr, SUM(quantity_liters) / 100.0 as hl')
            ->groupBy('yr')
            ->orderByDesc('yr')
            ->limit(4)
            ->get()
            ->avg('hl');

        return (float) $avgHl > 1000;
    }

    private function monthlyDeadlines(Carbon $today): array
    {
        $next = $today->copy()->addMonth()->startOfMonth()->setDay(19);
        return [[
            'label' => 'Declaración mensual ' . $next->translatedFormat('F Y'),
            'date'  => $next->toDateString(),
        ]];
    }

    private function semiAnnualDeadlines(Carbon $today): array
    {
        $candidates = [
            $today->year . '-08-19',
            $today->year . '-12-19',
            ($today->year + 1) . '-08-19',
            ($today->year + 1) . '-12-19',
        ];
        foreach ($candidates as $d) {
            if ($d >= $today->toDateString()) {
                $date  = Carbon::parse($d);
                $month = $date->month === 12 ? 'diciembre' : 'agosto';
                return [[
                    'label' => 'Declaración ampliada ' . $month . ' ' . $date->year,
                    'date'  => $d,
                ]];
            }
        }
        return [];
    }

    /**
     * Check container-related alerts
     */
    private function checkContainerAlerts(User $user, Collection $alerts): void
    {
        // Solo mostrar alertas de contenedores si el viticultor tiene bodega vinculada
        if ($user->isViticulturist() && !WineryViticulturist::where('viticulturist_id', $user->id)->exists()) {
            return;
        }

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
                'action_url' => route('viticulturist.containers.index'),
                'action_text' => 'Ver contenedores',
            ]);
        } elseif ($availableContainers === 0) {
            $alerts->push([
                'id' => 'no_containers',
                'type' => 'danger',
                'icon' => '🚨',
                'title' => 'Sin contenedores',
                'message' => 'No hay contenedores disponibles',
                'action_url' => route('viticulturist.containers.index'),
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
