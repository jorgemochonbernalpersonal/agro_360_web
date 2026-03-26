<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Container;
use App\Models\Harvest;
use App\Models\Wine;
use App\Models\WineFermentationControl;
use App\Http\Resources\Api\FermentationControlResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless($user->hasWineryAccess(), 403, 'Acceso denegado.');

        $userId = $user->id;

        // ── Containers ────────────────────────────────────────────────────────
        $containers     = Container::where('user_id', $userId)->where('archived', false)->get();
        $totalCapacity  = $containers->sum(fn ($c) => (float) $c->capacity);
        $totalUsed      = $containers->sum(fn ($c) => (float) $c->used_capacity);
        $criticalCount  = $containers->filter(fn ($c) => $c->getOccupancyPercentage() >= 85)->count();

        $criticalContainers = Container::where('user_id', $userId)
            ->where('archived', false)
            ->with(['containerRoom', 'currentStates.wine'])
            ->get()
            ->filter(fn ($c) => $c->getOccupancyPercentage() >= 85)
            ->sortByDesc(fn ($c) => $c->getOccupancyPercentage())
            ->take(5)
            ->values();

        // ── Wines ─────────────────────────────────────────────────────────────
        $wines              = Wine::forUser($userId)->get();
        $inProgressWines    = $wines->where('status', 'in_progress')->count();
        $activeFermentations = WineFermentationControl::whereHas(
            'wine', fn ($q) => $q->where('user_id', $userId)->where('status', 'in_progress')
        )->where('control_date', '>=', now()->subDays(3))->count();

        // ── Campaign (harvest stats) ──────────────────────────────────────────
        $campaignYear   = now()->year;
        $totalKgReceived = Harvest::where('winery_id', $userId)
            ->where('status', 'active')
            ->whereYear('harvest_start_date', $campaignYear)
            ->sum('total_weight');

        // ── Recent fermentation controls ──────────────────────────────────────
        $recentControls = WineFermentationControl::whereHas(
            'wine', fn ($q) => $q->where('user_id', $userId)
        )
        ->with(['wine', 'container'])
        ->orderByDesc('control_date')
        ->take(5)
        ->get();

        // ── Alerts ────────────────────────────────────────────────────────────
        $alerts = [];
        foreach ($criticalContainers as $c) {
            $pct = $c->getOccupancyPercentage();
            if ($pct >= 90) {
                $alerts[] = ['type' => 'critical', 'message' => "{$c->name} al {$pct}% de capacidad"];
            } elseif ($pct >= 85) {
                $alerts[] = ['type' => 'warning', 'message' => "{$c->name} al {$pct}% de capacidad"];
            }
        }

        return response()->json([
            'campaign_year'       => $campaignYear,
            'total_kg_received'   => (float) $totalKgReceived,
            'containers' => [
                'total'          => $containers->count(),
                'total_capacity' => $totalCapacity,
                'total_used'     => $totalUsed,
                'usage_pct'      => $totalCapacity > 0 ? round($totalUsed / $totalCapacity * 100, 1) : 0,
                'critical_count' => $criticalCount,
            ],
            'wines' => [
                'total'              => $wines->count(),
                'in_progress'        => $inProgressWines,
                'active_fermentations' => $activeFermentations,
            ],
            'critical_containers' => $criticalContainers->map(fn ($c) => [
                'id'            => $c->id,
                'name'          => $c->name,
                'occupancy_pct' => $c->getOccupancyPercentage(),
                'used_capacity' => (float) $c->used_capacity,
                'capacity'      => (float) $c->capacity,
                'room'          => $c->containerRoom?->name,
            ]),
            'recent_fermentation_controls' => FermentationControlResource::collection($recentControls),
            'alerts' => $alerts,
        ]);
    }
}
