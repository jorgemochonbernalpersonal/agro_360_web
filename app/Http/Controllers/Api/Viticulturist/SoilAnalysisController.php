<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\SoilAnalysisResource;
use App\Models\SoilAnalysis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SoilAnalysisController extends BaseApiController
{
    // ─── GET /viticulturist/soil-analyses ─────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id' => 'nullable|integer',
            'plot_id' => 'nullable|integer',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = SoilAnalysis::where('viticulturist_id', $user->id)
            ->with(['plot'])
            ->orderByDesc('analysis_date');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('plot_id')) {
            $query->where('plot_id', $request->plot_id);
        }

        if ($request->filled('search')) {
            $query->where('laboratory', 'like', "%{$request->search}%");
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return $this->paginated($items, SoilAnalysisResource::collection($items->items()));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $validated = $request->validate([
            'plot_id' => 'required|integer|exists:plots,id',
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'analysis_date' => 'required|date',
            'laboratory' => 'required|string|max:255',
            'ph' => 'nullable|numeric|min:0|max:14',
            'organic_matter' => 'nullable|numeric|min:0',
            'texture_class' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
        ]);

        \App\Models\Plot::where('viticulturist_id', $user->id)->findOrFail($validated['plot_id']);

        $record = \App\Models\SoilAnalysis::create([...$validated, 'viticulturist_id' => $user->id]);
        $record->load(['plot']);

        return response()->json([
            'data' => new \App\Http\Resources\Api\SoilAnalysisResource($record),
            'message' => __('Análisis de suelo registrado correctamente.'),
        ], 201);
    }
}
