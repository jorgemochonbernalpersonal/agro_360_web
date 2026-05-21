<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FermentationControlResource;
use App\Http\Resources\Api\WineResource;
use App\Models\Wine;
use App\Models\WineAnalysis;
use App\Models\WineBottling;
use App\Models\WineFermentationControl;
use App\Models\WineTastingNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WineController extends Controller
{
    // ─── GET /winery/wines ────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $request->validate([
            'status'  => 'nullable|string|in:' . implode(',', array_keys(Wine::STATUSES)),
            'vintage' => 'nullable|integer|min:1900|max:' . (now()->year + 2),
        ]);

        $base = Wine::forUser($user->id);

        if ($request->filled('status')) {
            $base->where('status', $request->status);
        }
        if ($request->filled('vintage')) {
            $base->where('vintage', (int) $request->vintage);
        }

        // Status counts over full filtered set (single aggregation query)
        $counts = (clone $base)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'aged'        THEN 1 ELSE 0 END) as aged,
                SUM(CASE WHEN status = 'bottled'     THEN 1 ELSE 0 END) as bottled
            ")
            ->first();

        $perPage = $this->resolvePerPage($request, 12, 100);

        $wines = (clone $base)
            ->with(['fermentationControls' => fn ($q) => $q->latest('control_date')->take(1)])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => WineResource::collection($wines),
            'meta' => [
                'total'        => (int) $counts->total,
                'per_page'     => $wines->perPage(),
                'current_page' => $wines->currentPage(),
                'last_page'    => $wines->lastPage(),
                'in_progress'  => (int) $counts->in_progress,
                'aged'         => (int) $counts->aged,
                'bottled'      => (int) $counts->bottled,
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

    // ─── POST /winery/wines ───────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'vintage'             => 'required|integer|min:1900|max:' . (now()->year + 2),
            'wine_type'           => 'required|string|in:' . implode(',', array_keys(Wine::WINE_TYPES)),
            'aging_type'          => 'nullable|string|in:' . implode(',', array_keys(Wine::AGING_TYPES)),
            'category'            => 'nullable|string|in:' . implode(',', array_keys(Wine::CATEGORIES)),
            'variety'             => 'nullable|string|max:255',
            'volume_liters'       => 'required|numeric|min:0.001',
            'initial_quantity_kg' => 'nullable|numeric|min:0',
            'internal_code'       => 'nullable|string|max:100',
            'is_must'             => 'nullable|boolean',
            'is_organic'          => 'nullable|boolean',
            'notes'               => 'nullable|string|max:2000',
        ]);

        $wine = Wine::create(array_merge($validated, [
            'user_id' => $user->id,
            'status'  => 'in_progress',
        ]));

        return response()->json(['data' => new WineResource($wine)], 201);
    }

    // ─── PUT /winery/wines/{id} ───────────────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $wine = Wine::forUser($user->id)->findOrFail($id);

        $validated = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'vintage'       => 'sometimes|nullable|integer|min:1900|max:' . (now()->year + 2),
            'wine_type'     => 'sometimes|string|in:' . implode(',', array_keys(Wine::WINE_TYPES)),
            'aging_type'    => 'sometimes|nullable|string|in:' . implode(',', array_keys(Wine::AGING_TYPES)),
            'category'      => 'sometimes|nullable|string|in:' . implode(',', array_keys(Wine::CATEGORIES)),
            'status'        => 'sometimes|string|in:' . implode(',', array_keys(Wine::STATUSES)),
            'variety'       => 'sometimes|nullable|string|max:255',
            'volume_liters' => 'sometimes|nullable|numeric|min:0',
            'internal_code' => 'sometimes|nullable|string|max:100',
            'is_must'       => 'sometimes|nullable|boolean',
            'is_organic'    => 'sometimes|nullable|boolean',
            'notes'         => 'sometimes|nullable|string|max:2000',
        ]);

        $wine->update($validated);

        return response()->json(['data' => new WineResource($wine)]);
    }

    // ─── DELETE /winery/wines/{id} ────────────────────────────────────────────

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $wine = Wine::forUser($user->id)->findOrFail($id);
        $wine->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Vino cancelado correctamente.']);
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

    // ─── GET /winery/wines/{id}/technical-sheet ───────────────────────────────

    public function technicalSheet(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $wine = Wine::forUser($user->id)
            ->with(['oenologist'])
            ->findOrFail($id);

        // Latest analysis
        $latestAnalysis = WineAnalysis::forUser($user->id)
            ->forWine($wine->id)
            ->orderByDesc('analysis_date')
            ->first();

        // Latest tasting note
        $latestTasting = WineTastingNote::forUser($user->id)
            ->where('wine_id', $wine->id)
            ->orderByDesc('evaluation_date')
            ->first();

        // Bottling summary
        $bottlingSummary = WineBottling::whereHas('wine', fn ($q) => $q->where('user_id', $user->id))
            ->where('wine_id', $wine->id)
            ->selectRaw('SUM(quantity_bottles) as total_bottles, MAX(bottling_date) as last_bottling_date')
            ->first();

        return response()->json([
            'data' => [
                'wine' => [
                    'id'                  => $wine->id,
                    'name'                => $wine->name,
                    'internal_code'       => $wine->internal_code,
                    'vintage'             => $wine->vintage,
                    'wine_type'           => $wine->wine_type,
                    'wine_type_label'     => Wine::WINE_TYPES[$wine->wine_type] ?? $wine->wine_type,
                    'aging_type'          => $wine->aging_type,
                    'aging_type_label'    => Wine::AGING_TYPES[$wine->aging_type] ?? $wine->aging_type,
                    'category'            => $wine->category,
                    'category_label'      => Wine::CATEGORIES[$wine->category] ?? $wine->category,
                    'variety'             => $wine->variety,
                    'volume_liters'       => $wine->volume_liters !== null ? (float) $wine->volume_liters : null,
                    'initial_quantity_kg' => $wine->initial_quantity_kg !== null ? (float) $wine->initial_quantity_kg : null,
                    'is_must'             => $wine->is_must,
                    'is_organic'          => $wine->is_organic,
                    'status'              => $wine->status,
                    'status_label'        => Wine::STATUSES[$wine->status] ?? $wine->status,
                    'trace_token'         => $wine->trace_token,
                    'notes'               => $wine->notes,
                    'oenologist'          => $wine->oenologist ? [
                        'id'   => $wine->oenologist->id,
                        'name' => trim(($wine->oenologist->name ?? '') . ' ' . ($wine->oenologist->surname ?? '')),
                    ] : null,
                ],
                'analysis' => $latestAnalysis ? [
                    'analysis_date'      => $latestAnalysis->analysis_date?->toDateString(),
                    'analysis_type'      => $latestAnalysis->analysis_type,
                    'laboratory'         => $latestAnalysis->laboratory_name ?? $latestAnalysis->laboratory,
                    'alcoholic_strength' => $latestAnalysis->alcoholic_strength !== null ? (float) $latestAnalysis->alcoholic_strength : null,
                    'total_acidity'      => $latestAnalysis->total_acidity !== null ? (float) $latestAnalysis->total_acidity : null,
                    'volatile_acidity'   => $latestAnalysis->volatile_acidity !== null ? (float) $latestAnalysis->volatile_acidity : null,
                    'residual_sugar'     => $latestAnalysis->residual_sugar !== null ? (float) $latestAnalysis->residual_sugar : null,
                    'free_so2'           => $latestAnalysis->free_so2 !== null ? (float) $latestAnalysis->free_so2 : null,
                    'total_so2'          => $latestAnalysis->total_so2 !== null ? (float) $latestAnalysis->total_so2 : null,
                    'ph'                 => $latestAnalysis->ph !== null ? (float) $latestAnalysis->ph : null,
                    'density'            => $latestAnalysis->density !== null ? (float) $latestAnalysis->density : null,
                    'color_intensity'    => $latestAnalysis->color_intensity !== null ? (float) $latestAnalysis->color_intensity : null,
                    'result'             => $latestAnalysis->result,
                    'result_label'       => $latestAnalysis->result_label,
                ] : null,
                'tasting' => $latestTasting ? [
                    'evaluation_date'     => $latestTasting->evaluation_date?->toDateString(),
                    'evaluator'           => $latestTasting->evaluator_display,
                    'visual_color'        => $latestTasting->visual_color,
                    'visual_clarity'      => $latestTasting->visual_clarity,
                    'visual_intensity'    => $latestTasting->visual_intensity,
                    'aroma_intensity'     => $latestTasting->aroma_intensity,
                    'aroma_descriptors'   => $latestTasting->aroma_descriptors,
                    'palate_acidity'      => $latestTasting->palate_acidity,
                    'palate_tannins'      => $latestTasting->palate_tannins,
                    'palate_body'         => $latestTasting->palate_body,
                    'palate_finish'       => $latestTasting->palate_finish,
                    'overall_score'       => $latestTasting->overall_score !== null ? (float) $latestTasting->overall_score : null,
                    'overall_conclusion'  => $latestTasting->overall_conclusion,
                ] : null,
                'bottling' => [
                    'total_bottles'     => (int) ($bottlingSummary->total_bottles ?? 0),
                    'last_bottling_date'=> $bottlingSummary->last_bottling_date
                        ? \Carbon\Carbon::parse($bottlingSummary->last_bottling_date)->toDateString()
                        : null,
                ],
            ],
        ]);
    }
}
