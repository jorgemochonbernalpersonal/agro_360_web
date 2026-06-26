<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\FermentationControlResource;
use App\Models\Container;
use App\Models\Harvest;
use App\Models\Wine;
use App\Models\WineFermentationControl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends BaseApiController
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $userId = $user->id;
        $campaignYear = now()->year;
        $cacheKey = "winery_dashboard:{$userId}:{$campaignYear}";

        $data = Cache::remember($cacheKey, 60, function () use ($userId, $campaignYear) {

            // ── Container stats via selectRaw (1 query en vez de cargar todos) ────
            $containerStats = Container::where('user_id', $userId)
                ->where('archived', false)
                ->selectRaw('
                    COUNT(*) as total,
                    COALESCE(SUM(capacity), 0) as total_capacity,
                    COALESCE(SUM(used_capacity), 0) as total_used,
                    SUM(CASE WHEN capacity > 0 AND (used_capacity / capacity * 100) >= 85 THEN 1 ELSE 0 END) as critical_count
                ')
                ->first();

            $totalCapacity = (float) ($containerStats->total_capacity ?? 0);
            $totalUsed = (float) ($containerStats->total_used ?? 0);

            // ── Critical containers (query directa con WHERE, no load+filter PHP) ──
            $criticalContainers = Container::where('user_id', $userId)
                ->where('archived', false)
                ->whereRaw('capacity > 0 AND (used_capacity / capacity * 100) >= 85')
                ->with(['containerRoom:id,name'])
                ->orderByRaw('used_capacity / capacity DESC')
                ->take(5)
                ->get(['id', 'name', 'capacity', 'used_capacity', 'container_room_id']);

            // ── Wine stats via selectRaw (1 query en vez de cargar todos) ──────────
            $wineStats = Wine::forUser($userId)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress
                ')
                ->first();

            $activeFermentations = WineFermentationControl::whereHas(
                'wine', fn ($q) => $q->where('user_id', $userId)->where('status', 'in_progress')
            )->where('control_date', '>=', now()->subDays(3))->count();

            // ── Harvest stats ─────────────────────────────────────────────────────
            $totalKgReceived = (float) Harvest::where('winery_id', $userId)
                ->where('status', 'active')
                ->whereYear('harvest_start_date', $campaignYear)
                ->sum('total_weight');

            // ── Recent fermentation controls ──────────────────────────────────────
            $recentControls = WineFermentationControl::whereHas(
                'wine', fn ($q) => $q->where('user_id', $userId)
            )
                ->with(['wine:id,name,status', 'container:id,name'])
                ->orderByDesc('control_date')
                ->take(5)
                ->get();

            // ── Alerts from critical containers ───────────────────────────────────
            $alerts = [];
            foreach ($criticalContainers as $c) {
                $pct = $c->capacity > 0
                    ? round(($c->used_capacity / $c->capacity) * 100, 1)
                    : 0;
                if ($pct >= 90) {
                    $alerts[] = ['type' => 'critical', 'message' => "{$c->name} al {$pct}% de capacidad"];
                } elseif ($pct >= 85) {
                    $alerts[] = ['type' => 'warning', 'message' => "{$c->name} al {$pct}% de capacidad"];
                }
            }

            return [
                'campaign_year' => $campaignYear,
                'total_kg_received' => $totalKgReceived,
                'containers' => [
                    'total' => (int) ($containerStats->total ?? 0),
                    'total_capacity' => $totalCapacity,
                    'total_used' => $totalUsed,
                    'usage_pct' => $totalCapacity > 0 ? round($totalUsed / $totalCapacity * 100, 1) : 0,
                    'critical_count' => (int) ($containerStats->critical_count ?? 0),
                ],
                'wines' => [
                    'total' => (int) ($wineStats->total ?? 0),
                    'in_progress' => (int) ($wineStats->in_progress ?? 0),
                    'active_fermentations' => $activeFermentations,
                ],
                'critical_containers' => $criticalContainers->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'occupancy_pct' => $c->capacity > 0
                        ? round(($c->used_capacity / $c->capacity) * 100, 1) : 0,
                    'used_capacity' => (float) $c->used_capacity,
                    'capacity' => (float) $c->capacity,
                    'room' => $c->containerRoom?->name,
                ])->all(),
                'recent_fermentation_controls' => FermentationControlResource::collection($recentControls)->resolve(),
                'alerts' => $alerts,
            ];
        });

        return response()->json($data);
    }
}
