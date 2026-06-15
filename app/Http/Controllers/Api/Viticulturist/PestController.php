<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\PestResource;
use App\Models\Pest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PestController extends BaseApiController
{
    // ─── GET /viticulturist/pests ─────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {

        $request->validate([
            'type' => 'nullable|string|max:50',
            'risk_period' => 'nullable|boolean',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Pest::active()->orderBy('name');

        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        if ($request->boolean('risk_period')) {
            $query->inRiskPeriod(now()->month);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('scientific_name', 'like', "%{$term}%");
            });
        }

        $pests = $query->with(['products' => fn ($q) => $q->orderByDesc('pest_product_effectiveness.effectiveness_rating'),
        ])
            ->paginate($this->resolvePerPage($request, 30));

        return $this->paginated($pests, PestResource::collection($pests->items()));
    }

    // ─── GET /viticulturist/pests/{id} ────────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {

        $pest = Pest::active()
            ->with(['products' => fn ($q) => $q->orderByDesc('pest_product_effectiveness.effectiveness_rating'),
            ])
            ->findOrFail($id);

        return $this->success(new PestResource($pest));
    }
}
