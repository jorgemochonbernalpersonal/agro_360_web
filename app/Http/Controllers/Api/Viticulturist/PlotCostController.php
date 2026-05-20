<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PlotCostResource;
use App\Models\PlotCost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlotCostController extends Controller
{
    // ─── GET /viticulturist/plot-costs ────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id' => 'nullable|integer',
            'plot_id'     => 'nullable|integer',
            'category'    => 'nullable|string',
            'search'      => 'nullable|string|max:100',
            'per_page'    => 'nullable|integer|min:1|max:100',
        ]);

        $query = PlotCost::where('viticulturist_id', $user->id)
            ->with(['plot'])
            ->orderByDesc('cost_date');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('plot_id')) {
            $query->where('plot_id', $request->plot_id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', "%{$term}%")
                  ->orWhere('supplier', 'like', "%{$term}%");
            });
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => PlotCostResource::collection($items->items()),
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
            'plot_id'           => 'nullable|integer|exists:plots,id',
            'campaign_id'       => 'nullable|integer|exists:campaigns,id',
            'category'          => 'required|in:labor,machinery,materials,phytosanitary,fertilizer,water,insurance,transport,subcontracting,other',
            'description'       => 'required|string|max:255',
            'amount'            => 'required|numeric|min:0.01',
            'cost_date'         => 'required|date',
            'supplier'          => 'nullable|string|max:255',
            'invoice_reference' => 'nullable|string|max:100',
            'notes'             => 'nullable|string|max:2000',
        ]);

        if (isset($validated['plot_id'])) {
            \App\Models\Plot::where('user_id', $user->id)->findOrFail($validated['plot_id']);
        }

        $record = PlotCost::create([...$validated, 'viticulturist_id' => $user->id]);
        $record->load(['plot']);

        return response()->json([
            'data'    => new PlotCostResource($record),
            'message' => 'Coste registrado correctamente.',
        ], 201);
    }
}
