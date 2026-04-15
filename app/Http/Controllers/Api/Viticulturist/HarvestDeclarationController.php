<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\HarvestDeclarationResource;
use App\Models\HarvestDeclaration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HarvestDeclarationController extends Controller
{
    // ─── GET /viticulturist/harvest-declarations ──────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id' => 'nullable|integer',
            'status'      => 'nullable|string|in:draft,submitted,accepted,rejected',
            'search'      => 'nullable|string|max:100',
            'per_page'    => 'nullable|integer|min:1|max:100',
        ]);

        $query = HarvestDeclaration::forViticulturist($user->id)
            ->active()
            ->with(['campaign'])
            ->orderByDesc('declaration_year');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => HarvestDeclarationResource::collection($items->items()),
            'meta' => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }
}
