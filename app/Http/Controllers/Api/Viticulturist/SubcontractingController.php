<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SubcontractingResource;
use App\Models\Subcontracting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubcontractingController extends Controller
{
    // ─── GET /viticulturist/subcontractings ────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id'  => 'nullable|integer|min:1',
            'plot_id'      => 'nullable|integer|min:1',
            'service_type' => 'nullable|string|max:50',
            'search'       => 'nullable|string|max:100',
            'per_page'     => 'nullable|integer|min:1|max:100',
        ]);

        $query = Subcontracting::where('viticulturist_id', $user->id)
            ->with(['plot', 'campaign'])
            ->orderByDesc('service_date');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('company_name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', (int) $request->campaign_id);
        }

        if ($request->filled('plot_id')) {
            $query->where('plot_id', (int) $request->plot_id);
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => SubcontractingResource::collection($items->items()),
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
            'plot_id'        => 'nullable|integer|exists:plots,id',
            'campaign_id'    => 'nullable|integer|exists:campaigns,id',
            'service_type'   => 'required|in:harvesting,pruning,treatment,fertilization,irrigation,soil_work,transport,analysis,other',
            'company_name'   => 'required|string|max:255',
            'service_date'   => 'required|date',
            'amount'         => 'nullable|numeric|min:0',
            'invoiced'       => 'nullable|boolean',
            'description'    => 'nullable|string|max:1000',
            'notes'          => 'nullable|string|max:2000',
        ]);

        if (isset($validated['plot_id'])) {
            \App\Models\Plot::where('user_id', $user->id)->findOrFail($validated['plot_id']);
        }

        $record = \App\Models\Subcontracting::create([...$validated, 'viticulturist_id' => $user->id]);

        return response()->json([
            'data'    => new \App\Http\Resources\Api\SubcontractingResource($record),
            'message' => 'Subcontratación registrada correctamente.',
        ], 201);
    }
}
