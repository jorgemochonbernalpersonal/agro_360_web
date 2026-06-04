<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\WaterConcessionResource;
use App\Models\WaterConcession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WaterConcessionController extends Controller
{
    // ─── GET /viticulturist/water-concessions ─────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id' => 'nullable|integer|min:1',
            'concession_type' => 'nullable|string|max:50',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = WaterConcession::where('viticulturist_id', $user->id)
            ->where('active', true)
            ->with('campaign')
            ->orderByDesc('concession_date');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', (int) $request->campaign_id);
        }

        if ($request->filled('concession_type')) {
            $query->where('concession_type', $request->concession_type);
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => WaterConcessionResource::collection($items->items()),
            'meta' => [
                'total' => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'has_more' => $items->hasMorePages(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $validated = $request->validate([
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'concession_type' => 'required|in:superficial,subterranea,comunidad_regantes,otro',
            'concession_number' => 'nullable|string|max:100',
            'water_body' => 'required|string|max:255',
            'authority' => 'required|string|max:255',
            'expiry_date' => 'nullable|date',
            'max_volume_m3' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        $record = \App\Models\WaterConcession::create([...$validated, 'viticulturist_id' => $user->id, 'active' => true]);

        return response()->json([
            'data' => new \App\Http\Resources\Api\WaterConcessionResource($record),
            'message' => __('Concesión de agua registrada correctamente.'),
        ], 201);
    }
}
