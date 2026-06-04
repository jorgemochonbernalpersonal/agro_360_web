<?php

namespace App\Http\Controllers\Api\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\Plot;
use App\Models\SupervisorViticulturist;
use App\Models\SupervisorWinery;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OversightController extends Controller
{
    // ─── GET /supervisor/wineries ─────────────────────────────────────────────

    public function wineries(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->isSupervisor(), 403);

        $wineryIds = SupervisorWinery::where('supervisor_id', $user->id)->pluck('winery_id');

        $wineries = User::whereIn('id', $wineryIds)
            ->with('profile')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => UserResource::collection($wineries)]);
    }

    // ─── GET /supervisor/wineries/{id} ────────────────────────────────────────

    public function winery(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->isSupervisor(), 403);

        $supervised = SupervisorWinery::where('supervisor_id', $user->id)
            ->where('winery_id', $id)
            ->firstOrFail();

        $winery = User::with('profile')->findOrFail($id);

        return response()->json(['data' => new UserResource($winery)]);
    }

    // ─── GET /supervisor/viticulturists ───────────────────────────────────────

    public function viticulturists(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->isSupervisor(), 403);

        $viticulturistIds = SupervisorViticulturist::where('supervisor_id', $user->id)
            ->pluck('viticulturist_id');

        $viticulturists = User::whereIn('id', $viticulturistIds)
            ->with('profile')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => UserResource::collection($viticulturists)]);
    }

    // ─── GET /supervisor/viticulturists/{id} ──────────────────────────────────

    public function viticulturist(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->isSupervisor(), 403);

        SupervisorViticulturist::where('supervisor_id', $user->id)
            ->where('viticulturist_id', $id)
            ->firstOrFail();

        $viticulturist = User::with('profile')->findOrFail($id);

        $plots = Plot::where('viticulturist_id', $id)
            ->where('active', true)
            ->selectRaw('COUNT(*) as total, SUM(area) as total_area')
            ->first();

        return response()->json([
            'data' => new UserResource($viticulturist),
            'plots' => [
                'total' => (int) ($plots->total ?? 0),
                'total_area' => round((float) ($plots->total_area ?? 0), 2),
            ],
        ]);
    }
}
