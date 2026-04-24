<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ResidueAnalysisResource;
use App\Models\ResidueAnalysis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResidueAnalysisController extends Controller
{
    // ─── GET /viticulturist/residue-analyses ──────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id'       => 'nullable|integer|min:1',
            'overall_compliant' => 'nullable|boolean',
            'per_page'          => 'nullable|integer|min:1|max:100',
        ]);

        $query = ResidueAnalysis::active()
            ->forViticulturist($user->id)
            ->with('plotPlanting.plot')
            ->orderByDesc('analysis_date');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', (int) $request->campaign_id);
        }

        if ($request->filled('overall_compliant')) {
            $query->where('overall_compliant', $request->boolean('overall_compliant'));
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => ResidueAnalysisResource::collection($items->items()),
            'meta' => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $validated = $request->validate([
            'campaign_id'       => 'nullable|integer|exists:campaigns,id',
            'analysis_date'     => 'required|date',
            'laboratory_name'   => 'required|string|max:255',
            'sample_type'       => 'nullable|string|max:100',
            'overall_compliant' => 'nullable|boolean',
            'notes'             => 'nullable|string|max:2000',
        ]);

        $record = \App\Models\ResidueAnalysis::create([...$validated, 'viticulturist_id' => $user->id, 'active' => true]);

        return response()->json([
            'data'    => new \App\Http\Resources\Api\ResidueAnalysisResource($record),
            'message' => 'Análisis de residuos registrado correctamente.',
        ], 201);
    }
}
