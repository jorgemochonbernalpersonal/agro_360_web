<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\Container;
use App\Models\ContainerHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContainerHistoryController extends Controller
{
    /**
     * GET /winery/containers/{id}/history
     * Historial de movimientos de un contenedor, paginado.
     */
    public function index(Request $request, int $containerId): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $container = Container::where('user_id', $user->id)->findOrFail($containerId);

        $query = ContainerHistory::where('container_id', $container->id)
            ->with(['wine', 'harvest', 'creator'])
            ->orderByDesc('start_date');

        if ($request->filled('operation_type')) {
            $query->where('operation_type', $request->input('operation_type'));
        }
        if ($request->filled('wine_id')) {
            $query->where('wine_id', $request->integer('wine_id'));
        }
        if ($request->filled('from')) {
            $query->whereDate('start_date', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('start_date', '<=', $request->input('to'));
        }

        $perPage = min($request->integer('per_page', 30), 100);
        $history = $query->paginate($perPage);

        return response()->json([
            'container' => [
                'id'                 => $container->id,
                'name'               => $container->name,
                'capacity'           => (float) $container->capacity,
                'used_capacity'      => (float) $container->getTotalUsed(),
                'occupancy_percent'  => $container->getOccupancyPercentage(),
            ],
            'data' => $history->map(fn ($h) => $this->formatEntry($h)),
            'meta' => [
                'total'        => $history->total(),
                'per_page'     => $history->perPage(),
                'current_page' => $history->currentPage(),
                'last_page'    => $history->lastPage(),
            ],
        ]);
    }

    /**
     * GET /winery/containers/{id}/analytics
     * Analytics de ocupación y movimientos de un contenedor.
     */
    public function analytics(Request $request, int $containerId): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $container = Container::where('user_id', $user->id)
            ->with(['currentStates.wine', 'containerType', 'containerRoom'])
            ->findOrFail($containerId);

        $months = $request->integer('months', 12);

        // Movimientos por tipo (últimos N meses)
        $byType = ContainerHistory::where('container_id', $containerId)
            ->where('start_date', '>=', now()->subMonths($months))
            ->selectRaw('operation_type, COUNT(*) as count, SUM(ABS(quantity)) as total_volume')
            ->groupBy('operation_type')
            ->get()
            ->keyBy('operation_type')
            ->map(fn ($r) => ['count' => (int) $r->count, 'total_volume' => (float) $r->total_volume]);

        // Volumen neto por mes (últimos N meses)
        $volumeByMonth = ContainerHistory::where('container_id', $containerId)
            ->where('start_date', '>=', now()->subMonths($months))
            ->selectRaw("DATE_FORMAT(start_date, '%Y-%m') as month, SUM(quantity) as net_volume, COUNT(*) as operations")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($r) => [
                'month'      => $r->month,
                'net_volume' => (float) $r->net_volume,
                'operations' => (int) $r->operations,
            ]);

        // Vinos que han pasado por este contenedor
        $wines = ContainerHistory::where('container_id', $containerId)
            ->whereNotNull('wine_id')
            ->with('wine:id,name,vintage_year,wine_type')
            ->select('wine_id', DB::raw('SUM(ABS(quantity)) as total_volume'), DB::raw('COUNT(*) as operations'))
            ->groupBy('wine_id')
            ->orderByDesc('total_volume')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'wine_id'      => $r->wine_id,
                'wine_name'    => $r->wine?->name,
                'vintage_year' => $r->wine?->vintage_year,
                'wine_type'    => $r->wine?->wine_type,
                'total_volume' => (float) $r->total_volume,
                'operations'   => (int) $r->operations,
            ]);

        // Último y próximo mantenimiento
        $lastMaintenance = ContainerHistory::where('container_id', $containerId)
            ->where('operation_type', 'maintenance')
            ->orderByDesc('start_date')
            ->first();

        return response()->json([
            'container' => [
                'id'                => $container->id,
                'name'              => $container->name,
                'type'              => $container->containerType?->name,
                'room'              => $container->containerRoom?->name,
                'capacity'          => (float) $container->capacity,
                'used_capacity'     => (float) $container->getTotalUsed(),
                'occupancy_percent' => $container->getOccupancyPercentage(),
                'is_empty'          => $container->isEmpty(),
                'is_full'           => $container->isFull(),
                'next_maintenance'  => $container->next_maintenance_date?->toDateString(),
                'last_maintenance'  => $lastMaintenance?->start_date?->toDateString(),
            ],
            'summary' => [
                'total_operations' => array_sum(array_column($byType->toArray(), 'count')),
                'by_type'          => $byType,
            ],
            'volume_by_month' => $volumeByMonth,
            'wines_history'   => $wines,
        ]);
    }

    /**
     * GET /winery/containers/fleet-analytics
     * Resumen global de la flota de contenedores.
     */
    public function fleetAnalytics(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $containers = Container::where('user_id', $user->id)
            ->where('archived', false)
            ->with(['containerType', 'containerRoom'])
            ->get();

        $total     = $containers->count();
        $capacity  = $containers->sum(fn ($c) => (float) $c->capacity);
        $used      = $containers->sum(fn ($c) => (float) $c->getTotalUsed());
        $empty     = $containers->filter(fn ($c) => $c->isEmpty())->count();
        $critical  = $containers->filter(fn ($c) => $c->getOccupancyPercentage() >= 90)->count();
        $warning   = $containers->filter(fn ($c) => $c->getOccupancyPercentage() >= 75 && $c->getOccupancyPercentage() < 90)->count();

        // Agrupación por sala
        $byRoom = $containers->groupBy(fn ($c) => $c->container_room_id ?? 0)
            ->map(function ($group) {
                $first    = $group->first();
                $capacity = $group->sum(fn ($c) => (float) $c->capacity);
                $used     = $group->sum(fn ($c) => (float) $c->getTotalUsed());
                return [
                    'room_id'          => $first->container_room_id,
                    'room_name'        => $first->containerRoom?->name ?? 'Sin sala',
                    'count'            => $group->count(),
                    'capacity'         => $capacity,
                    'used'             => $used,
                    'occupancy_percent'=> $capacity > 0 ? round($used / $capacity * 100, 1) : 0,
                ];
            })->values();

        // Agrupación por tipo
        $byType = $containers->groupBy(fn ($c) => $c->type_id)
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'type_id'   => $first->type_id,
                    'type_name' => $first->containerType?->name ?? 'Sin tipo',
                    'count'     => $group->count(),
                    'capacity'  => $group->sum(fn ($c) => (float) $c->capacity),
                ];
            })->values();

        // Contenedores que necesitan mantenimiento pronto
        $maintenanceDue = Container::where('user_id', $user->id)
            ->where('archived', false)
            ->whereNotNull('next_maintenance_date')
            ->where('next_maintenance_date', '<=', now()->addDays(30))
            ->orderBy('next_maintenance_date')
            ->get(['id', 'name', 'next_maintenance_date'])
            ->map(fn ($c) => [
                'id'   => $c->id,
                'name' => $c->name,
                'next_maintenance' => $c->next_maintenance_date?->toDateString(),
                'days_until'       => (int) now()->diffInDays($c->next_maintenance_date, false),
            ]);

        return response()->json([
            'overview' => [
                'total'            => $total,
                'total_capacity'   => $capacity,
                'total_used'       => $used,
                'occupancy_percent'=> $capacity > 0 ? round($used / $capacity * 100, 1) : 0,
                'empty'            => $empty,
                'critical'         => $critical,
                'warning'          => $warning,
            ],
            'by_room'         => $byRoom,
            'by_type'         => $byType,
            'maintenance_due' => $maintenanceDue,
        ]);
    }

    private function formatEntry(ContainerHistory $h): array
    {
        return [
            'id'             => $h->id,
            'operation_type' => $h->operation_type,
            'description'    => $h->getOperationDescription(),
            'wine_id'        => $h->wine_id,
            'wine_name'      => $h->wine?->name,
            'harvest_id'     => $h->harvest_id,
            'quantity'       => (float) $h->quantity,
            'is_inbound'     => $h->isInbound(),
            'start_date'     => $h->start_date?->toIso8601String(),
            'end_date'       => $h->end_date?->toIso8601String(),
            'created_by'     => $h->creator?->name,
            'has_subproducts'=> $h->has_subproducts,
        ];
    }
}
