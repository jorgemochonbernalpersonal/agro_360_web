<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ResidueManagementResource;
use App\Models\ResidueManagement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResidueManagementController extends Controller
{
    // ─── GET /viticulturist/residue-managements ──────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id'   => 'nullable|integer|min:1',
            'practice_type' => 'nullable|string|max:50',
            'material_type' => 'nullable|string|max:50',
            'per_page'      => 'nullable|integer|min:1|max:100',
        ]);

        $query = ResidueManagement::active()
            ->forViticulturist($user->id)
            ->with('plot')
            ->orderByDesc('date');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', (int) $request->campaign_id);
        }

        if ($request->filled('practice_type')) {
            $query->where('practice_type', $request->practice_type);
        }

        if ($request->filled('material_type')) {
            $query->where('material_type', $request->material_type);
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => ResidueManagementResource::collection($items->items()),
            'meta' => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }
}
