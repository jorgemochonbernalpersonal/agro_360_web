<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\WineAnalysisResource;
use App\Models\WineAnalysis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WineAnalysisController extends Controller
{
    // ─── GET /winery/wine-analysis ────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $this->resolvePerPage($request, 20, 50);

        $query = WineAnalysis::where('user_id', $user->id)
            ->with(['wine', 'container'])
            ->latest('analysis_date');

        if ($request->filled('wine_id')) {
            $query->where('wine_id', $request->integer('wine_id'));
        }
        if ($request->filled('result')) {
            $query->where('result', $request->result);
        }

        $analyses = $query->paginate($perPage);

        return response()->json([
            'data' => WineAnalysisResource::collection($analyses),
            'meta' => [
                'total' => $analyses->total(),
                'per_page' => $analyses->perPage(),
                'current_page' => $analyses->currentPage(),
                'last_page' => $analyses->lastPage(),
            ],
        ]);
    }

    // ─── POST /winery/wine-analysis ──────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'wine_id' => 'required|integer',
            'container_id' => 'nullable|integer',
            'analysis_date' => 'required|date',
            'analysis_type' => 'nullable|string|in:standard,complete,organic,custom',
            'laboratory' => 'nullable|string|max:200',
            'laboratory_name' => 'nullable|string|max:255',
            'alcoholic_strength' => 'nullable|numeric|between:0,25',
            'alcohol' => 'nullable|numeric|between:0,25',
            'total_acidity' => 'nullable|numeric|between:0,30',
            'volatile_acidity' => 'nullable|numeric|between:0,5',
            'residual_sugar' => 'nullable|numeric|min:0',
            'ph' => 'nullable|numeric|between:2,5',
            'density' => 'nullable|numeric|between:0.9,1.5',
            'free_so2' => 'nullable|numeric|min:0',
            'total_so2' => 'nullable|numeric|min:0',
            'result' => 'nullable|string|in:passed,failed,pending',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Verify the wine belongs to this user
        \App\Models\Wine::where('user_id', $user->id)->findOrFail($validated['wine_id']);

        $analysis = WineAnalysis::create(array_merge($validated, ['user_id' => $user->id]));
        $analysis->load(['wine', 'container']);

        return response()->json(['data' => new WineAnalysisResource($analysis)], 201);
    }

    // ─── GET /winery/wine-analysis/{id} ──────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $analysis = WineAnalysis::where('user_id', $user->id)
            ->with(['wine', 'container'])
            ->findOrFail($id);

        return response()->json(['data' => new WineAnalysisResource($analysis)]);
    }

    // ─── PUT /winery/wine-analysis/{id} ──────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $analysis = WineAnalysis::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'container_id' => 'nullable|integer',
            'analysis_date' => 'nullable|date',
            'analysis_type' => 'nullable|string|in:standard,complete,organic,custom',
            'laboratory' => 'nullable|string|max:200',
            'laboratory_name' => 'nullable|string|max:255',
            'alcoholic_strength' => 'nullable|numeric|between:0,25',
            'total_acidity' => 'nullable|numeric|between:0,30',
            'volatile_acidity' => 'nullable|numeric|between:0,5',
            'residual_sugar' => 'nullable|numeric|min:0',
            'ph' => 'nullable|numeric|between:2,5',
            'density' => 'nullable|numeric|between:0.9,1.5',
            'free_so2' => 'nullable|numeric|min:0',
            'total_so2' => 'nullable|numeric|min:0',
            'result' => 'nullable|string|in:passed,failed,pending',
            'notes' => 'nullable|string|max:1000',
        ]);

        $analysis->update($validated);
        $analysis->load(['wine', 'container']);

        return response()->json(['data' => new WineAnalysisResource($analysis)]);
    }

    // ─── DELETE /winery/wine-analysis/{id} ───────────────────────────────────

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $analysis = WineAnalysis::where('user_id', $user->id)->findOrFail($id);
        $analysis->delete();

        return response()->json(['message' => __('Análisis eliminado correctamente.')]);
    }
}
