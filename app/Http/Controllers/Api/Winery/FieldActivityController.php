<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\ActivityResource;
use App\Models\AgriculturalActivity;
use App\Models\WineryViticulturist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FieldActivityController extends BaseApiController
{
    /**
     * Actividades de campo de los viticultores vinculados (solo lectura).
     * Solo muestra actividades de viticultores con notebook_access = true.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $viticulturistIds = WineryViticulturist::where('winery_id', $user->id)
            ->where('notebook_access', true)
            ->pluck('viticulturist_id');

        if ($viticulturistIds->isEmpty()) {
            return response()->json([
                'data' => [],
                'meta' => ['total' => 0, 'per_page' => 20, 'current_page' => 1, 'last_page' => 1],
            ]);
        }

        $query = AgriculturalActivity::whereIn('viticulturist_id', $viticulturistIds)
            ->with(['plot', 'campaign'])
            ->orderByDesc('activity_date');

        if ($request->filled('viticulturist_id')) {
            $vId = $request->integer('viticulturist_id');
            abort_unless($viticulturistIds->contains($vId), 403, 'Sin acceso al cuaderno de este viticultor.');
            $query->where('viticulturist_id', $vId);
        }
        if ($request->filled('type')) {
            $query->where('activity_type', $request->input('type'));
        }
        if ($request->filled('plot_id')) {
            $query->where('plot_id', $request->integer('plot_id'));
        }
        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->integer('campaign_id'));
        }
        if ($request->filled('from')) {
            $query->whereDate('activity_date', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('activity_date', '<=', $request->input('to'));
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $activities = $query->paginate($perPage);

        return $this->paginated($activities, ActivityResource::collection($activities), [
            'types' => AgriculturalActivity::activityTypes(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $viticulturistIds = WineryViticulturist::where('winery_id', $user->id)
            ->where('notebook_access', true)
            ->pluck('viticulturist_id');

        $activity = AgriculturalActivity::whereIn('viticulturist_id', $viticulturistIds)
            ->with(['plot', 'campaign'])
            ->findOrFail($id);

        return $this->success(new ActivityResource($activity));
    }
}
