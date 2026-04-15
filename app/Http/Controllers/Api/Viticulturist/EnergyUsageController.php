<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\EnergyUsageResource;
use App\Models\EnergyUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnergyUsageController extends Controller
{
    // ─── GET /viticulturist/energy-usages ─────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id'  => 'nullable|integer',
            'energy_type'  => 'nullable|string',
            'search'       => 'nullable|string|max:100',
            'per_page'     => 'nullable|integer|min:1|max:100',
        ]);

        $query = EnergyUsage::active()
            ->forViticulturist($user->id)
            ->with(['machinery'])
            ->orderByDesc('date');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('energy_type')) {
            $query->where('energy_type', $request->energy_type);
        }

        if ($request->filled('search')) {
            $query->where('usage_description', 'like', "%{$request->search}%");
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => EnergyUsageResource::collection($items->items()),
            'meta' => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }
}
