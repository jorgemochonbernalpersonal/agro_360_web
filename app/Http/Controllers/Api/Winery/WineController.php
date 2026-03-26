<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FermentationControlResource;
use App\Http\Resources\Api\WineResource;
use App\Models\Wine;
use App\Models\WineFermentationControl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WineController extends Controller
{
    // ─── GET /winery/wines ────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $query = Wine::forUser($user->id)
            ->with(['fermentationControls' => fn ($q) => $q->latest('control_date')->take(1)]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('vintage')) {
            $query->where('vintage', $request->vintage);
        }

        $wines = $query->orderByDesc('created_at')->get();

        return response()->json([
            'data' => WineResource::collection($wines),
            'meta' => [
                'total'       => $wines->count(),
                'in_progress' => $wines->where('status', 'in_progress')->count(),
                'aged'        => $wines->where('status', 'aged')->count(),
                'bottled'     => $wines->where('status', 'bottled')->count(),
            ],
        ]);
    }

    // ─── GET /winery/wines/{id} ───────────────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $wine = Wine::forUser($user->id)
            ->with(['fermentationControls' => fn ($q) => $q->latest('control_date')->take(10)])
            ->findOrFail($id);

        return response()->json(['data' => new WineResource($wine)]);
    }

    // ─── GET /winery/wines/{id}/fermentation-controls ─────────────────────────

    public function fermentationControls(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $wine = Wine::forUser($user->id)->findOrFail($id);

        $controls = WineFermentationControl::where('wine_id', $wine->id)
            ->with(['container'])
            ->orderByDesc('control_date')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => FermentationControlResource::collection($controls->items()),
            'meta' => [
                'total'        => $controls->total(),
                'current_page' => $controls->currentPage(),
                'last_page'    => $controls->lastPage(),
            ],
        ]);
    }
}
