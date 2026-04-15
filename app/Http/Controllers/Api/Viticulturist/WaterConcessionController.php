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
            'campaign_id'     => 'nullable|integer|min:1',
            'concession_type' => 'nullable|string|max:50',
            'per_page'        => 'nullable|integer|min:1|max:100',
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
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }
}
