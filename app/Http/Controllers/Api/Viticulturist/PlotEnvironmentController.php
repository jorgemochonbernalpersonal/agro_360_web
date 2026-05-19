<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PlotEnvironmentResource;
use App\Models\PlotEnvironment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlotEnvironmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'plot_id'     => 'nullable|integer|exists:plots,id',
        ]);

        $query = PlotEnvironment::forViticulturist($user->id)
            ->with(['plot'])
            ->orderByDesc('created_at');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('plot_id')) {
            $query->where('plot_id', $request->plot_id);
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => PlotEnvironmentResource::collection($items->items()),
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
            'campaign_id'             => 'nullable|integer|exists:campaigns,id',
            'plot_id'                 => 'required|integer|exists:plots,id',
            'water_intake_nearby'     => 'nullable|boolean',
            'water_intake_distance_m' => 'nullable|numeric|min:0',
            'slope_pct'              => 'nullable|numeric|min:0|max:100',
            'erosion_risk'            => 'nullable|boolean',
            'notes'                   => 'nullable|string|max:2000',
        ]);

        \App\Models\Plot::where('user_id', $user->id)->findOrFail($validated['plot_id']);

        if (empty($validated['campaign_id'])) {
            $validated['campaign_id'] = \App\Models\Campaign::where('viticulturist_id', $user->id)
                ->where('active', true)->value('id');
        }

        $record = \App\Models\PlotEnvironment::create([...$validated, 'viticulturist_id' => $user->id]);
        $record->load(['plot']);

        return response()->json([
            'data'    => new \App\Http\Resources\Api\PlotEnvironmentResource($record),
            'message' => 'Entorno de parcela registrado correctamente.',
        ], 201);
    }
}
