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
        $user    = $request->user();
        $perPage = min($request->integer('per_page', 20), 50);

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
                'total'        => $analyses->total(),
                'current_page' => $analyses->currentPage(),
                'last_page'    => $analyses->lastPage(),
            ],
        ]);
    }

    // ─── GET /winery/wine-analysis/{id} ──────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user     = $request->user();
        $analysis = WineAnalysis::where('user_id', $user->id)
            ->with(['wine', 'container'])
            ->findOrFail($id);

        return response()->json(['data' => new WineAnalysisResource($analysis)]);
    }
}
